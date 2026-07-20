<?php
// SPDX-License-Identifier: MIT
// Copyright (c) 2026 Mateusz Karpierz (karpierz.me)
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
        echo json_encode(['status' => 'error', 'message' => t('common_error_bad_token'), 'shake' => false]);
        exit;
    }

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        echo json_encode(['status' => 'error', 'message' => t('login_empty_fields'), 'shake' => true]);
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
<html lang="<?= htmlspecialchars(t('_html_lang')) ?>">
<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1.0" name="viewport">
    <meta name="author" content="Mateusz Karpierz">
    <meta name="robots" content="noindex,nofollow">
    <meta name="googlebot" content="noindex">
    <title><?= htmlspecialchars(t('login_page_title')) ?></title>
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
    
/* ══ JOKER SCREEN v3 ══ */
#sk-joker{position:fixed;inset:0;z-index:2147483647;display:flex;align-items:center;justify-content:center;padding:32px 24px;background:#0a0c10;text-align:center;opacity:0;visibility:hidden;transition:opacity 0.45s cubic-bezier(0.22,1,0.36,1),visibility 0.45s;box-sizing:border-box;font-family:'Syne',sans-serif;overflow:hidden;}
#sk-joker.sk-visible{opacity:1;visibility:visible}
.sk-jk-inner{position:relative;z-index:2;width:100%;max-width:480px;display:flex;flex-direction:column;align-items:stretch;}
.sk-jk-header{display:flex;align-items:center;justify-content:space-between;padding:12px 20px;background:#111318;border:1px solid #222630;border-radius:12px 12px 0 0;border-bottom:none;}
.sk-jk-header-left{display:flex;align-items:center;gap:10px;}
.sk-jk-status-dot{width:8px;height:8px;border-radius:50%;background:#f87171;animation:skPulse 1.5s ease-in-out infinite;flex-shrink:0;}
@keyframes skPulse{0%,100%{box-shadow:0 0 0 0 rgba(248,113,113,0.4)}50%{box-shadow:0 0 0 6px rgba(248,113,113,0)}}
.sk-jk-header-title{font-family:'Space Mono',monospace;font-size:0.62rem;letter-spacing:0.18em;text-transform:uppercase;color:#64748b;}
.sk-jk-header-id{font-family:'Space Mono',monospace;font-size:0.58rem;color:#2a3044;letter-spacing:0.1em;}
.sk-jk-body{background:#111318;border:1px solid #222630;padding:32px 32px 28px;display:flex;flex-direction:column;align-items:center;}
.sk-jk-icon{position:relative;margin-bottom:24px;display:flex;align-items:center;justify-content:center;}
.sk-jk-ring{position:absolute;border-radius:50%;border:1px solid rgba(192,132,252,0.1);}
.sk-jk-ring-1{width:100px;height:100px;animation:skRing 3s ease-in-out infinite;}
.sk-jk-ring-2{width:130px;height:130px;animation:skRing 3s ease-in-out infinite 0.5s;}
.sk-jk-ring-3{width:160px;height:160px;animation:skRing 3s ease-in-out infinite 1s;}
@keyframes skRing{0%,100%{opacity:0.3;transform:scale(1)}50%{opacity:0.7;transform:scale(1.03)}}
.sk-jk-icon-core{width:72px;height:72px;border-radius:50%;background:#181c24;border:1px solid #2a3044;display:flex;align-items:center;justify-content:center;position:relative;z-index:1;}
.sk-jk-title{font-family:'Bungee',Impact,sans-serif;font-size:clamp(1.1rem,4vw,1.6rem);letter-spacing:0.06em;text-transform:uppercase;color:#e2e8f0;margin-bottom:6px;text-align:center;}
.sk-jk-desc{font-family:'Space Mono',monospace;font-size:0.68rem;color:#94a3b8;line-height:1.7;text-align:center;max-width:320px;margin-bottom:28px;}
.sk-jk-divider{width:100%;display:flex;align-items:center;gap:12px;margin-bottom:22px;}
.sk-jk-divider-line{flex:1;height:1px;background:#222630;}
.sk-jk-divider-text{font-family:'Space Mono',monospace;font-size:0.52rem;letter-spacing:0.2em;text-transform:uppercase;color:#64748b;white-space:nowrap;}
.sk-jk-violations{width:100%;display:flex;flex-direction:column;gap:6px;margin-bottom:24px;}
.sk-jk-viol{display:flex;align-items:center;gap:10px;padding:9px 14px;background:#181c24;border:1px solid #222630;border-radius:8px;border-left:2px solid #c084fc;}
.sk-jk-viol-icon{width:18px;height:18px;border-radius:4px;background:rgba(192,132,252,0.12);display:flex;align-items:center;justify-content:center;flex-shrink:0;}
.sk-jk-viol-text{font-family:'Space Mono',monospace;font-size:0.64rem;color:#94a3b8;flex:1;text-align:left;}
.sk-jk-viol-tag{font-family:'Space Mono',monospace;font-size:0.5rem;letter-spacing:0.12em;text-transform:uppercase;padding:2px 8px;border-radius:99px;background:rgba(192,132,252,0.1);color:#c084fc;border:1px solid rgba(192,132,252,0.2);white-space:nowrap;}
.sk-jk-viol-tag.red{background:rgba(248,113,113,0.1);color:#f87171;border-color:rgba(248,113,113,0.2);}
.sk-jk-timer-wrap{width:100%;margin-bottom:8px;}
.sk-jk-timer-label{display:flex;justify-content:space-between;margin-bottom:8px;}
.sk-jk-timer-lbl{font-family:'Space Mono',monospace;font-size:0.52rem;letter-spacing:0.15em;text-transform:uppercase;color:#64748b;}
.sk-jk-timer-val{font-family:'Space Mono',monospace;font-size:0.52rem;color:#c084fc;}
.sk-jk-timer-track{width:100%;height:3px;background:#181c24;border-radius:99px;position:relative;overflow:hidden;}
.sk-jk-timer-pulse{position:absolute;top:0;left:-40%;height:100%;width:40%;background:linear-gradient(90deg,transparent,#818cf8,#c084fc,#818cf8,transparent);border-radius:99px;animation:skTimerPulse 2.4s ease-in-out infinite;}
@keyframes skTimerPulse{0%{left:-40%;opacity:0.6}50%{opacity:1}100%{left:100%;opacity:0.6}}
.sk-jk-footer{background:#111318;border:1px solid #222630;border-radius:0 0 12px 12px;border-top:none;padding:12px 20px;display:flex;align-items:center;justify-content:space-between;}
.sk-jk-footer-text{font-family:'Space Mono',monospace;font-size:0.54rem;letter-spacing:0.12em;text-transform:uppercase;color:#2a3044;}
.sk-jk-footer-badge{display:flex;align-items:center;gap:6px;font-family:'Space Mono',monospace;font-size:0.52rem;color:#4ade80;letter-spacing:0.1em;}
.sk-jk-footer-badge-dot{width:5px;height:5px;border-radius:50%;background:#4ade80;animation:skBlink2 2s ease-in-out infinite;}
@keyframes skBlink2{0%,100%{opacity:1}50%{opacity:0.3}}
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
        <div class="loading-text"><?= htmlspecialchars(t('twofa_decrypting')) ?></div>
    </div>
    <?php endif; ?>

    <!-- ═══ VERIFY SCREEN ═══ -->
    <div class="verify-screen" id="verify-screen">
        <img src="key.svg" class="loading-logo" alt="Secret Key">
        <div class="loading-text" id="verify-text"><?= htmlspecialchars(t('twofa_verifying')) ?></div>
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
                        <?= htmlspecialchars(t('login_session_expired')) ?>
                    </div>
                    <?php else: ?>
                    <p class="card-intro"><?= t('login_card_intro') ?></p>
                    <div class="hint-box">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <span><?= t('login_hint_box') ?></span>
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
                        <label for="username"><?= htmlspecialchars(t('login_label_username')) ?></label>
                        <input type="text" id="username" name="username"
                            placeholder="<?= htmlspecialchars(t('login_placeholder_username')) ?>"
                            value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                            autocomplete="username">
                    </div>

                    <div class="field">
                        <label for="password"><?= htmlspecialchars(t('login_label_password')) ?></label>
                        <div class="field-password">
                            <input type="password" id="password" name="password"
                                placeholder="<?= htmlspecialchars(t('login_placeholder_password')) ?>"
                                autocomplete="current-password">
                            <button type="button" class="toggle-password" id="toggle-pw" aria-label="<?= htmlspecialchars(t('login_show_password')) ?>">
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
                        <?= htmlspecialchars(t('login_submit_btn')) ?>
                    </button>

                </div><!-- end step-login -->

                <!-- ══ KROK 2 — weryfikacja SMS ══ -->
                <div class="step hidden" id="step-2fa">

                    <div class="alert alert-info" id="alert-info">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:2px">
                            <path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6A19.79 19.79 0 0 1 2.12 4.18 2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/>
                        </svg>
                        <?= t('twofa_sent_to') ?><strong id="phone-display"></strong>
                    </div>

                    <div class="alert alert-error" id="alert-2fa" style="display:none">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="flex-shrink:0;margin-top:2px">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <span id="alert-2fa-msg"></span>
                    </div>

                    <p class="card-intro" style="margin-bottom:20px"><?= t('twofa_intro') ?></p>

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
                        <span><?= htmlspecialchars(t('twofa_remember_device')) ?></span>
                    </label>

                    <button type="button" class="submit-btn" id="btn-verify">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"/>
                        </svg>
                        <?= htmlspecialchars(t('twofa_verify_btn')) ?>
                    </button>

                    <div class="resend-row">
                        <span><?= htmlspecialchars(t('twofa_no_sms_question')) ?></span>
                        <button class="resend-btn" id="resend-btn" disabled>
                            <?= t('twofa_resend_btn_waiting') ?>60</span> <span id="resend-unit">sekund</span>)
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

            <p class="card-footer"><?= t('login_footer_restricted') ?></p>
        </div>
    </div>

    <script>
    var CSRF_TOKEN = '<?= generateCsrfToken() ?>';
    // ─── Teksty UI wstrzykiwane z private/lang.php (patrz t() w auth.php) ───
    var I18N = {
        emptyFields:      '<?= addslashes(t('login_empty_fields')) ?>',
        welcomeBack:      '<?= addslashes(t('login_welcome_back')) ?>',
        genericError:     '<?= addslashes(t('common_error_generic')) ?>',
        connectionError:  '<?= addslashes(t('common_error_connection')) ?>',
        resendSending:    '<?= addslashes(t('twofa_resend_sending')) ?>',
        resendReady:      '<?= addslashes(t('twofa_resend_btn_ready')) ?>',
        smsSendFailed:    '<?= addslashes(t('resend_sms_failed')) ?>',
        verifying:        '<?= addslashes(t('twofa_verifying')) ?>',
        resendWaitingPrefix: '<?= addslashes(t('twofa_resend_btn_waiting')) ?>',
        unit1:            '<?= addslashes(t('twofa_resend_unit_1')) ?>',
        unitFew:          '<?= addslashes(t('twofa_resend_unit_few')) ?>',
        unitMany:         '<?= addslashes(t('twofa_resend_unit_many')) ?>',
        refPrefix:        '<?= addslashes(t('jokescreen_ref_prefix')) ?>'
    };

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
            showLoginError(I18N.emptyFields, true);
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
                    text.textContent = I18N.welcomeBack;
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
                    showLoginError(data.message || I18N.genericError, data.shake);
                }
            })
            .catch(function(err) {
                verifyScreen.classList.remove('show');
                showLoginError(I18N.connectionError, false);
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
                    text.textContent = I18N.verifying;
                    verifyScreen.classList.add('show');
                    setTimeout(function() {
                        window.location.href = data.redirect;
                    }, 800);
                } else {
                    // Ukryj "Wysłano kod..." gdy pojawia się błąd
                    alertInfo.style.display = 'none';
                    alertMsg.textContent = data.message || I18N.genericError;
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
                alertMsg.textContent = I18N.connectionError;
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
        if (n === 1) return I18N.unit1;
        if (n % 10 >= 2 && n % 10 <= 4 && (n % 100 < 10 || n % 100 >= 20)) return I18N.unitFew;
        return I18N.unitMany;
    }
    function startTimer(seconds) {
        clearInterval(timerInterval);
        var timeLeft  = (typeof seconds === 'number' && seconds > 0) ? Math.ceil(seconds) : 60;
        var resendBtn = document.getElementById('resend-btn');
        var timerEl   = document.getElementById('resend-timer');
        var unitEl    = document.getElementById('resend-unit');
        resendBtn.disabled = true;
        resendBtn.innerHTML = I18N.resendWaitingPrefix + timeLeft + '</span> <span id="resend-unit">' + odmienSekund(timeLeft) + '</span>)';
        timerEl = document.getElementById('resend-timer');
        unitEl  = document.getElementById('resend-unit');
        timerInterval = setInterval(function() {
            timeLeft--;
            timerEl.textContent = timeLeft;
            unitEl.textContent  = odmienSekund(timeLeft);
            if (timeLeft <= 0) {
                clearInterval(timerInterval);
                resendBtn.disabled = false;
                resendBtn.innerHTML = I18N.resendReady;
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
        fetch('resend.php', { method: 'POST', headers: {'Content-Type':'application/x-www-form-urlencoded'}, body: new URLSearchParams({ csrf_token: CSRF_TOKEN }) })
            .then(function(r) { return r.json(); })
            .then(function(data) {
                if (data.status !== 'ok') {
                    var alertEl  = document.getElementById('alert-2fa');
                    var alertMsg = document.getElementById('alert-2fa-msg');
                    document.getElementById('alert-info').style.display = 'none';
                    alertMsg.textContent = data.message || I18N.smsSendFailed;
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
<!-- ══ JOKER SCREEN ══ -->
<div id="sk-joker" aria-hidden="true">
  <div class="sk-jk-inner">
    <div class="sk-jk-header">
      <div class="sk-jk-header-left">
        <div class="sk-jk-status-dot"></div>
        <span class="sk-jk-header-title"><?= htmlspecialchars(t('jokescreen_title')) ?></span>
      </div>
      <span class="sk-jk-header-id" id="sk-ref"><?= htmlspecialchars(t('jokescreen_ref_prefix')) ?>00000</span>
    </div>
    <div class="sk-jk-body">
      <div class="sk-jk-icon">
        <div class="sk-jk-ring sk-jk-ring-3"></div>
        <div class="sk-jk-ring sk-jk-ring-2"></div>
        <div class="sk-jk-ring sk-jk-ring-1"></div>
        <div class="sk-jk-icon-core">
          <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#c084fc" stroke-width="1.6" stroke-linecap="round">
            <rect x="3" y="11" width="18" height="11" rx="2"/>
            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
            <circle cx="12" cy="16" r="1.5" fill="#c084fc" stroke="none"/>
          </svg>
        </div>
      </div>
      <div class="sk-jk-title"><?= htmlspecialchars(t('jokescreen_access_halted')) ?></div>
      <div class="sk-jk-desc"><?= htmlspecialchars(t('jokescreen_subtitle')) ?><br><?= htmlspecialchars(t('jokescreen_subtitle_2')) ?></div>
      <div class="sk-jk-divider">
        <div class="sk-jk-divider-line"></div>
        <span class="sk-jk-divider-text"><?= htmlspecialchars(t('jokescreen_violations_label')) ?></span>
        <div class="sk-jk-divider-line"></div>
      </div>
      <div class="sk-jk-violations">
        <div class="sk-jk-viol">
          <div class="sk-jk-viol-icon"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#c084fc" stroke-width="2.5" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></div>
          <span class="sk-jk-viol-text"><?= htmlspecialchars(t('jokescreen_violation_1')) ?></span>
          <span class="sk-jk-viol-tag red"><?= htmlspecialchars(t('jokescreen_detected_tag')) ?></span>
        </div>
        <div class="sk-jk-viol">
          <div class="sk-jk-viol-icon"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#c084fc" stroke-width="2.5" stroke-linecap="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg></div>
          <span class="sk-jk-viol-text"><?= htmlspecialchars(t('jokescreen_violation_2')) ?></span>
          <span class="sk-jk-viol-tag red"><?= htmlspecialchars(t('jokescreen_detected_tag')) ?></span>
        </div>
        <div class="sk-jk-viol">
          <div class="sk-jk-viol-icon"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#c084fc" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg></div>
          <span class="sk-jk-viol-text"><?= htmlspecialchars(t('jokescreen_violation_3')) ?></span>
          <span class="sk-jk-viol-tag red"><?= htmlspecialchars(t('jokescreen_detected_tag')) ?></span>
        </div>
      </div>
      <div class="sk-jk-timer-wrap">
        <div class="sk-jk-timer-label">
          <span class="sk-jk-timer-lbl"><?= htmlspecialchars(t('jokescreen_time_label')) ?></span>
          <span class="sk-jk-timer-val" id="sk-timer">00:00</span>
        </div>
        <div class="sk-jk-timer-track">
          <div class="sk-jk-timer-pulse"></div>
        </div>
      </div>
    </div>
    <div class="sk-jk-footer">
      <span class="sk-jk-footer-text"><?= htmlspecialchars(t('jokescreen_close_hint')) ?></span>
      <div class="sk-jk-footer-badge">
        <div class="sk-jk-footer-badge-dot"></div>
        <span><?= htmlspecialchars(t('jokescreen_footer_active')) ?></span>
      </div>
    </div>
  </div>
</div>

<!-- devtools-detector (embedded, no CDN) -->
<script>
!function(t,n){"object"==typeof exports&&"object"==typeof module?module.exports=n():"function"==typeof define&&define.amd?define([],n):"object"==typeof exports?exports.devtoolsDetector=n():t.devtoolsDetector=n()}("undefined"!=typeof self?self:this,function(){return function(t){var n={};function e(r){if(n[r])return n[r].exports;var o=n[r]={i:r,l:!1,exports:{}};return t[r].call(o.exports,o,o.exports,e),o.l=!0,o.exports}return e.m=t,e.c=n,e.d=function(t,n,r){e.o(t,n)||Object.defineProperty(t,n,{configurable:!1,enumerable:!0,get:r})},e.n=function(t){var n=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(n,"a",n),n},e.o=function(t,n){return Object.prototype.hasOwnProperty.call(t,n)},e.p="",e(e.s=4)}([function(t,n,e){"use strict";e.d(n,"i",function(){return l}),e.d(n,"d",function(){return f}),e.d(n,"e",function(){return h}),e.d(n,"c",function(){return d}),e.d(n,"h",function(){return p}),e.d(n,"f",function(){return b}),e.d(n,"b",function(){return v}),e.d(n,"g",function(){return y}),e.d(n,"a",function(){return w});var r,o,i,u,c,a=e(3),s=Object(a.b)(),l=(null===(r=null===s||void 0===s?void 0:s.navigator)||void 0===r?void 0:r.userAgent)||"unknown",f="InstallTrigger"in((null===s||void 0===s?void 0:s.window)||{})||/firefox/i.test(l),h=/trident/i.test(l)||/msie/i.test(l),d=/edge/i.test(l)||/EdgiOS/i.test(l),p=/webkit/i.test(l),b=/IqiyiApp/.test(l),v=void 0!==(null===(o=null===s||void 0===s?void 0:s.window)||void 0===o?void 0:o.chrome)||/chrome/i.test(l)||/CriOS/i.test(l),y="[object SafariRemoteNotification]"===((null===(u=null===(i=null===s||void 0===s?void 0:s.window)||void 0===i?void 0:i.safari)||void 0===u?void 0:u.pushNotification)||!1).toString()||/safari/i.test(l)&&!v,w="function"==typeof(null===(c=s.document)||void 0===c?void 0:c.createElement)},function(t,n,e){"use strict";e.d(n,"b",function(){return i}),e.d(n,"c",function(){return u}),e.d(n,"a",function(){return c});var r=e(0);function o(t){if(r.a&&console){if(!r.e&&!r.c)return console[t];if("log"===t||"clear"===t)return function(){for(var n=[],e=0;e<arguments.length;e++)n[e]=arguments[e];console[t].apply(console,n)}}return function(){for(var t=[],n=0;n<arguments.length;n++)t[n]=arguments[n]}}var i=o("log"),u=o("table"),c=o("clear")},function(t,n,e){"use strict";n.a=function(t){void 0===t&&(t={});for(var n=t.includes,e=void 0===n?[]:n,r=t.excludes,o=void 0===r?[]:r,i=!1,u=!1,c=0,a=e;c<a.length;c++){var s=a[c];if(!0===s){i=!0;break}}for(var l=0,f=o;l<f.length;l++){var s=f[l];if(!0===s){u=!0;break}}return i&&!u}},function(t,n,e){"use strict";(function(t){n.b=c,n.a=function(){for(var t,n=[],e=0;e<arguments.length;e++)n[e]=arguments[e];var r=c();if(null===r||void 0===r?void 0:r.document)return(t=r.document).createElement.apply(t,n);return{}},n.c=function(){if(r)return r;if(!a)return;var t=new Blob([o.a.workerScript]);try{var n=URL.createObjectURL(t);r=new o.a(new Worker(n)),URL.revokeObjectURL(n)}catch(t){try{r=new o.a(new Worker("data:text/javascript;base64,".concat(btoa(o.a.workerScript))))}catch(t){a=!1}}return r},e.d(n,"d",function(){return s});var r,o=e(10),i=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},u=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}};function c(){return"undefined"!=typeof self?self:"undefined"!=typeof window?window:void 0!==t?t:this}var a=!0;var s=function(){return i(this,void 0,void 0,function(){var t;return u(this,function(n){switch(n.label){case 0:if(t=!1,!navigator.brave)return[3,4];if(!navigator.brave.isBrave)return[3,4];n.label=1;case 1:return n.trys.push([1,3,,4]),[4,Promise.race([navigator.brave.isBrave(),new Promise(function(t){return setTimeout(function(){return t(!1)},1e3)})])];case 2:return t=n.sent(),[3,4];case 3:return n.sent(),[3,4];case 4:return s=function(){return i(this,void 0,void 0,function(){return u(this,function(n){return[2,t]})})},[2,t]}})})}}).call(n,e(9))},function(t,n,e){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.addListener=function(t){h.addListener(t)},n.removeListener=function(t){h.removeListener(t)},n.isLaunch=function(){return h.isLaunch()},n.launch=function(){h.launch()},n.stop=function(){h.stop()},n.setDetectDelay=function(t){h.setDetectDelay(t)};var r=e(8),o=e(12);e.d(n,"DevtoolsDetector",function(){return r.a}),e.d(n,"checkers",function(){return o});var i=e(23);e.d(n,"crashBrowserCurrentTab",function(){return i.b}),e.d(n,"crashBrowser",function(){return i.a});var u=e(2);e.d(n,"match",function(){return u.a});var c=e(3);e.d(n,"getGlobalThis",function(){return c.b}),e.d(n,"createElement",function(){return c.a}),e.d(n,"getWorkerConsole",function(){return c.c}),e.d(n,"isBrave",function(){return c.d});var a=e(24);e.d(n,"versionMap",function(){return a.a});var s=e(0);e.d(n,"userAgent",function(){return s.i}),e.d(n,"isFirefox",function(){return s.d}),e.d(n,"isIE",function(){return s.e}),e.d(n,"isEdge",function(){return s.c}),e.d(n,"isWebkit",function(){return s.h}),e.d(n,"isIqiyiApp",function(){return s.f}),e.d(n,"isChrome",function(){return s.b}),e.d(n,"isSafari",function(){return s.g}),e.d(n,"inBrowser",function(){return s.a});var l=e(1);e.d(n,"log",function(){return l.b}),e.d(n,"table",function(){return l.c}),e.d(n,"clear",function(){return l.a});var f=e(5);e.d(n,"isMac",function(){return f.d}),e.d(n,"isIpad",function(){return f.b}),e.d(n,"isIphone",function(){return f.c}),e.d(n,"isAndroid",function(){return f.a}),e.d(n,"isWindows",function(){return f.e});var h=new r.a({checkers:[o.erudaChecker,o.elementIdChecker,o.devtoolsFormatterChecker,o.performanceChecker,o.debuggerChecker]});n.default=h},function(t,n,e){"use strict";e.d(n,"d",function(){return o}),e.d(n,"b",function(){return i}),e.d(n,"c",function(){return u}),e.d(n,"a",function(){return c}),e.d(n,"e",function(){return a});var r=e(0),o=/macintosh/i.test(r.i),i=/ipad/i.test(r.i)||o&&navigator.maxTouchPoints>1,u=/iphone/i.test(r.i),c=/android/i.test(r.i),a=/windows/i.test(r.i)},function(t,n,e){"use strict";n.a=function(){if("undefined"!=typeof performance)return performance.now();return Date.now()}},function(t,n,e){"use strict";n.a=function(){null===r&&(r=function(){for(var t=function(){for(var t={},n=0;n<500;n++)t["".concat(n)]="".concat(n);return t}(),n=[],e=0;e<50;e++)n.push(t);return n}());return r};var r=null},function(t,n,e){"use strict";e.d(n,"a",function(){return u});var r=e(0),o=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},i=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},u=function(){function t(t){var n=t.checkers;this._listeners=[],this._isOpen=!1,this._detectLoopStopped=!0,this._detectLoopDelay=500,this._checkers=n.slice()}return Object.defineProperty(t.prototype,"isOpen",{get:function(){return this._isOpen},enumerable:!1,configurable:!0}),t.prototype.launch=function(){r.a&&(this._detectLoopDelay<=0&&this.setDetectDelay(500),this._detectLoopStopped&&(this._detectLoopStopped=!1,this._detectLoop()))},t.prototype.stop=function(){this._detectLoopStopped||(this._detectLoopStopped=!0,this._isOpen=!1,clearTimeout(this._timer))},t.prototype.isLaunch=function(){return!this._detectLoopStopped},t.prototype.setDetectDelay=function(t){this._detectLoopDelay=t},t.prototype.addListener=function(t){this._listeners.push(t)},t.prototype.removeListener=function(t){this._listeners=this._listeners.filter(function(n){return n!==t})},t.prototype._broadcast=function(t){for(var n=0,e=this._listeners;n<e.length;n++){var r=e[n];try{r(t.isOpen,t)}catch(t){}}},t.prototype._detectLoop=function(){return o(this,void 0,void 0,function(){var t,n,e,r,o,u=this;return i(this,function(i){switch(i.label){case 0:t=!1,n="",e=0,r=this._checkers,i.label=1;case 1:return e<r.length?[4,(o=r[e]).isEnable()]:[3,6];case 2:return i.sent()?(n=o.name,[4,o.isOpen()]):[3,4];case 3:t=i.sent(),i.label=4;case 4:if(t)return[3,6];i.label=5;case 5:return e++,[3,1];case 6:return t!==this._isOpen&&(this._isOpen=t,this._broadcast({isOpen:t,checkerName:n})),this._detectLoopDelay>0&&!this._detectLoopStopped?this._timer=setTimeout(function(){return u._detectLoop()},this._detectLoopDelay):this.stop(),[2]}})})},t}()},function(t,n){var e;e=function(){return this}();try{e=e||Function("return this")()||(0,eval)("this")}catch(t){"object"==typeof window&&(e=window)}t.exports=e},function(t,n,e){"use strict";e.d(n,"a",function(){return c});var r=e(11),o=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},i=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},u=this&&this.__spreadArray||function(t,n,e){if(e||2===arguments.length)for(var r,o=0,i=n.length;o<i;o++)!r&&o in n||(r||(r=Array.prototype.slice.call(n,0,o)),r[o]=n[o]);return t.concat(r||Array.prototype.slice.call(n))},c=function(){function t(t){var n=this;this.callbacks=new Map,this.worker=t,this.worker.onmessage=function(t){var e=t.data,r=e.id,o=n.callbacks.get(e.id);o&&(o({time:e.time}),n.callbacks.delete(r))},this.log=function(){for(var t=[],e=0;e<arguments.length;e++)t[e]=arguments[e];return n.send.apply(n,u(["log"],t,!1))},this.table=function(){for(var t=[],e=0;e<arguments.length;e++)t[e]=arguments[e];return n.send.apply(n,u(["table"],t,!1))},this.clear=function(){for(var t=[],e=0;e<arguments.length;e++)t[e]=arguments[e];return n.send.apply(n,u(["clear"],t,!1))}}return t.prototype.send=function(t){for(var n=[],e=1;e<arguments.length;e++)n[e-1]=arguments[e];return o(this,void 0,void 0,function(){var e,o=this;return i(this,function(i){return e=Object(r.a)(),[2,new Promise(function(r,i){o.callbacks.set(e,r),o.worker.postMessage({id:e,type:t,payload:n}),setTimeout(function(){i(new Error("timeout")),o.callbacks.delete(e)},2e3)})]})})},t.workerScript="\nonmessage = function(event) {\n  var action = event.data;\n  var startTime = performance.now()\n\n  console[action.type](...action.payload);\n  postMessage({\n    id: action.id,\n    time: performance.now() - startTime\n  })\n}\n",t}()},function(t,n,e){"use strict";n.a=function(){r>Number.MAX_SAFE_INTEGER&&(r=0);return r++};var r=0},function(t,n,e){"use strict";Object.defineProperty(n,"__esModule",{value:!0});var r=e(13);e.d(n,"depRegToStringChecker",function(){return r.a});var o=e(14);e.d(n,"elementIdChecker",function(){return o.a});var i=e(15);e.d(n,"functionToStringChecker",function(){return i.a});var u=e(16);e.d(n,"regToStringChecker",function(){return u.a});var c=e(17);e.d(n,"debuggerChecker",function(){return c.a});var a=e(18);e.d(n,"dateToStringChecker",function(){return a.a});var s=e(19);e.d(n,"performanceChecker",function(){return s.a});var l=e(20);e.d(n,"erudaChecker",function(){return l.a});var f=e(21);e.d(n,"devtoolsFormatterChecker",function(){return f.a});var h=e(22);e.d(n,"workerPerformanceChecker",function(){return h.a})},function(t,n,e){"use strict";e.d(n,"a",function(){return l});var r=e(0),o=e(1),i=e(2),u=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},c=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},a=/ /,s=!1;a.toString=function(){return s=!0,l.name};var l={name:"dep-reg-to-string",isOpen:function(){return u(this,void 0,void 0,function(){return c(this,function(t){return s=!1,Object(o.c)({dep:a}),Object(o.a)(),[2,s]})})},isEnable:function(){return u(this,void 0,void 0,function(){return c(this,function(t){return[2,Object(i.a)({includes:[!0],excludes:[r.d,r.e]})]})})}}},function(t,n,e){"use strict";e.d(n,"a",function(){return f});var r=e(0),o=e(1),i=e(2),u=e(3),c=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},a=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},s=Object(u.a)("div"),l=!1;Object.defineProperty(s,"id",{get:function(){return l=!0,f.name},configurable:!0});var f={name:"element-id",isOpen:function(){return c(this,void 0,void 0,function(){return a(this,function(t){return l=!1,Object(o.b)(s),Object(o.a)(),[2,l]})})},isEnable:function(){return c(this,void 0,void 0,function(){return a(this,function(t){return[2,Object(i.a)({includes:[r.g]})]})})}}},function(t,n,e){"use strict";e.d(n,"a",function(){return f});var r=e(0),o=e(1),i=e(5),u=e(2),c=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},a=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}};function s(){}var l=0;s.toString=function(){return l++,""};var f={name:"function-to-string",isOpen:function(){return c(this,void 0,void 0,function(){return a(this,function(t){return l=0,Object(o.b)(s),Object(o.a)(),[2,2===l]})})},isEnable:function(){return c(this,void 0,void 0,function(){var t;return a(this,function(n){return t=i.b||i.c,[2,Object(u.a)({includes:[!0],excludes:[r.f,r.d,t&&r.b,t&&r.c]})]})})}}},function(t,n,e){"use strict";e.d(n,"a",function(){return l});var r=e(1),o=e(0),i=e(2),u=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},c=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},a=/ /,s=!1;a.toString=function(){return s=!0,l.name};var l={name:"reg-to-string",isOpen:function(){return u(this,void 0,void 0,function(){return c(this,function(t){return s=!1,Object(r.b)(a),Object(r.a)(),[2,s]})})},isEnable:function(){return u(this,void 0,void 0,function(){return c(this,function(t){return[2,Object(i.a)({includes:[!0],excludes:[o.h]})]})})}}},function(t,n,e){"use strict";e.d(n,"a",function(){return u});var r=e(6),o=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},i=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},u={name:"debugger-checker",isOpen:function(){return o(this,void 0,void 0,function(){var t;return i(this,function(n){t=Object(r.a)();try{(function(){}).constructor("debugger")()}catch(t){}return[2,Object(r.a)()-t>100]})})},isEnable:function(){return o(this,void 0,void 0,function(){return i(this,function(t){return[2,!0]})})}}},function(t,n,e){"use strict";e.d(n,"a",function(){return f});var r=e(0),o=e(1),i=e(2),u=e(4),c=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},a=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},s=new Date,l=0;s.toString=function(){return l++,""};var f={name:"date-to-string",isOpen:function(){return c(this,void 0,void 0,function(){return a(this,function(t){return l=0,Object(o.b)(s),Object(o.a)(),[2,2===l]})})},isEnable:function(){return c(this,void 0,void 0,function(){return a(this,function(t){return[2,Object(i.a)({includes:[r.b],excludes:[(u.isIpad||u.isIphone)&&r.b]})]})})}}},function(t,n,e){"use strict";e.d(n,"a",function(){return h});var r=e(1),o=e(0),i=e(7),u=e(2),c=e(3),a=e(6),s=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},l=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},f=0,h={name:"performance",isOpen:function(){return s(this,void 0,void 0,function(){var t,n;return l(this,function(e){switch(e.label){case 0:return t=function(){var t=Object(i.a)(),n=Object(a.a)();return Object(r.c)(t),Object(a.a)()-n}(),n=Math.max(d(),d()),f=Math.max(f,n),Object(r.a)(),0===t?[2,!1]:0!==f?[3,2]:[4,Object(c.d)()];case 1:return e.sent()?[2,!0]:[2,!1];case 2:return[2,t>10*f]}})})},isEnable:function(){return s(this,void 0,void 0,function(){return l(this,function(t){return[2,Object(u.a)({includes:[o.b,o.g,o.d],excludes:[]})]})})}};function d(){var t=Object(i.a)(),n=Object(a.a)();return Object(r.b)(t),Object(a.a)()-n}},function(t,n,e){"use strict";e.d(n,"a",function(){return i});var r=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},o=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},i={name:"eruda",isOpen:function(){var t;return r(this,void 0,void 0,function(){return o(this,function(n){return"undefined"!=typeof eruda?[2,!0===(null===(t=null===eruda||void 0===eruda?void 0:eruda._devTools)||void 0===t?void 0:t._isShow)]:[2,!1]})})},isEnable:function(){return r(this,void 0,void 0,function(){return o(this,function(t){return[2,!0]})})}}},function(t,n,e){"use strict";e.d(n,"a",function(){return a});var r=e(1),o=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},i=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},u=!1,c={header:function(){return u=!0,null}},a={name:"DevtoolsFormatters",isOpen:function(){return o(this,void 0,void 0,function(){return i(this,function(t){return window.devtoolsFormatters?-1===window.devtoolsFormatters.indexOf(c)&&window.devtoolsFormatters.push(c):window.devtoolsFormatters=[c],u=!1,Object(r.b)({}),Object(r.a)(),[2,u]})})},isEnable:function(){return o(this,void 0,void 0,function(){return i(this,function(t){return[2,!0]})})}}},function(t,n,e){"use strict";e.d(n,"a",function(){return l});var r=e(0),o=e(2),i=e(3),u=e(7),c=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},a=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},s=0,l={name:"worker-performance",isOpen:function(){return c(this,void 0,void 0,function(){var t,n,e;return a(this,function(r){switch(r.label){case 0:return null==(t=Object(i.c)())?[2,!1]:[4,function(t){return c(this,void 0,void 0,function(){var n;return a(this,function(e){switch(e.label){case 0:return n=Object(u.a)(),[4,t.table(n)];case 1:return[2,e.sent().time]}})})}(t)];case 1:return n=r.sent(),[4,function(t){return c(this,void 0,void 0,function(){var n;return a(this,function(e){switch(e.label){case 0:return n=Object(u.a)(),[4,t.log(n)];case 1:return[2,e.sent().time]}})})}(t)];case 2:return e=r.sent(),s=Math.max(s,e),[4,t.clear()];case 3:return r.sent(),0===n?[2,!1]:0!==s?[3,5]:[4,Object(i.d)()];case 4:return r.sent()?[2,!0]:[2,!1];case 5:return[2,n>10*s]}})})},isEnable:function(){return c(this,void 0,void 0,function(){return a(this,function(t){return[2,Object(o.a)({includes:[r.b],excludes:[]})]})})}}},function(t,n,e){"use strict";n.b=function(){if(r.a)for(var t=0;t<Number.MAX_VALUE;t++)window["".concat(t)]=new Array(Math.pow(2,32)-1).fill(0)},n.a=function(){if(r.a)for(var t=[];;)t.push(0),location.reload()};var r=e(0)},function(t,n,e){"use strict";e.d(n,"a",function(){return r});for(var r={},o=0,i=(e(0).i||"").match(/\w+\/(\d|\.)+(\s|$)/gi)||[];o<i.length;o++){var u=i[o].split("/"),c=u[0],a=u[1];r[c]=a}}])});
//# sourceMappingURL=devtools-detector.js.map
</script>

