<?php
// ════════════════════════════════════════════════════════
//  login.php — strona logowania z własnym formularzem
// ════════════════════════════════════════════════════════

ob_start(); // bufor — zapobiega przypadkowemu outputowi przed JSON
require_once 'auth.php';

// Jeśli już zalogowany — przekieruj od razu
if (!empty($_SESSION['logged_in']) && $_SESSION['logged_in'] === true) {
    header('Location: ' . PROTECTED_PAGE);
    exit;
}

$error   = '';
$shake   = false;

// Obsługa formularza — zwraca JSON (fetch z JS)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    ob_clean(); // wyczyść bufor przed JSON
    header('Content-Type: application/json');

    $csrfToken = trim($_POST['csrf_token'] ?? '');
    if (!validateCsrfToken($csrfToken)) {
        echo json_encode(['status' => 'error', 'message' => 'Nieprawidłowy token. Odśwież stronę.', 'shake' => false]);
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        echo json_encode(['status' => 'error', 'message' => 'Wpisz login i hasło.', 'shake' => true]);
        exit;
    }

    $result = attemptLogin($username, $password);

    switch ($result['status']) {
        case 'ok':
            echo json_encode([
                'status'       => 'ok',
                'phone_masked' => $result['phone_masked'],
            ]);
            break;
        case 'trusted':
            echo json_encode([
                'status'   => 'trusted',
                'redirect' => $result['redirect'],
            ]);
            break;
        case 'blocked':
            echo json_encode(['status' => 'blocked', 'message' => $result['message'], 'shake' => false]);
            break;
        case 'sms_error':
            echo json_encode(['status' => 'sms_error', 'message' => $result['message'], 'shake' => false]);
            break;
        default: // invalid
            echo json_encode(['status' => 'invalid', 'message' => $result['message'], 'shake' => true]);
            break;
    }
    exit;
}