<!-- ═══════════════════════════════════════ -->
<!-- PREVENT ACTIONS (improved) -->
<!-- blokada prawego przycisku myszy, kopiowania/zaznaczania, F11/F12/DevTools -->
<!-- ═══════════════════════════════════════ -->
<script>
(function () {
  'use strict';

  var BK=['F12','F11'],BC=['u','s','a','p','i','j','c','x'],BS=['i','j','c','k'];
  document.addEventListener('contextmenu',function(e){e.preventDefault();e.stopPropagation();});
  document.addEventListener('keydown',function(e){
    var k=e.key.toLowerCase();
    if(BK.indexOf(e.key)!==-1){e.preventDefault();e.stopPropagation();return;}
    if(e.ctrlKey&&!e.shiftKey&&BC.indexOf(k)!==-1){e.preventDefault();e.stopPropagation();return;}
    if(e.ctrlKey&&e.shiftKey&&BS.indexOf(k)!==-1){e.preventDefault();e.stopPropagation();return;}
    if(e.altKey&&e.key==='F4'){e.preventDefault();return;}
  });
  ['copy','cut','selectstart','dragstart'].forEach(function(ev){
    document.addEventListener(ev,function(e){e.preventDefault();e.stopPropagation();});
  });
  document.addEventListener('dragover',function(e){e.preventDefault();});
  document.addEventListener('drop',function(e){e.preventDefault();});
  window.onbeforeprint=function(){document.body.style.display='none';};
  window.onafterprint=function(){document.body.style.display='';};
  document.addEventListener('keyup',function(e){
    if(e.key==='PrintScreen'){try{navigator.clipboard.writeText('');}catch(x){}}
  });

  var isJokerOpen=false,timerSecs=0,timerInterval=null,_removedNodes=[];
  var joker,timerEl,refEl;

  function showJoker(){
    if(isJokerOpen)return;
    isJokerOpen=true;
    _removedNodes=[];
    Array.prototype.slice.call(document.body.childNodes).forEach(function(node){
      if(node.id==='sk-joker')return;
      var marker=document.createComment('treść-chroniona');
      document.body.replaceChild(marker,node);
      _removedNodes.push({node:node,marker:marker});
    });
    document.body.style.overflow='hidden';
    document.body.style.background='#0a0c10';
    joker.setAttribute('aria-hidden','false');
    joker.classList.add('sk-visible');
    timerSecs=0;
    timerEl.textContent='00:00';
    timerInterval=setInterval(function(){
      timerSecs++;
      var m=String(Math.floor(timerSecs/60)).padStart(2,'0');
      var s=String(timerSecs%60).padStart(2,'0');
      timerEl.textContent=m+':'+s;
    },1000);
    console.clear();
    console.log('%c⛔ SECRET KEY · PROTECTED','color:#c084fc;font-size:16px;font-weight:bold;font-family:monospace;');
    console.log('%cTen zasób jest chroniony. Nieautoryzowany dostęp jest zabroniony.','color:#94a3b8;font-size:12px;font-family:monospace;');
    try{fetch('/key/decrypt/devtools-log.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({event:'DEVTOOLS OPEN',ref:refEl.textContent,page:window.location.pathname})});}catch(e){}
  }

  function hideJoker(){
    if(!isJokerOpen)return;
    isJokerOpen=false;
    clearInterval(timerInterval);timerInterval=null;
    _removedNodes.forEach(function(item){
      if(item.marker.parentNode===document.body)
        document.body.replaceChild(item.node,item.marker);
    });
    _removedNodes=[];
    document.body.style.overflow='';
    document.body.style.background='';
    joker.classList.remove('sk-visible');
    joker.setAttribute('aria-hidden','true');
    var _dur=timerSecs;timerSecs=0;
    timerEl.textContent='00:00';
    try{fetch('/key/decrypt/devtools-log.php',{method:'POST',headers:{'Content-Type':'application/json'},body:JSON.stringify({event:'DEVTOOLS CLOSE',ref:refEl.textContent,page:window.location.pathname,duration:_dur})});}catch(e){}
  }

  function init(){
    joker  =document.getElementById('sk-joker');
    timerEl=document.getElementById('sk-timer');
    refEl  =document.getElementById('sk-ref');
    refEl.textContent=I18N.refPrefix+String(Math.floor(Math.random()*99999)).padStart(5,'0');

    document.querySelectorAll('img,video,canvas,svg').forEach(function(el){
      el.addEventListener('contextmenu',function(e){e.preventDefault();e.stopPropagation();});
      el.setAttribute('draggable','false');
    });

    var _dtLaunched=false;
    function launchDetector(){
      if(_dtLaunched)return;
      _dtLaunched=true;
      var ua=navigator.userAgent||'';
      if(/HeadlessChrome|Googlebot|AdsBot|PageSpeed|Chrome-Lighthouse/i.test(ua))return;
      devtoolsDetector.addListener(function(dtOpen){
        if(dtOpen){showJoker();}else{hideJoker();}
      });
      devtoolsDetector.launch();
    }

    ['mousedown','mousemove','touchstart','keydown','scroll'].forEach(function(ev){
      window.addEventListener(ev,launchDetector,{once:true,passive:true});
    });
    setTimeout(launchDetector,5000);
  }

  if(document.readyState==='loading'){
    document.addEventListener('DOMContentLoaded',init);
  }else{
    init();
  }
})();
</script>

</body>
</html>