// Sprawdź czy przekierowano po wylogowaniu
$loggedOut = isset($_GET['wylogowano']);
$timedOut  = isset($_GET['timeout']);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="author" content="Mateusz Karpierz">
    <meta name="robots" content="noindex,nofollow">
    <meta name="googlebot" content="noindex">
    <title>Logowanie — Secret Key</title>
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bungee&family=Space+Mono:wght@400;700&family=Barlow+Condensed:wght@700;900&family=Syne:wght@400;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg:           #0a0c10;
            --surface:      #111318;
            --surface2:     #181c24;
            --border:       #222630;
            --border-glow:  #2a3044;
            --accent:       #c084fc;
            --accent2:      #818cf8;
            --accent-dim:   rgba(192,132,252,0.10);
            --danger:       #f87171;
            --danger-dim:   rgba(248,113,113,0.10);
            --danger-border:rgba(248,113,113,0.28);
            --success:      #4ade80;
            --success-dim:  rgba(74,222,128,0.10);
            --success-border:rgba(74,222,128,0.28);
            --text:         #e2e8f0;
            --text-muted:   #64748b;
            --text-dim:     #94a3b8;
            --mono:         'Space Mono', monospace;
            --sans:         'Syne', sans-serif;
            --heading:      'Bungee', 'Barlow Condensed', Impact, sans-serif;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            height: 100%;
            background: var(--bg);
            color: var(--text);
            font-family: var(--sans);
            overflow: hidden;
            /* Zaznaczanie włączone — użytkownik musi móc wpisywać dane */
        }

        /* ─── PARTICLES ─── */
        #particles {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
            overflow: hidden;
        }
        .particle {
            position: absolute;
            font-family: var(--mono);
            font-weight: 700;
            color: rgba(192,132,252,0.15);
            pointer-events: none;
            transition: color 0.4s ease;
        }

        /* ─── PAGE ─── */
        .page {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            min-height: 100dvh; /* dynamic viewport — uwzględnia pasek przeglądarki na mobile */
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            background:
                radial-gradient(ellipse 80% 50% at 50% -10%, rgba(192,132,252,0.08) 0%, transparent 60%),
                radial-gradient(ellipse 50% 40% at 80% 80%, rgba(129,140,248,0.05) 0%, transparent 50%);
        }

        /* ─── CARD ─── */
        .login-card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 10px 40px 36px;
            width: 100%;
            max-width: 430px;
            text-align: center;
            position: relative;
            overflow: hidden;
            box-shadow: 0 24px 80px rgba(0,0,0,0.5), 0 0 0 1px rgba(255,255,255,0.03) inset;
            animation: cardIn 0.55s 0.1s cubic-bezier(0.22, 1, 0.36, 1) both;
        }
        .login-card.shake {
            animation: shake 0.45s 0.05s cubic-bezier(0.36, 0.07, 0.19, 0.97) both;
        }
        @keyframes cardIn {
            from { opacity: 0; transform: translateY(28px) scale(0.98); }
            to   { opacity: 1; transform: translateY(0) scale(1); }
        }
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20%       { transform: translateX(-7px); }
            40%       { transform: translateX(7px); }
            60%       { transform: translateX(-4px); }
            80%       { transform: translateX(4px); }
        }
        .login-card::before {
            content: '';
            position: absolute;
            top: 0; left: 10%; right: 10%; height: 1px;
            background: linear-gradient(90deg, transparent, rgba(192,132,252,0.5), transparent);
        }
        .login-card::after {
            content: '';
            position: absolute;
            inset: 0;
            border-radius: 20px;
            background: radial-gradient(ellipse 80% 60% at 50% 0%, rgba(192,132,252,0.04) 0%, transparent 70%);
            pointer-events: none;
        }

        /* ─── LOGO ─── */
        .card-logo {
            width: 100px;
            height: 100px;
            margin-bottom: 5px;
            filter: drop-shadow(0 0 14px rgba(192,132,252,0.45));
            animation: logoFloat 4s ease-in-out infinite;
            position: relative;
            z-index: 1;
        }
        @keyframes logoFloat {
            0%, 100% { transform: translateY(0); }
            50%       { transform: translateY(-7px); }
        }

        .card-title {
            font-family: var(--heading);
            font-size: 3rem;
            font-weight: 900;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            line-height: 1;
            background: linear-gradient(140deg, #ffffff 35%, var(--accent) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 5px;
            position: relative;
            z-index: 1;
        }
        .card-subtitle {
            font-family: var(--mono);
            font-size: 0.62rem;
            color: var(--text-muted);
            letter-spacing: 0.22em;
            text-transform: uppercase;
            margin-bottom: 28px;
            position: relative;
            z-index: 1;
        }

        .divider {
            height: 1px;
            background: linear-gradient(90deg, transparent, var(--border-glow), transparent);
            margin-bottom: 24px;
            position: relative;
            z-index: 1;
        }

        /* ─── ALERT BOXES ─── */
        .alert {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 0.82rem;
            text-align: left;
            line-height: 1.55;
            margin-bottom: 18px;
            position: relative;
            z-index: 1;
        }
        .alert svg { flex-shrink: 0; margin-top: 1px; }
        .alert-error {
            background: var(--danger-dim);
            border: 1px solid var(--danger-border);
            color: var(--danger);
        }
        .alert-success {
            background: var(--success-dim);
            border: 1px solid var(--success-border);
            color: var(--success);
        }

        /* ─── FORM FIELDS ─── */
        .field {
            margin-bottom: 14px;
            text-align: left;
            position: relative;
            z-index: 1;
        }
        .field label {
            display: block;
            font-family: var(--mono);
            font-size: 0.65rem;
            letter-spacing: 0.15em;
            text-transform: uppercase;
            color: var(--text-muted);
            margin-bottom: 7px;
        }
        .field input {
            width: 100%;
            padding: 12px 14px;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-family: var(--mono);
            font-size: 0.85rem;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            -webkit-text-fill-color: var(--text);
        }
        .field input::placeholder { color: var(--text-muted); }
        .field input::-webkit-input-placeholder { -webkit-text-fill-color: var(--text-muted); color: var(--text-muted); }
        .field input::-moz-placeholder { color: var(--text-muted); opacity: 1; }
        .field input:focus {
            border-color: rgba(192,132,252,0.5);
            box-shadow: 0 0 0 3px rgba(192,132,252,0.1);
        }
        /* Autofill styling */
        .field input:-webkit-autofill,
        .field input:-webkit-autofill:focus {
            -webkit-box-shadow: 0 0 0 100px var(--surface2) inset;
            -webkit-text-fill-color: var(--text);
            caret-color: var(--text);
        }

        /* ─── PASSWORD TOGGLE ─── */
        .field-password { position: relative; }
        .field-password input { padding-right: 44px; }
        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            display: flex;
            align-items: center;
            transition: color 0.2s;
        }
        .toggle-password:hover { color: var(--accent); }

        /* ─── SUBMIT BUTTON ─── */
        .submit-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            width: 100%;
            padding: 15px 24px;
            background: linear-gradient(135deg, rgba(192,132,252,0.18), rgba(129,140,248,0.12));
            border: 1px solid rgba(192,132,252,0.3);
            border-radius: 12px;
            color: var(--text);
            font-family: var(--sans);
            font-size: 0.88rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.1em;
            cursor: pointer;
            transition: all 0.22s ease;
            position: relative;
            z-index: 1;
            margin-top: 6px;
            overflow: hidden;
        }
        .submit-btn::before {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, rgba(192,132,252,0.12), rgba(129,140,248,0.08));
            opacity: 0;
            transition: opacity 0.22s;
        }
        .submit-btn:hover {
            border-color: rgba(192,132,252,0.6);
            box-shadow: 0 0 24px rgba(192,132,252,0.18), 0 4px 16px rgba(0,0,0,0.3);
            transform: translateY(-2px);
            color: #fff;
        }
        .submit-btn:hover::before { opacity: 1; }
        .submit-btn:active { transform: translateY(0); }

        /* ─── INTRO TEXT ─── */
        .card-intro {
            font-family: var(--sans);
            font-size: 0.88rem;
            color: var(--text-dim);
            line-height: 1.65;
            text-align: center;
            margin-bottom: 14px;
            position: relative;
            z-index: 1;
        }
        .card-intro strong {
            color: var(--text);
            font-weight: 700;
        }

        /* ─── HINT BOX ─── */
        .hint-box {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            background: var(--accent-dim);
            border: 1px solid rgba(192,132,252,0.2);
            border-radius: 10px;
            font-family: var(--sans);
            font-size: 0.82rem;
            color: var(--accent);
            text-align: left;
            line-height: 1.5;
            margin-bottom: 20px;
            position: relative;
            z-index: 1;
        }
        .hint-box svg { flex-shrink: 0; opacity: 0.75; }
        .hint-box span { flex: 1; }
        .hint-box strong { color: #d8b4fe; font-weight: 700; white-space: nowrap; }

        .card-footer {
            margin-top: 20px;
            font-family: var(--mono);
            font-size: 0.58rem;
            color: var(--text-muted);
            letter-spacing: 0.14em;
            text-transform: uppercase;
            opacity: 0.5;
            position: relative;
            z-index: 1;
        }

        @media (max-width: 480px) {
            .login-card { padding: 10px 22px 28px; }
            .card-title  { font-size: 2.4rem; }
        }

        /* ─── VERIFY SCREEN ─── */
        .verify-screen {
            position: fixed;
            inset: 0;
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 20px;
            z-index: 99999;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.6s ease, visibility 0.6s ease;
        }
        .verify-screen.show {
            opacity: 1;
            visibility: visible;
        }
        .verify-screen.hide-instant {
            transition: none;
            opacity: 0;
            visibility: hidden;
        }
        @keyframes dotPulse {
            0%, 20%  { opacity: 0; }
            50%      { opacity: 1; }
            100%     { opacity: 0; }
        }

        /* ─── STEP TRANSITIONS ─── */
        .card-body { position: relative; }
        .step { transition: opacity 0.4s ease, transform 0.4s ease; }
        .step.hidden {
            opacity: 0;
            transform: translateY(10px);
            pointer-events: none;
            position: absolute;
            width: 100%;
        }

        /* ─── OTP ─── */
        .otp-wrap {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin-bottom: 20px;
        }
        .otp-digit {
            width: 52px; height: 62px;
            background: var(--surface2);
            border: 1.5px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-family: var(--mono);
            font-size: 1.6rem;
            text-align: center;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            caret-color: var(--accent);
        }
        .otp-digit:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 3px rgba(192,132,252,0.15);
        }
        .otp-digit.filled { border-color: rgba(192,132,252,0.5); }
        @media (max-width: 480px) {
            .otp-digit { width: 44px; height: 54px; font-size: 1.4rem; }
        }

        /* ─── RESEND ROW ─── */
        .resend-row {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 4px;
            margin: 12px 0 8px;
            font-family: var(--mono);
            font-size: 0.6rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--text-muted);
            text-align: center;
        }
        .resend-btn {
            background: none; border: none;
            color: var(--accent);
            font-family: var(--mono);
            font-size: 0.62rem;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            cursor: pointer; padding: 0;
            transition: opacity 0.2s;
            outline: none;
        }
        .resend-btn:disabled { color: var(--text-muted); cursor: default; }
        .resend-btn:focus { outline: none; }

        /* ─── REMEMBER DEVICE ─── */
        .remember-device {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            margin: 4px auto 14px;
            width: fit-content;
            cursor: pointer !important;
            font-family: var(--mono);
            font-size: 0.62rem;
            letter-spacing: 0.08em;
            color: var(--text-muted);
            user-select: none;
            transition: color 0.2s;
        }
        .remember-device input[type="checkbox"] {
            appearance: none;
            -webkit-appearance: none;
            width: 15px; height: 15px;
            border: 1.5px solid var(--border-glow);
            border-radius: 3px;
            background: var(--surface2);
            cursor: pointer !important;
            flex-shrink: 0;
            transition: border-color 0.2s, background 0.2s, transform 0.15s;
            position: relative;
        }
        .remember-device input[type="checkbox"]:checked {
            background: var(--accent);
            border-color: var(--accent);
            transform: scale(1.15);
        }
        .remember-device input[type="checkbox"]:checked::after {
            content: '';
            position: absolute;
            left: 3px; top: 1px;
            width: 5px; height: 8px;
            border: 1.5px solid #fff;
            border-top: none; border-left: none;
            transform: rotate(45deg);
            animation: checkPop 0.2s ease-out;
        }
        @keyframes checkPop {
            0%   { opacity: 0; transform: rotate(45deg) scale(0.5); }
            60%  { transform: rotate(45deg) scale(1.2); }
            100% { opacity: 1; transform: rotate(45deg) scale(1); }
        }
        .remember-device span { user-select: none; }
        .remember-device:hover { color: var(--text); }
        .remember-device:hover input[type="checkbox"]:not(:checked) {
            border-color: var(--accent);
        }

        .back-link {
            display: inline-flex; align-items: center; gap: 6px;
            margin-top: 16px;
            font-family: var(--mono); font-size: 0.65rem;
            letter-spacing: 0.12em; text-transform: uppercase;
            color: var(--text-muted); cursor: pointer;
            transition: color 0.2s;
            background: none; border: none; outline: none;
            padding: 0; -webkit-appearance: none;
        }
        .back-link:hover { color: var(--accent); }
        .back-link:focus { outline: none; }

        /* ─── ALERT INFO ─── */
        .alert-info {
            background: var(--accent-dim);
            border: 1px solid rgba(192,132,252,0.25);
            color: var(--accent);
            flex-wrap: nowrap;
            justify-content: center;
            white-space: nowrap;
        }
        .alert-info strong { white-space: nowrap; }
        @media (max-width: 400px) {
            .alert-info { font-size: 0.72rem; }
        }

        /* ─── LOADING SCREEN ─── */
        .loading-screen {
            position: fixed;
            inset: 0;
            background: var(--bg);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-direction: column;
            gap: 20px;
            z-index: 999;
            transition: opacity 0.7s ease, visibility 0.7s ease;
        }
        .loading-screen.hide {
            opacity: 0;
            visibility: hidden;
        }
        .loading-logo {
            width: 110px;
            height: 110px;
            filter: drop-shadow(0 0 18px rgba(192,132,252,0.55));
            animation: logoPulse 1.6s ease-in-out infinite;
        }
        @keyframes logoPulse {
            0%, 100% { transform: scale(1);    filter: drop-shadow(0 0 18px rgba(192,132,252,0.5)); }
            50%       { transform: scale(1.07); filter: drop-shadow(0 0 32px rgba(192,132,252,0.85)); }
        }
        .loading-bar-track {
            width: 240px;
            height: 3px;
            background: var(--border);
            border-radius: 2px;
            overflow: hidden;
        }
        .loading-bar-fill {
            height: 100%;
            width: 40%;
            background: linear-gradient(90deg, transparent, var(--accent), transparent);
            border-radius: 2px;
            animation: barSweep 1.5s ease-in-out infinite;
        }
        @keyframes barSweep {
            0%   { transform: translateX(-200%); }
            100% { transform: translateX(400%); }
        }
        .loading-text {
            font-family: var(--mono);
            font-size: 0.8rem;
            letter-spacing: 0.25em;
            color: var(--text-muted);
            text-transform: uppercase;
        }

        /* ════════════════════════════════════════
           CUSTOM CURSOR — tylko dla myszy
        ════════════════════════════════════════ */
        @media (pointer: fine) { * { cursor: none !important; } }
        input, input * { cursor: text !important; caret-color: var(--accent); }
        input[type="checkbox"], label.remember-device, label.remember-device span { cursor: none !important; }

        #cur-arrow {
            position: fixed;
            width: 28px; height: 28px;
            pointer-events: none;
            z-index: 99999;
            top: 0; left: 0;
            opacity: 0;
            filter: drop-shadow(0 0 5px rgba(192,132,252,0.8))
                    drop-shadow(0 0 12px rgba(192,132,252,0.35));
            transition: filter 0.2s ease, opacity 0.2s ease;
        }
        #cur-arrow.is-morphed {
            filter: drop-shadow(0 0 7px rgba(192,132,252,1))
                    drop-shadow(0 0 18px rgba(192,132,252,0.5));
        }
        #cur-arrow.clicking {
            filter: drop-shadow(0 0 12px rgba(255,255,255,0.9))
                    drop-shadow(0 0 28px rgba(192,132,252,1));
        }
        #cur-ring {
            position: fixed;
            top: 0; left: 0;
            width: 36px; height: 36px;
            border: 1.5px solid rgba(192,132,252,0.45);
            border-radius: 50%;
            pointer-events: none;
            z-index: 99996;
            opacity: 0;
            transition:
                left   0.32s cubic-bezier(0.23, 1, 0.32, 1),
                top    0.32s cubic-bezier(0.23, 1, 0.32, 1),
                width  0.32s cubic-bezier(0.23, 1, 0.32, 1),
                height 0.32s cubic-bezier(0.23, 1, 0.32, 1),
                border-radius 0.32s cubic-bezier(0.23, 1, 0.32, 1),
                border-color  0.2s ease,
                opacity       0.2s ease;
            will-change: left, top, width, height, border-radius;
        }
        #cur-ring.is-morphed {
            border-color: rgba(192,132,252,0.85);
            border-width: 1px;
            opacity: 1;
            box-shadow: 0 0 12px rgba(192,132,252,0.15),
                        0 0 0 1px rgba(192,132,252,0.06);
        }
        .cur-ripple {
            position: fixed;
            width: 10px; height: 10px;
            border: 1px solid rgba(192,132,252,0.8);
            border-radius: 50%;
            pointer-events: none;
            z-index: 99997;
            animation: curRipple 0.55s ease-out forwards;
        }
        @keyframes curRipple {
            0%   { width: 10px;  height: 10px;  opacity: 0.85; transform: translate(-50%,-50%); }
            100% { width: 110px; height: 110px; opacity: 0;    transform: translate(-50%,-50%); }
        }
    </style>
</head>
<body>

    <!-- ═══ LOADING SCREEN ═══ -->
    <?php if (!$error && !$loggedOut && !$timedOut): ?>
    <div class="loading-screen" id="loading-screen">
        <img src="key.svg" class="loading-logo" alt="Secret Key">
        <div class="loading-bar-track">
            <div class="loading-bar-fill"></div>
        </div>
        <div class="loading-text">Odszyfrowywanie…</div>
    </div>
    <?php endif; ?>

    <!-- ═══ VERIFY SCREEN ═══ -->
    <div class="verify-screen" id="verify-screen">
        <img src="key.svg" class="loading-logo" alt="Secret Key">
        <div class="loading-text" id="verify-text">Weryfikowanie…</div>
    </div>

    <div id="particles"></div>

    <div class="page">
        <div id="card" class="login-card<?= $shake ? ' shake' : '' ?>">

            <img src="key.svg" class="card-logo" alt="Key">
            <h1 class="card-title">Secret Key</h1>
            <p class="card-subtitle">by Mateusz Karpierz</p>

            <div class="divider"></div>

            <div class="card-body">

                <!-- ══ KROK 1 — formularz logowania ══ -->
                <div class="step" id="step-login">

                    <?php if ($error): ?>
                    <div class="alert alert-error" id="alert-static">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <?= htmlspecialchars($error) ?>
                    </div>
                    <?php elseif ($loggedOut): ?>
                    <div class="alert alert-success" id="alert-static">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        Zostałeś wylogowany.
                    </div>
                    <?php elseif ($timedOut): ?>
                    <div class="alert alert-error" id="alert-static">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        Sesja wygasła z powodu nieaktywności.
                    </div>
                    <?php else: ?>
                    <p class="card-intro">Znajdujesz się na tej stronie, ponieważ jesteś posiadaczem <strong>1&nbsp;z&nbsp;5&nbsp;części</strong> kodu Secret Key.</p>
                    <div class="hint-box">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <span>Dane do logowania znajdują się na Twojej karcie&nbsp;<strong>Secret Key</strong>.</span>
                    </div>
                    <?php endif; ?>

                    <!-- Alert dynamiczny (błędy JS) -->
                    <div class="alert alert-error" id="alert-login" style="display:none">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <span id="alert-login-msg"></span>
                    </div>

                    <div class="field">
                        <label for="username">Login</label>
                        <input type="text" id="username" name="username"
                            placeholder="Twój login z karty"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                            autocomplete="username">
                    </div>

                    <div class="field">
                        <label for="password">Hasło</label>
                        <div class="field-password">
                            <input type="password" id="password" name="password"
                                placeholder="Twoje hasło z karty"
                                autocomplete="current-password">
                            <button type="button" class="toggle-password" id="toggle-pw" aria-label="Pokaż hasło">
                                <svg id="eye-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                                    <circle cx="12" cy="12" r="3"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <button type="button" class="submit-btn" id="btn-login">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2">
                            <path d="M15 3h4a2 2 0 0 1 2 2v14a2 2 0 0 1-2 2h-4"/>
                            <polyline points="10 17 15 12 10 7"/>
                            <line x1="15" y1="12" x2="3" y2="12"/>
                        </svg>
                        Zaloguj się
                    </button>

                </div><!-- end step-login -->

                <!-- ══ KROK 2 — weryfikacja SMS ══ -->
                <div class="step hidden" id="step-2fa">

                    <div class="alert alert-info" id="alert-info">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:2px">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                        Wysłano kod weryfikacyjny na&nbsp;<strong id="phone-display"></strong>
                    </div>

                    <div class="alert alert-error" id="alert-2fa" style="display:none">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:2px">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <span id="alert-2fa-msg"></span>
                    </div>

                    <p class="card-intro" style="margin-bottom:20px">Wpisz <strong>6-cyfrowy kod</strong> z wiadomości SMS, aby potwierdzić swoją tożsamość.</p>

                    <div class="otp-wrap">
                        <input class="otp-digit" type="text" inputmode="numeric" maxlength="1" id="d0" autocomplete="one-time-code">
                        <input class="otp-digit" type="text" inputmode="numeric" maxlength="1" id="d1">
                        <input class="otp-digit" type="text" inputmode="numeric" maxlength="1" id="d2">
                        <input class="otp-digit" type="text" inputmode="numeric" maxlength="1" id="d3">
                        <input class="otp-digit" type="text" inputmode="numeric" maxlength="1" id="d4">
                        <input class="otp-digit" type="text" inputmode="numeric" maxlength="1" id="d5">
                    </div>

                    <label class="remember-device">
                        <input type="checkbox" id="remember-device">
                        <span>Zapamiętaj to urządzenie na 7 dni</span>
                    </label>

                    <button type="button" class="submit-btn" id="btn-verify">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        Weryfikuj
                    </button>

                    <div class="resend-row">
                        <span>Nie dostałeś SMS-a?</span>
                        <button class="resend-btn" id="resend-btn" disabled>
                            Wyślij kod ponownie za (<span id="resend-timer">60</span> <span id="resend-unit">sekund</span>)
                        </button>
                    </div>

                    <div style="text-align:center">
                        <button class="back-link" id="back-btn">
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="15 18 9 12 15 6"/>
                            </svg>
                            Wróć do logowania
                        </button>
                    </div>

                </div><!-- end step-2fa -->

            </div><!-- end card-body -->

            <p class="card-footer">obszar zastrzeżony &nbsp;·&nbsp; autoryzacja wymagana</p>
        </div>
    </div>

    <script src="prevent-actions.js"></script>
    <script>
    var CSRF_TOKEN = '<?= generateCsrfToken() ?>';

    // ─── Loading screen ───
    var loadingScreen = document.getElementById('loading-screen');
    if (loadingScreen) {
        setTimeout(function() { loadingScreen.classList.add('hide'); }, 5500);
    }

    // ─── Step references ───
    var stepLogin = document.getElementById('step-login');
    var step2fa   = document.getElementById('step-2fa');
    var card      = document.getElementById('card');
    var mx = -200, my = -200;

    function switchStep(from, to, cb) {
        var ring = document.getElementById('cur-ring');
        if (ring) {
            ring.classList.remove('is-morphed');
            ring.style.transition   = 'none';
            ring.style.width        = '36px';
            ring.style.height       = '36px';
            ring.style.borderRadius = '50%';
            ring.style.left         = (mx - 18) + 'px';
            ring.style.top          = (my - 18) + 'px';
            requestAnimationFrame(function() {
                requestAnimationFrame(function() { ring.style.transition = ''; });
            });
        }
        var arrow = document.getElementById('cur-arrow');
        if (arrow) arrow.classList.remove('is-morphed');

        from.style.opacity   = '0';
        from.style.transform = 'translateY(-10px)';
        setTimeout(function() {
            from.classList.add('hidden');
            to.classList.remove('hidden');
            to.style.opacity   = '0';
            to.style.transform = 'translateY(10px)';
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    to.style.opacity   = '1';
                    to.style.transform = 'translateY(0)';
                    if (cb) cb();
                });
            });
        }, 350);
    }

    // ─── Krok 1: wysyłanie formularza ───
    document.getElementById('btn-login').addEventListener('click', function() {
        var username = document.getElementById('username').value.trim();
        var password = document.getElementById('password').value;
        var alertEl  = document.getElementById('alert-login');
        var alertMsg = document.getElementById('alert-login-msg');

        function showLoginError(msg, shake) {
            alertMsg.textContent = msg;
            alertEl.style.display = '';
            var intro   = document.querySelector('.card-intro');
            var hintBox = document.querySelector('.hint-box');
            var staticAlert = document.getElementById('alert-static');
            if (intro)       intro.style.display       = 'none';
            if (hintBox)     hintBox.style.display      = 'none';
            if (staticAlert) staticAlert.style.display  = 'none';
            if (shake) {
                card.classList.remove('shake');
                void card.offsetWidth;
                card.classList.add('shake');
            }
        }

        alertEl.style.display = 'none';

        if (!username || !password) {
            showLoginError('Wpisz login i hasło.', true);
            return;
        }

        // Pokaż verify screen
        var verifyScreen = document.getElementById('verify-screen');
        var text   = document.getElementById('verify-text');
        verifyScreen.classList.add('show');
        var body = new URLSearchParams({ username: username, password: password, csrf_token: CSRF_TOKEN });

        fetch('login.php', { method: 'POST', body: body })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.status === 'trusted') {
                    // Zaufane urządzenie — overlay zostaje z nowym tekstem, redirect
                    text.textContent = 'Witaj ponownie…';
                    setTimeout(function() {
                        window.location.href = data.redirect;
                    }, 800);
                } else if (data.status === 'ok') {
                    document.getElementById('phone-display').textContent = data.phone_masked;
                    document.getElementById('alert-2fa').style.display  = 'none';
                    document.getElementById('alert-info').style.display = '';
                    setTimeout(function() {
                        // Pokaż krok 2 z lekkim fade-in po zniknięciu overlay
                        stepLogin.style.transition = 'none';
                        stepLogin.style.opacity    = '0';
                        stepLogin.classList.add('hidden');
                        step2fa.style.transition = 'none';
                        step2fa.style.opacity    = '0';
                        step2fa.style.transform  = 'translateY(14px) scale(0.99)';
                        step2fa.classList.remove('hidden');
                        verifyScreen.classList.remove('show');
                        verifyScreen.classList.add('hide-instant');
                        setTimeout(function() {
                            verifyScreen.classList.remove('hide-instant');
                            stepLogin.style.transition = '';
                            stepLogin.style.opacity    = '';
                            step2fa.style.transition = 'opacity 0.45s cubic-bezier(0.22,1,0.36,1), transform 0.45s cubic-bezier(0.22,1,0.36,1)';
                            step2fa.style.opacity    = '1';
                            step2fa.style.transform  = 'translateY(0) scale(1)';
                            setTimeout(function() {
                                step2fa.style.transition = '';
                                step2fa.style.opacity    = '';
                                step2fa.style.transform  = '';
                            }, 450);
                            startTimer(<?= max(0, RESEND_COOLDOWN - (time() - ($_SESSION['sms_sent_at'] ?? 0))) ?>);
                            document.getElementById('d0').focus();
                            if ('OTPCredential' in window) {
                                navigator.credentials.get({ otp: { transport: ['sms'] } })
                                    .then(function(otp) {
                                        var chars = otp.code.split('');
                                        chars.forEach(function(ch, i) {
                                            var d = document.getElementById('d' + i);
                                            if (d) { d.value = ch; d.classList.add('filled'); }
                                        });
                                        document.getElementById('btn-verify').click();
                                    })
                                    .catch(function() {});
                            }
                        }, 50);
                    }, 2000);
                } else {
                    verifyScreen.classList.remove('show');
                    showLoginError(data.message || 'Wystąpił błąd.', data.shake);
                }
            })
            .catch(function(err) {
                verifyScreen.classList.remove('show');
                showLoginError('Błąd połączenia. Spróbuj ponownie.', false);
            });
    });

    // Enter w polach logowania
    ['username','password'].forEach(function(id) {
        document.getElementById(id).addEventListener('keydown', function(e) {
            if (e.key === 'Enter') document.getElementById('btn-login').click();
        });
    });

    // ─── Krok 2: weryfikacja kodu ───
    var digits = Array.from(document.querySelectorAll('.otp-digit'));

    digits.forEach(function(input, i) {
        input.addEventListener('input', function() {
            var val = this.value.replace(/\D/g, '');
            this.value = val ? val[0] : '';
            if (val && i < digits.length - 1) digits[i + 1].focus();
            this.classList.toggle('filled', !!this.value);
            // Автосабмит при заповненні всіх
            if (digits.every(function(d) { return d.value !== ''; })) {
                setTimeout(function() { document.getElementById('btn-verify').click(); }, 120);
            }
        });
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && i > 0) {
                digits[i - 1].focus();
                digits[i - 1].value = '';
                digits[i - 1].classList.remove('filled');
            }
        });
        input.addEventListener('paste', function(e) {
            e.preventDefault();
            var text = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            text.split('').slice(0, 6).forEach(function(ch, j) {
                if (digits[i + j]) { digits[i + j].value = ch; digits[i + j].classList.add('filled'); }
            });
            digits[Math.min(i + text.length, digits.length - 1)].focus();
        });
    });

    document.getElementById('btn-verify').addEventListener('click', function() {
        var code = digits.map(function(d) { return d.value; }).join('');
        if (code.length !== 6) return;

        var alertEl   = document.getElementById('alert-2fa');
        var alertMsg  = document.getElementById('alert-2fa-msg');
        var alertInfo = document.getElementById('alert-info');
        alertEl.style.display = 'none';

        var btn = this;
        btn.disabled = true;

        var remember = document.getElementById('remember-device').checked;
        var body = JSON.stringify({ code: code, remember: remember, csrf_token: CSRF_TOKEN });

        fetch('verify.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: body
        })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                btn.disabled = false;

                if (data.status === 'ok') {
                    // Pokaż verify screen i przekieruj
                    var verifyScreen = document.getElementById('verify-screen');
                    var text   = document.getElementById('verify-text');
                    text.textContent = 'Weryfikowanie…';
                    verifyScreen.classList.add('show');
                    setTimeout(function() {
                        window.location.href = data.redirect;
                    }, 800);
                } else {
                    // Ukryj "Wysłano kod..." gdy pojawia się błąd
                    alertInfo.style.display = 'none';
                    alertMsg.textContent = data.message || 'Wystąpił błąd.';
                    alertEl.style.display = '';
                    card.classList.remove('shake');
                    void card.offsetWidth;
                    card.classList.add('shake');

                    if (data.status === 'expired' || data.status === 'blocked') {
                        // Wróć do kroku 1
                        setTimeout(function() {
                            switchStep(step2fa, stepLogin, function() {
                                digits.forEach(function(d) { d.value = ''; d.classList.remove('filled'); });
                            });
                        }, 2000);
                    } else {
                        // Wyczyść pola i fokus na pierwszym
                        digits.forEach(function(d) { d.value = ''; d.classList.remove('filled'); });
                        document.getElementById('d0').focus();
                    }
                }
            })
            .catch(function() {
                btn.disabled = false;
                alertInfo.style.display = 'none';
                alertMsg.textContent = 'Błąd połączenia. Spróbuj ponownie.';
                alertEl.style.display = '';
            });
    });

    // Wróć do logowania
    document.getElementById('back-btn').addEventListener('click', function() {
        clearInterval(timerInterval);
        stepLogin.style.transition = '';
        stepLogin.style.opacity    = '';
        step2fa.style.transition   = '';
        step2fa.style.opacity      = '';
        step2fa.style.transform    = '';
        switchStep(step2fa, stepLogin, function() {
            digits.forEach(function(d) { d.value = ''; d.classList.remove('filled'); });
            document.getElementById('alert-2fa').style.display  = 'none';
            document.getElementById('alert-info').style.display = '';
            document.getElementById('username').value  = '';
            document.getElementById('password').value  = '';
            document.getElementById('username').focus();
        });
    });

    document.querySelector('label.remember-device').addEventListener('mousedown', function(e) {
        e.preventDefault();
    });

    // Wyślij ponownie
    var timerInterval;
    function odmienSekund(n) {
        if (n === 1) return 'sekunda';
        if (n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20)) return 'sekundy';
        return 'sekund';
    }
    function startTimer(seconds) {
        clearInterval(timerInterval);
        var timeLeft  = (typeof seconds === 'number' && seconds > 0) ? Math.ceil(seconds) : 60;
        var resendBtn = document.getElementById('resend-btn');
        var timerEl   = document.getElementById('resend-timer');
        var unitEl    = document.getElementById('resend-unit');
        resendBtn.disabled = true;
        resendBtn.innerHTML = 'Wyślij kod ponownie za (<span id="resend-timer">' + timeLeft + '</span> <span id="resend-unit">' + odmienSekund(timeLeft) + '</span>)';
        timerEl = document.getElementById('resend-timer');
        unitEl  = document.getElementById('resend-unit');
        timerInterval = setInterval(function() {
            timeLeft--;
            timerEl.textContent = timeLeft;
            unitEl.textContent  = odmienSekund(timeLeft);
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                resendBtn.disabled = false;
                resendBtn.innerHTML = 'Wyślij ponownie';
            }
        }, 1000);
    }
    document.getElementById('resend-btn').addEventListener('click', function() {
        if (this.disabled) return;
        startTimer();
        digits.forEach(function(d) { d.value = ''; d.classList.remove('filled'); });
        document.getElementById('d0').focus();
        // Przywróć "Wysłano kod...", ukryj alert błędu
        document.getElementById('alert-info').style.display = '';
        document.getElementById('alert-2fa').style.display  = 'none';
        fetch('resend.php', { method: 'POST' })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.status !== 'ok') {
                    var alertEl  = document.getElementById('alert-2fa');
                    var alertMsg = document.getElementById('alert-2fa-msg');
                    document.getElementById('alert-info').style.display = 'none';
                    alertMsg.textContent = data.message || 'Nie udało się wysłać SMS.';
                    alertEl.style.display = '';
                }
            })
            .catch(function() {});
    });

    // ─── Password show/hide toggle ───
    document.getElementById('toggle-pw').addEventListener('click', function() {
        var input = document.getElementById('password');
        var icon  = document.getElementById('eye-icon');
        if (input.type === 'password') {
            input.type = 'text';
            icon.innerHTML = '<path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/>';
        } else {
            input.type = 'password';
            icon.innerHTML = '<path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>';
        }
    });

    // ─── Particles ───
    (function() {
        var container = document.getElementById('particles');
        var chars = '0123456789';
        var particles = [];
        var style = document.createElement('style');
        style.textContent =
            '@keyframes float        {0%,100%{transform:translateY(0)}50%{transform:translateY(180px)}}' +
            '@keyframes floatReverse {0%,100%{transform:translateY(0)}50%{transform:translateY(-180px)}}' +
            '@keyframes float2       {0%,100%{transform:translateY(0)}50%{transform:translateY(28px)}}' +
            '@keyframes floatReverse2{0%,100%{transform:translateY(0)}50%{transform:translateY(-28px)}}';
        document.head.appendChild(style);
        var animations = ['float','floatReverse','float2','floatReverse2'];
        function sr(s) { var x = Math.sin(s+1)*10000; return x-Math.floor(x); }
        for (var i = 0; i < 80; i++) {
            var span = document.createElement('span');
            span.className = 'particle';
            span.textContent = chars[Math.floor(sr(i*11)*10)];
            span.style.cssText =
                'top:'+(sr(i*3)*96+1).toFixed(2)+'%;'+
                'left:'+(sr(i*3+1)*97+0.5).toFixed(2)+'%;'+
                'font-size:'+Math.floor(sr(i*3+2)*20+11)+'px;'+
                'filter:blur('+(i*0.02).toFixed(2)+'px);'+
                'animation:'+Math.floor(sr(i*7)*20+20)+'s '+animations[i%4]+' infinite -'+(sr(i*5)*30).toFixed(1)+'s';
            container.appendChild(span);
            particles.push(span);
        }
        setInterval(function() {
            for (var j = 0; j < 5; j++) {
                var idx = Math.floor(Math.random()*particles.length);
                particles[idx].style.color = 'rgba(192,132,252,0.55)';
                particles[idx].textContent = chars[Math.floor(Math.random()*10)];
                (function(p){ setTimeout(function(){ p.style.color=''; }, 400); })(particles[idx]);
            }
        }, 1800);
    })();
    </script>

    <!-- ══ CUSTOM CURSOR ══ -->
    <svg id="cur-arrow" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path d="M4 2 L20 11 L13 13 L10 20 Z"
            fill="none" stroke="#c084fc" stroke-width="1.4"
            stroke-linejoin="round" stroke-linecap="round"/>
    </svg>
    <div id="cur-ring"></div>

    <script>
    if (window.matchMedia('(pointer: fine)').matches) {
    (function() {
        var arrow = document.getElementById('cur-arrow');
        var ring  = document.getElementById('cur-ring');
        var rx = -200, ry = -200;
        var isMorphed   = false;
        var morphTarget = null;
        var cursorVisible = false;

        // Elementy morphujące na login: karta, przyciski, pola
        var MORPH_ALL = '.submit-btn, .field-password, #username, .otp-digit';

        // ─── Ruch myszy ───
        document.addEventListener('mousemove', function(e) {
            mx = e.clientX;
            my = e.clientY;
            arrow.style.left = mx + 'px';
            arrow.style.top  = my + 'px';
            if (!cursorVisible) {
                cursorVisible = true;
                arrow.style.opacity = '1';
                ring.style.opacity  = '0.75';
            }
            if (isMorphed && morphTarget) applyMorph(morphTarget);
        });

        // ─── Lerp loop ───
        (function lerpLoop() {
            if (!isMorphed) {
                rx += (mx - rx) * 0.13;
                ry += (my - ry) * 0.13;
                ring.style.left = (rx - 18) + 'px';
                ring.style.top  = (ry - 18) + 'px';
            }
            requestAnimationFrame(lerpLoop);
        })();

        function applyMorph(el) {
            var r  = el.getBoundingClientRect();
            // Jeśli to wrapper pola hasła — weź border-radius z inputa w środku
            var brSource = el.classList.contains('field-password')
                ? (el.querySelector('input') || el)
                : el;
            var br = getComputedStyle(brSource).borderRadius || '10px';
            // Dla field-password użyj wymiarów inputa (nie wrappera)
            if (el.classList.contains('field-password')) {
                var inp = el.querySelector('input');
                if (inp) r = inp.getBoundingClientRect();
            }
            // Opcjonalne zmniejszenie ringa (np. żeby nie nachodził na sąsiednie elementy)
            var shrink = parseInt(el.dataset.morphShrink || '0', 10);
            ring.style.left         = (r.left   + shrink) + 'px';
            ring.style.top          = (r.top    + shrink) + 'px';
            ring.style.width        = (r.width  - shrink * 2) + 'px';
            ring.style.height       = (r.height - shrink * 2) + 'px';
            ring.style.borderRadius = br;
        }

        function resetToCircle() {
            ring.style.transition = 'none';
            ring.style.width        = '36px';
            ring.style.height       = '36px';
            ring.style.borderRadius = '50%';
            ring.style.left = (mx - 18) + 'px';
            ring.style.top  = (my - 18) + 'px';
            rx = mx; ry = my;
            requestAnimationFrame(function() {
                requestAnimationFrame(function() { ring.style.transition = ''; });
            });
        }

        document.addEventListener('mouseover', function(e) {
            var target = e.target.closest(MORPH_ALL);
            if (!target) return;
            isMorphed   = true;
            morphTarget = target;
            arrow.classList.add('is-morphed');
            ring.classList.add('is-morphed');
            applyMorph(target);
        });

        document.addEventListener('mouseout', function(e) {
            var target = e.target.closest(MORPH_ALL);
            if (!target) return;
            var related = e.relatedTarget;
            if (related && related.closest(MORPH_ALL)) return;
            isMorphed   = false;
            morphTarget = null;
            arrow.classList.remove('is-morphed');
            ring.classList.remove('is-morphed');
            resetToCircle();
        });

        // ─── Click ripple ───
        document.addEventListener('mousedown', function(e) {
            arrow.classList.add('clicking');
            var r = document.createElement('div');
            r.className = 'cur-ripple';
            r.style.left = e.clientX + 'px';
            r.style.top  = e.clientY + 'px';
            document.body.appendChild(r);
            setTimeout(function() { r.remove(); }, 600);
        });
        document.addEventListener('mouseup', function() {
            arrow.classList.remove('clicking');
        });

        // ─── Schowaj przy wyjściu z okna ───
        document.addEventListener('mouseleave', function() {
            arrow.style.opacity = '0';
            ring.style.opacity  = '0';
        });
        document.addEventListener('mouseenter', function() {
            arrow.style.opacity = '1';
            ring.style.opacity  = '0.75';
        });

        // ─── Input — schowaj strzałkę, ring zostaje (morphuje na pole) ───
        document.querySelectorAll('input').forEach(function(el) {
            el.addEventListener('mouseenter', function() {
                arrow.style.opacity = '0';
            });
            el.addEventListener('mouseleave', function() {
                arrow.style.opacity = '1';
            });
        });

    })();
    } // end pointer: fine
    </script>
</body>
</html>
