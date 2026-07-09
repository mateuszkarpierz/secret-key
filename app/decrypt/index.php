<?php
require_once '../auth.php';
requireLogin();

// Fallback — jeśli stara sesja nie ma display_name, odczytaj z mapy
if (empty($_SESSION['display_name']) && !empty($_SESSION['username'])) {
    $_SESSION['display_name'] = $display_names[$_SESSION['username']] ?? $_SESSION['username'];
}
// Fallback dla starych sesji bez login_time
if (empty($_SESSION['login_time'])) {
    $_SESSION['login_time'] = time();
}

// ─── Powiadomienie email — wysyłane raz po zalogowaniu, nie blokuje przekierowania ───
if (!empty($_SESSION['pending_mail'])) {
    $_SESSION['pending_mail'] = false;

    $display = $_SESSION['display_name'] ?? $_SESSION['username'];
    $ip      = $_SERVER['REMOTE_ADDR'] ?? '—';
    $ua      = $_SERVER['HTTP_USER_AGENT'] ?? '—';
    $dt      = date('d.m.Y H:i:s', $_SESSION['login_time']);
    $panel   = 'https://secretkey.moja-domena.pl';

    $subject = '🔐 Logowanie do Secret Key Panel — ' . $display;
    $message = "Nowe logowanie do panelu Secret Key.\n\n"
             . "─────────────────────────────\n"
             . "Użytkownik:   " . $display . " (" . ($_SESSION['username'] ?? '—') . ")\n"
             . "Data i czas:  " . $dt . "\n"
             . "Adres IP:     " . $ip . "\n"
             . "Przeglądarka: " . $ua . "\n"
             . "─────────────────────────────\n\n"
             . "Panel: " . $panel . "\n";

    $messageId = sprintf("<%s.%s@twoja-domena.pl>", date('YmdHis'), uniqid());
    $headers   = "Message-ID: $messageId\r\n";
    $headers  .= "From: Secret Key <no-reply@twoja-domena.pl>\r\n";
    $headers  .= "Reply-To: no-reply@twoja-domena.pl\r\n";
    $headers  .= "Return-Path: no-reply@twoja-domena.pl\r\n";
    $headers  .= "X-Sender: no-reply@twoja-domena.pl\r\n";
    $headers  .= "X-Mailer: secretkey.moja-domena.pl Secret Key Panel\r\n";
    $headers  .= "X-Priority: 3\r\n";
    $headers  .= "MIME-Version: 1.0\r\n";
    $headers  .= "Content-Type: text/plain; charset=UTF-8\r\n";

    @mail('twoj-email@domena.pl', $subject, $message, $headers, '-fno-reply@twoja-domena.pl');
        sk_log("MAIL FAILED: nie udało się wysłać powiadomienia o logowaniu — " . $display . " ('" . ($_SESSION['username'] ?? '—') . "') IP: " . $ip);
    }
}

// Dane sesji do stopki
$session_ip       = $_SERVER['REMOTE_ADDR'] ?? '—';
$session_ua       = $_SERVER['HTTP_USER_AGENT'] ?? '—';
$session_lang     = $_SERVER['HTTP_ACCEPT_LANGUAGE'] ?? '—';
$session_lang     = strtok($session_lang, ',');
$session_login_ts = $_SESSION['login_time'];
$session_login_dt = date('d.m.Y H:i:s', $session_login_ts);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="utf-8" />
    <meta name="author" content="Mateusz Karpierz">
    <meta name="robots" content="noindex,nofollow">
    <meta name="googlebot" content="noindex">
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <title>Panel Secret Key</title>
    <link rel="shortcut icon" type="image/x-icon" href="favicon.ico">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Bungee&family=Space+Mono:wght@400;700&family=Syne:wght@400;600;700;800&family=Barlow+Condensed:wght@700;800;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg: #0a0c10;
            --surface: #111318;
            --surface2: #181c24;
            --border: #222630;
            --border-glow: #2a3044;
            --accent: #c084fc;
            --accent2: #818cf8;
            --accent-dim: rgba(192,132,252,0.12);
            --accent-glow: rgba(192,132,252,0.25);
            --danger: #f87171;
            --danger-dim: rgba(248,113,113,0.1);
            --warning: #fbbf24;
            --warning-dim: rgba(251,191,36,0.1);
            --info: #38bdf8;
            --info-dim: rgba(56,189,248,0.1);
            --success: #4ade80;
            --success-dim: rgba(74,222,128,0.1);
            --text: #e2e8f0;
            --text-muted: #64748b;
            --text-dim: #94a3b8;
            --mono: 'Space Mono', monospace;
            --sans: 'Syne', sans-serif;
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }

        html { font-family: var(--sans); background: var(--bg); color: var(--text); }

        body {
            min-height: 100vh;
            background-color: var(--bg);
            background-image:
                radial-gradient(ellipse 80% 50% at 50% -10%, rgba(192,132,252,0.08) 0%, transparent 60%),
                linear-gradient(180deg, #0a0c10 0%, #0d0f14 100%);
            user-select: none;
            -webkit-user-select: none;
            animation: fadeIn 0.6s ease both;
        }
        @keyframes fadeIn {
            from { opacity: 0; }
            to   { opacity: 1; }
        }

        /* ─── HEADER ─── */
        .header {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex-wrap: wrap;
            padding: 48px 24px 36px;
            gap: 12px;
            position: relative;
        }
        .header::after {
            content: '';
            position: absolute;
            bottom: 0; left: 50%;
            transform: translateX(-50%);
            width: 320px; height: 1px;
            background: linear-gradient(90deg, transparent, var(--border-glow), transparent);
        }
        .header-actions {
            position: absolute;
            top: 20px;
            right: 20px;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .welcome-text {
            font-family: var(--mono);
            font-size: 0.65rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            color: var(--text-muted);
            white-space: nowrap;
        }
        .welcome-text span { color: var(--accent); }
        .logout-btn {
            display: inline-flex;
            align-items: center;
            gap: 7px;
            padding: 8px 14px;
            background: transparent;
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-muted);
            font-family: var(--mono);
            font-size: 0.65rem;
            letter-spacing: 0.12em;
            text-transform: uppercase;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .logout-btn:hover {
            border-color: var(--danger);
            color: var(--danger);
            background: rgba(248,113,113,0.06);
        }
        .logout-btn svg { transition: transform 0.2s; }
        .logout-btn:hover svg { transform: translateX(2px); }

        /* Mobile — powitanie po lewej, wyloguj po prawej, bez dużego marginesu */
        @media (max-width: 768px) {
            .header {
                padding-top: 16px;
                gap: 8px;
            }
            .header-actions {
                position: static;
                width: 100%;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
                order: -1;
                gap: 0;
            }
            .welcome-text {
                font-size: 0.6rem;
            }
        }
        .header-logo {
            width: 100px; height: 100px;
            filter: drop-shadow(0 0 18px rgba(192,132,252,0.4));
            animation: float 4s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        .header h1 {
            font-family: 'Bungee', 'Barlow Condensed', 'Impact', sans-serif;
            font-size: 3.2rem;
            font-weight: 900;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            line-height: 1;
            background: linear-gradient(135deg, #ffffff 40%, var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        .header h2 {
            font-family: var(--mono);
            font-size: 0.7rem;
            color: var(--text-muted);
            letter-spacing: 0.2em;
            text-transform: uppercase;
            font-weight: 400;
        }

        /* ─── SCROLLBAR ─── */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: var(--bg); }
        ::-webkit-scrollbar-thumb {
            background: var(--border-glow);
            border-radius: 99px;
        }
        ::-webkit-scrollbar-thumb:hover { background: var(--accent); }
        * { scrollbar-width: thin; scrollbar-color: var(--border-glow) var(--bg); }

        /* ─── LAYOUT ─── */
        .container {
            max-width: 1100px;
            margin: 0 auto;
            padding: 40px 16px 24px;
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 24px;
        }
        @media (max-width: 768px) {
            .container {
                grid-template-columns: 1fr;
                padding: 20px 16px 20px;
                gap: 16px;
            }
            .header {
                padding: 36px 16px 28px;
            }
            .header h1 {
                font-size: 2.4rem;
            }
            .card {
                padding: 20px 16px;
            }
            .download-grid {
                grid-template-columns: 1fr;
            }
        }
        .card {
            background: var(--surface);
            border: 1px solid var(--border);
            border-radius: 16px;
            padding: 28px;
            position: relative;
            overflow: hidden;
            transition: border-color 0.3s;
        }
        .card::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; height: 1px;
            background: linear-gradient(90deg, transparent, var(--border-glow), transparent);
        }
        .card:hover { border-color: var(--border-glow); }

        /* ─── SECTION TITLES ─── */
        .section-label {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }
        .section-label .icon {
            width: 32px; height: 32px;
            background: var(--accent-dim);
            border: 1px solid rgba(192,132,252,0.2);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px;
            flex-shrink: 0;
        }
        .section-label h3 {
            font-family: var(--sans);
            font-size: 1rem;
            font-weight: 700;
            color: var(--text);
            letter-spacing: 0.03em;
        }

        /* ─── PERSON LIST ─── */
        .person-list { display: flex; flex-direction: column; gap: 8px; }

        .person-row {
            display: flex;
            flex-direction: column;
            gap: 0;
            padding: 12px 14px;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 10px;
            transition: border-color 0.2s;
        }
        .person-row:hover { border-color: var(--border-glow); }

        /* Top line: num badge + locked/placeholder text + button */
        .person-topline {
            display: flex;
            align-items: center;
            gap: 10px;
            width: 100%;
            overflow: hidden;
        }

        .person-num {
            font-family: var(--mono);
            font-size: 0.7rem;
            color: var(--accent);
            background: var(--accent-dim);
            border-radius: 5px;
            padding: 2px 7px;
            flex-shrink: 0;
        }

        .person-info {
            flex: 1;
            font-family: var(--mono);
            font-size: 0.8rem;
            color: var(--text-muted);
            letter-spacing: 0.05em;
            min-width: 0;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }
        .person-info.person-locked {
            animation: pulse-lock 2.5s ease-in-out infinite;
        }
        .lock-icon {
            margin-right: 5px;
            font-size: 0.7rem;
            opacity: 0.6;
        }
        @keyframes pulse-lock {
            0%, 100% { opacity: 0.5; }
            50% { opacity: 0.9; }
        }

        /* Revealed block: appears below the top line */
        .person-revealed {
            display: flex;
            align-items: baseline;
            gap: 6px;
            margin-top: 8px;
            padding-top: 8px;
            border-top: 1px solid var(--border);
            flex-wrap: wrap;
            animation: decryptReveal 0.4s ease forwards;
        }
        .person-name {
            font-family: var(--mono);
            font-size: 0.82rem;
            color: var(--text);
            font-weight: 700;
            white-space: nowrap;
        }
        .person-tel {
            font-family: var(--mono);
            font-size: 0.82rem;
            color: var(--accent);
            white-space: nowrap;
        }
        /* On wider screens: show name — tel on one line */
        @media (min-width: 480px) {
            .person-revealed { flex-wrap: nowrap; }
            .person-name::after { content: ' —'; color: var(--text-muted); margin-right: 4px; }
        }
        @keyframes decryptReveal {
            from { opacity: 0; transform: translateY(-4px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        .reveal-btn {
            background: none;
            border: 1px solid var(--border-glow);
            color: var(--accent);
            font-family: var(--mono);
            font-size: 0.65rem;
            padding: 4px 10px;
            border-radius: 6px;
            cursor: pointer;
            letter-spacing: 0.08em;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .reveal-btn:hover {
            background: var(--accent-dim);
            border-color: var(--accent);
        }
        .reveal-btn:disabled {
            opacity: 0.4;
            cursor: default;
        }

        /* ─── INSTRUCTIONS ─── */
        .instruction-list { display: flex; flex-direction: column; gap: 12px; }
        .instruction-item {
            display: flex;
            gap: 14px;
            padding: 14px;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 10px;
        }
        .instruction-num {
            font-family: var(--mono);
            font-size: 0.7rem;
            color: var(--accent);
            margin-top: 1px;
            flex-shrink: 0;
        }
        .instruction-text {
            font-size: 0.85rem;
            color: var(--text-dim);
            line-height: 1.6;
        }
        .instruction-text strong {
            color: var(--text);
            font-weight: 600;
        }

        /* ─── DECRYPT PANEL ─── */
        .decrypt-label {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 8px;
            font-family: var(--mono);
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 8px;
            letter-spacing: 0.06em;
        }

        .hint-link {
            background: var(--accent-dim);
            border: 1px solid rgba(192,132,252,0.25);
            color: var(--accent);
            font-family: var(--mono);
            font-size: 0.65rem;
            padding: 3px 10px;
            border-radius: 20px;
            cursor: pointer;
            text-decoration: none;
            letter-spacing: 0.06em;
            transition: all 0.2s;
        }
        .hint-link:hover {
            background: rgba(192,132,252,0.2);
        }

        .secret-textarea {
            width: 100%;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            font-family: var(--mono);
            font-size: 0.75rem;
            padding: 14px;
            resize: none;
            min-height: 180px;
            height: 180px;
            outline: none;
            transition: border-color 0.2s, box-shadow 0.2s;
            line-height: 1.7;
            letter-spacing: 0.04em;
        }
        .secret-textarea::placeholder { color: var(--text-muted); }
        .secret-textarea:focus {
            border-color: rgba(192,132,252,0.4);
            box-shadow: 0 0 0 3px rgba(192,132,252,0.07);
        }

        .result-box {
            margin-top: 20px;
        }
        .result-title {
            font-family: var(--sans);
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text);
            margin-bottom: 10px;
        }

        .result-locked {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 14px 16px;
            background: var(--warning-dim);
            border: 1px solid rgba(251,191,36,0.2);
            border-radius: 10px;
            font-family: var(--mono);
            font-size: 0.75rem;
            color: var(--warning);
            letter-spacing: 0.04em;
        }

        .result-value {
            display: none;
            padding: 16px;
            background: var(--success-dim);
            border: 1px solid rgba(74,222,128,0.25);
            border-radius: 10px;
            font-family: var(--mono);
            font-size: 1.1rem;
            color: var(--success);
            letter-spacing: 0.08em;
            text-align: center;
            word-break: break-all;
            animation: fadeGlow 0.5s ease;
        }
        @keyframes fadeGlow {
            from { opacity: 0; box-shadow: 0 0 30px rgba(74,222,128,0.3); }
            to { opacity: 1; box-shadow: none; }
        }

        .result-error {
            display: none;
            padding: 14px 16px;
            background: var(--danger-dim);
            border: 1px solid rgba(248,113,113,0.2);
            border-radius: 10px;
            font-family: var(--mono);
            font-size: 0.8rem;
            color: var(--danger);
        }

        /* ─── DOWNLOAD SECTION ─── */
        .download-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
            margin-top: 16px;
        }
        @media (max-width: 700px) { .download-grid { grid-template-columns: 1fr; } }

        .download-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            padding: 14px;
            background: var(--surface2);
            border: 1px solid var(--border-glow);
            border-radius: 10px;
            color: var(--text);
            font-family: var(--sans);
            font-size: 0.85rem;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: all 0.2s;
        }
        .download-btn:hover {
            background: var(--accent-dim);
            border-color: rgba(192,132,252,0.4);
            color: var(--accent);
        }
        .download-btn svg { opacity: 0.7; flex-shrink: 0; }

        .alert-box {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            padding: 12px 14px;
            border-radius: 10px;
            font-size: 0.82rem;
            line-height: 1.5;
            margin-bottom: 16px;
        }
        .alert-box.info {
            background: var(--info-dim);
            border: 1px solid rgba(56,189,248,0.2);
            color: var(--info);
        }
        .alert-box svg { flex-shrink: 0; margin-top: 1px; }

        /* ─── FOOTER + SESSION INFO ─── */
        .footer {
            text-align: center;
            padding: 24px 20px 32px;
            font-family: var(--mono);
            font-size: 0.6rem;
            color: var(--text-muted);
            letter-spacing: 0.1em;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
        }
        .footer-version {
            opacity: 0.5;
        }
        .session-info {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            align-items: center;
            gap: 6px 0;
            max-width: 900px;
            opacity: 0.45;
            transition: opacity 0.3s;
        }
        .session-info:hover { opacity: 0.8; }
        .si-item {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            padding: 0 8px;
        }
        .si-label {
            color: var(--text-muted);
            font-size: 0.55rem;
            letter-spacing: 0.12em;
            opacity: 0.7;
        }
        .si-val {
            color: var(--text-dim);
            font-size: 0.6rem;
            letter-spacing: 0.06em;
        }
        .si-dot {
            color: var(--border-glow);
            opacity: 0.5;
        }
        @media (max-width: 768px) {
            .session-info { gap: 4px 0; }
            .si-item { padding: 0 5px; }
            .si-dot { display: none; }
            .si-item::after {
                content: '·';
                margin-left: 10px;
                color: var(--border-glow);
                opacity: 0.5;
            }
            .si-item:last-child::after { display: none; }
        }

        /* ─── MODAL ─── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.75);
            backdrop-filter: blur(6px);
            z-index: 999999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        .modal-overlay.open { display: flex; }

        .modal-box {
            background: var(--surface);
            border: 1px solid var(--border-glow);
            border-radius: 20px;
            max-width: 600px;
            width: 100%;
            padding: 32px;
            position: relative;
            animation: modalIn 0.25s ease;
            box-shadow: 0 25px 80px rgba(0,0,0,0.6);
        }
        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.96) translateY(10px); }
            to { opacity: 1; transform: scale(1) translateY(0); }
        }

        .modal-close {
            position: absolute;
            top: 16px; right: 16px;
            width: 32px; height: 32px;
            background: var(--surface2);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text-muted);
            font-size: 18px;
            cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            transition: all 0.2s;
            line-height: 1;
        }
        .modal-close:hover { border-color: var(--accent); color: var(--accent); }

        .modal-title {
            font-family: var(--sans);
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 20px;
            padding-right: 40px;
        }

        .modal-card-img {
            width: 100%;
            border-radius: 12px;
            border: 1px solid var(--border);
            margin-bottom: 16px;
        }

        .modal-note {
            padding: 12px 14px;
            background: var(--danger-dim);
            border: 1px solid rgba(248,113,113,0.2);
            border-radius: 10px;
            font-size: 0.82rem;
            color: var(--danger);
            line-height: 1.5;
        }

        /* ─── LICZNIK KLUCZY ─── */
        .key-counter {
            display: flex;
            align-items: center;
            gap: 6px;
            margin-top: 8px;
            margin-bottom: 20px;
            font-family: var(--mono);
            font-size: 0.65rem;
            letter-spacing: 0.12em;
            color: var(--text-muted);
        }
        .key-counter-val {
            color: var(--text-dim);
            font-weight: 700;
            transition: color 0.3s, text-shadow 0.3s;
            min-width: 12px;
        }
        .key-counter-val.ready {
            color: var(--success);
            text-shadow: 0 0 8px rgba(74,222,128,0.6);
        }
        .key-counter-sep { color: var(--text-muted); }
        .key-counter-max { color: var(--text-muted); }
        .key-counter-dots {
            margin-left: 6px;
            display: flex;
            gap: 5px;
        }
        .kdot {
            width: 7px; height: 7px;
            border-radius: 50%;
            border: 1px solid var(--border-glow);
            background: transparent;
            transition: background 0.25s, border-color 0.25s, box-shadow 0.25s;
        }
        .kdot.active {
            background: var(--accent);
            border-color: var(--accent);
            box-shadow: 0 0 6px rgba(192,132,252,0.6);
        }
        .kdot.active.done {
            background: var(--success);
            border-color: var(--success);
            box-shadow: 0 0 6px rgba(74,222,128,0.6);
        }

        /* hidden inputs for secrets.js */
        .hidden { display: none !important; }

        /* ════════════════════════════════════════
           CUSTOM CURSOR — outline arrow + morph ring
        ════════════════════════════════════════ */
        * { cursor: none !important; }

        /* Strzałka SVG */
        #cur-arrow {
            position: fixed;
            width: 28px; height: 28px;
            pointer-events: none;
            z-index: 9999999;
            top: 0; left: 0;
            opacity: 0;
            filter: drop-shadow(0 0 5px rgba(192,132,252,0.8))
                    drop-shadow(0 0 12px rgba(192,132,252,0.35));
            transition: filter 0.2s ease, opacity 0.2s ease;
            will-change: left, top;
        }
        #cur-arrow.is-morphed {
            filter: drop-shadow(0 0 7px rgba(192,132,252,1))
                    drop-shadow(0 0 18px rgba(192,132,252,0.5));
        }
        #cur-arrow.clicking {
            filter: drop-shadow(0 0 12px rgba(255,255,255,0.9))
                    drop-shadow(0 0 28px rgba(192,132,252,1));
        }

        /* Ring — domyślnie mały okrąg przy kursorze,
           przy morphingu rozciąga się na kształt elementu */
        #cur-ring {
            position: fixed;
            top: 0; left: 0;
            width: 36px; height: 36px;
            border: 1.5px solid rgba(192,132,252,0.45);
            border-radius: 50%;
            pointer-events: none;
            z-index: 9999998;
            transition:
                left   0.32s cubic-bezier(0.23, 1, 0.32, 1),
                top    0.32s cubic-bezier(0.23, 1, 0.32, 1),
                width  0.32s cubic-bezier(0.23, 1, 0.32, 1),
                height 0.32s cubic-bezier(0.23, 1, 0.32, 1),
                border-radius 0.32s cubic-bezier(0.23, 1, 0.32, 1),
                border-color  0.2s ease,
                opacity       0.2s ease;
            opacity: 0;
            will-change: left, top, width, height, border-radius;
        }
        #cur-ring.is-morphed {
            border-color: rgba(192,132,252,0.85);
            border-width: 1px;
            opacity: 1;
            box-shadow: 0 0 12px rgba(192,132,252,0.15),
                        0 0 0 1px rgba(192,132,252,0.06);
        }

        /* Ripple po kliknięciu */
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
            0%   { width: 10px;  height: 10px;  opacity: 0.85;
                   transform: translate(-50%,-50%); }
            100% { width: 110px; height: 110px; opacity: 0;
                   transform: translate(-50%,-50%); }
        }

        /* Textarea — natywny kursor tekstowy */
        textarea, textarea * { cursor: text !important; caret-color: var(--accent); }

        /* ─── cursor: none tylko dla myszy ─── */
        @media (pointer: fine) { * { cursor: none !important; } }

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

    <!-- Hidden inputs required by secrets.js -->
    <input class="required hidden" type="number" value="3" min="2" max="255">
    <input class="total hidden" type="number" value="5" min="2" max="255">
    <textarea class="secret hidden"></textarea>

    <!-- MODAL -->
    <div class="modal-overlay" id="modal-overlay">
        <div class="modal-box">
            <button class="modal-close" id="modal-close">&times;</button>
            <p class="modal-title">Co to jest Secret key?</p>
            <img src="card-secret-key.webp" class="modal-card-img" alt="Secret Key Card">
            <div class="modal-note">
                Secret key to specjalny ciąg znaków kryptograficznych, który znajduje się na odwrocie karty w miejscu zaznaczonym czerwoną obramówką. Jest on również umieszczony w kodzie QR.
            </div>
        </div>
    </div>

    <!-- HEADER -->
    <header class="header">
        <div class="header-actions">
            <span class="welcome-text">
                Witaj, <?= htmlspecialchars($_SESSION['display_name'] ?? 'Gość') ?>
            </span>
            <a href="../logout.php" class="logout-btn">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                    <polyline points="16 17 21 12 16 7"/>
                    <line x1="21" y1="12" x2="9" y2="12"/>
                </svg>
                Wyloguj
            </a>
        </div>
        <img src="key.svg" class="header-logo" alt="Key Logo">
        <h1>Secret Key</h1>
        <h2>by Mateusz Karpierz</h2>
    </header>

    <!-- MAIN GRID -->
    <main class="container">

      <!-- LEFT: Instructions only -->
      <div class="card">
          <div class="section-label">
              <span class="icon">📋</span>
              <h3>Instrukcja dostępu do bazy haseł</h3>
          </div>
          <div class="instruction-list">
              <div class="instruction-item">
                  <span class="instruction-num">01</span>
                  <p class="instruction-text"><strong>Zbierzcie się razem.</strong> Skontaktuj się z osobami z listy po prawej stronie (lub poniżej na telefonie). Każda z nich posiada swoją część specjalnego kodu (Secret Key). Potrzebujecie minimum <strong>3 osoby z 5</strong> — dopiero wtedy możliwe jest odblokowanie hasła.</p>
              </div>
              <div class="instruction-item">
                  <span class="instruction-num">02</span>
                  <p class="instruction-text"><strong>Wejdźcie na tę stronę razem.</strong> Każda osoba powinna mieć przy sobie swoją kartę Secret Key — znajdziecie na niej długi ciąg znaków (np. <em>8015c7c4f263a74d…</em>). Kliknijcie „Co to jest Secret Key?" jeśli nie wiecie, gdzie go szukać.</p>
              </div>
              <div class="instruction-item">
                  <span class="instruction-num">03</span>
                  <p class="instruction-text"><strong>Wprowadźcie kody.</strong> W polu tekstowym po prawej stronie (lub poniżej na telefonie) wpisujcie kolejno kody z kart — każdy kod w osobnej linii, dokładnie tak jak jest napisany na karcie, bez żadnych spacji ani dodatkowych znaków.</p>
              </div>
              <div class="instruction-item">
                  <span class="instruction-num">04</span>
                  <p class="instruction-text"><strong>Hasło pojawi się automatycznie.</strong> Gdy wpiszecie co najmniej 3 kody, hasło do bazy haseł wyświetli się poniżej pola tekstowego. To właśnie hasło posłuży do otwarcia programu KeePassXC.</p>
              </div>
              <div class="instruction-item">
                  <span class="instruction-num">05</span>
                  <p class="instruction-text"><strong>Pobierzcie program i bazę haseł.</strong> Na dole strony znajdziecie dwa przyciski: pobierzcie program KeePassXC oraz plik z bazą haseł. Zainstalujcie program, otwórzcie nim pobrany plik i wpiszcie uzyskane hasło. Uwaga: oprócz hasła potrzebny jest też <strong>klucz sprzętowy</strong> (fizyczne urządzenie USB).</p>
              </div>
              <div class="instruction-item">
                  <span class="instruction-num">06</span>
                  <p class="instruction-text"><strong>Co dalej?</strong> Po uzyskaniu dostępu do bazy haseł znajdziecie tam dane logowania do wszystkich moich kont internetowych. Możecie je wtedy zamknąć lub przejąć zgodnie z wolą rodziny.</p>
              </div>
          </div>
      </div>

        <!-- RIGHT: Persons + Decrypt stacked -->
        <div style="display:flex; flex-direction:column; gap:24px;">

            <!-- Person list -->
            <div class="card">
                <div class="section-label">
                    <span class="icon">👤</span>
                    <h3>Lista posiadaczy Secret key</h3>
                </div>
                <div class="person-list" id="person-list">
                    <!-- Generated by JS -->
                </div>
            </div>

            <!-- Decrypt -->
            <div class="card" style="flex:1; display:flex; flex-direction:column;">
                <div class="section-label">
                    <span class="icon">🔓</span>
                    <h3>Odszyfrowywanie</h3>
                </div>

                <div class="decrypt-label">
                    <span>Wprowadź Secret key</span>
                    <a class="hint-link" id="hint-btn">Co to jest Secret key?</a>
                </div>

                <textarea
                    class="parts secret-textarea"
                    id="parts-input"
                    rows="8"
                    placeholder="Wpisz swój kod z karty tutaj — jeden kod, jedna linia…"
                    style="flex:1; min-height:120px;"
                ></textarea>

                <div class="key-counter">
                    <span class="key-counter-label">KLUCZE:</span>
                    <span class="key-counter-val" id="key-count">0</span>
                    <span class="key-counter-sep">/</span>
                    <span class="key-counter-max">3 wymagane</span>
                    <span class="key-counter-dots" id="key-dots">
                        <span class="kdot" id="kdot-1"></span>
                        <span class="kdot" id="kdot-2"></span>
                        <span class="kdot" id="kdot-3"></span>
                    </span>
                </div>
                    <p class="result-title">Hasło do bazy KeePassXC</p>
                    <div class="result-locked" id="result-locked">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                        Czekam na kody… wpisz co najmniej 3, a hasło pojawi się w tym miejscu.
                    </div>
                    <div class="result-value" id="result-value"></div>
                    <div class="result-error" id="result-error"></div>
                </div>
            </div>

        </div>

        <!-- BOTTOM FULL WIDTH: Downloads -->
        <div class="card" style="grid-column: 1 / -1;">
            <div class="section-label">
                <span class="icon">💾</span>
                <h3>Pliki i program do odzyskania dostępu</h3>
            </div>
            <p style="font-size:0.85rem; color:var(--text-dim); margin-bottom:16px;">
                Pobierz program i plik z hasłami. Będą Ci potrzebne w następnym kroku.
            </p>
            <div class="alert-box info">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Ważne: oprócz hasła będzie potrzebne fizyczne urządzenie USB — bez niego baza haseł pozostanie zablokowana.
            </div>
            <div class="download-grid">
                <a href="download.php?file=baza-hasel" class="download-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Pobierz przykładowy plik (demo)
                </a>
                <a href="https://keepassxc.org/download/" target="_blank" rel="noopener" class="download-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Pobierz KeePassXC
                </a>
            </div>
        </div>

    </main>

    <footer class="footer">
        <div class="footer-version">WERSJA SYSTEMU: v1.2.0</div>
        <div class="session-info">
            <span class="si-item">
                <span class="si-label">IP</span>
                <span class="si-val"><?= htmlspecialchars($session_ip) ?></span>
            </span>
            <span class="si-dot">·</span>
            <span class="si-item">
                <span class="si-label">PRZEGLĄDARKA</span>
                <span class="si-val" id="si-browser">—</span>
            </span>
            <span class="si-dot">·</span>
            <span class="si-item">
                <span class="si-label">SYSTEM</span>
                <span class="si-val" id="si-os">—</span>
            </span>
            <span class="si-dot">·</span>
            <span class="si-item">
                <span class="si-label">URZĄDZENIE</span>
                <span class="si-val" id="si-device">—</span>
            </span>
            <span class="si-dot">·</span>
            <span class="si-item">
                <span class="si-label">EKRAN</span>
                <span class="si-val" id="si-screen">—</span>
            </span>
            <span class="si-dot">·</span>
            <span class="si-item">
                <span class="si-label">JĘZYK</span>
                <span class="si-val"><?= htmlspecialchars($session_lang) ?></span>
            </span>
            <span class="si-dot">·</span>
            <span class="si-item">
                <span class="si-label">STREFA CZASOWA</span>
                <span class="si-val" id="si-tz">—</span>
            </span>
            <span class="si-dot">·</span>
            <span class="si-item">
                <span class="si-label">ZALOGOWANO</span>
                <span class="si-val"><?= $session_login_dt ?></span>
            </span>
            <span class="si-dot">·</span>
            <span class="si-item">
                <span class="si-label">CZAS SESJI</span>
                <span class="si-val" id="si-duration">—</span>
            </span>
        </div>
    </footer>

    <!-- ═══════════════════════════════════════ -->
    <!-- SECRETS.JS LIBRARY (Shamir's Secret Sharing) -->
    <!-- ═══════════════════════════════════════ -->
    <script>
(function (root, factory) {
    "use strict";
    if (typeof define === "function" && define.amd) {
        define([], function () { return (root.secrets = factory()); });
    } else if (typeof exports === "object") {
        module.exports = factory(require("crypto"));
    } else {
        root.secrets = factory(root.crypto);
    }
}(this, function (crypto) {
    "use strict";
    var defaults, config, preGenPadding, runCSPRNGTest, sjclParanoia, CSPRNGTypes;
    function reset() {
        defaults = { bits: 8, radix: 16, minBits: 3, maxBits: 20, bytesPerChar: 2, maxBytesPerChar: 6, primitivePolynomials: [null, null, 1, 3, 3, 5, 3, 3, 29, 17, 9, 5, 83, 27, 43, 3, 45, 9, 39, 39, 9, 5, 3, 33, 27, 9, 71, 39, 9, 5, 83] };
        config = {};
        preGenPadding = new Array(1024).join("0");
        runCSPRNGTest = true;
        sjclParanoia = 10;
        CSPRNGTypes = ["nodeCryptoRandomBytes", "browserCryptoGetRandomValues", "browserSJCLRandom", "testRandom"];
    }
    function isSetRNG() { return !!(config && config.rng && typeof config.rng === "function"); }
    function padLeft(str, multipleOfBits) {
        var missing;
        if (multipleOfBits === 0 || multipleOfBits === 1) return str;
        if (multipleOfBits && multipleOfBits > 1024) throw new Error("Wewnętrzny błąd: dopełnienie musi być wielokrotnością maksymalnie 1024 bitów.");
        multipleOfBits = multipleOfBits || config.bits;
        if (str) missing = str.length % multipleOfBits;
        if (missing) return (preGenPadding + str).slice(-(multipleOfBits - missing + str.length));
        return str;
    }
    function hex2bin(str) {
        var bin = "", num, i;
        for (i = str.length - 1; i >= 0; i--) { num = parseInt(str[i], 16); if (isNaN(num)) throw new Error("Nieprawidłowy znak szesnastkowy w kluczu."); bin = padLeft(num.toString(2), 4) + bin; }
        return bin;
    }
    function bin2hex(str) {
        var hex = "", num, i;
        str = padLeft(str, 4);
        for (i = str.length; i >= 4; i -= 4) { num = parseInt(str.slice(i - 4, i), 2); if (isNaN(num)) throw new Error("Nieprawidłowy znak binarny w kluczu."); hex = num.toString(16) + hex; }
        return hex;
    }
    function hasCryptoGetRandomValues() { return !!(crypto && typeof crypto === "object" && (typeof crypto.getRandomValues === "function" || typeof crypto.getRandomValues === "object") && (typeof Uint32Array === "function" || typeof Uint32Array === "object")); }
    function hasCryptoRandomBytes() { return !!(typeof crypto === "object" && typeof crypto.randomBytes === "function"); }
    function hasSJCL() { return !!(typeof sjcl === "object" && typeof sjcl.random === "object"); }
    function getRNG(type) {
        function construct(bits, arr, radix, size) { var i=0,len,str="",parsedInt; if(arr)len=arr.length-1; while(i<len||(str.length<bits)){parsedInt=Math.abs(parseInt(arr[i],radix));str=str+padLeft(parsedInt.toString(2),size);i++;} str=str.substr(-bits); if((str.match(/0/g)||[]).length===str.length)return null; return str; }
        function nodeCryptoRandomBytes(bits) { var buf,bytes,radix=16,size=4,str=null; bytes=Math.ceil(bits/8); while(str===null){buf=crypto.randomBytes(bytes);str=construct(bits,buf.toString("hex"),radix,size);} return str; }
        function browserCryptoGetRandomValues(bits) { var elems,radix=10,size=32,str=null; elems=Math.ceil(bits/32); while(str===null){str=construct(bits,crypto.getRandomValues(new Uint32Array(elems)),radix,size);} return str; }
        function browserSJCLRandom(bits) { var elems,radix=10,size=32,str=null; elems=Math.ceil(bits/32); if(sjcl.random.isReady(sjclParanoia)){str=construct(bits,sjcl.random.randomWords(elems,sjclParanoia),radix,size);}else{throw new Error("Generator losowości nie jest jeszcze gotowy. Poczekaj chwilę i spróbuj ponownie.");} return str; }
        function testRandom(bits) { var arr,elems=Math.ceil(bits/32),int=123456789,radix=10,size=32,str=null; arr=new Uint32Array(elems); for(var i=0;i<arr.length;i++)arr[i]=int; while(str===null)str=construct(bits,arr,radix,size); return str; }
        if(type&&type==="testRandom"){config.typeCSPRNG=type;return testRandom;}
        else if(type&&type==="nodeCryptoRandomBytes"){config.typeCSPRNG=type;return nodeCryptoRandomBytes;}
        else if(type&&type==="browserCryptoGetRandomValues"){config.typeCSPRNG=type;return browserCryptoGetRandomValues;}
        else if(type&&type==="browserSJCLRandom"){runCSPRNGTest=false;config.typeCSPRNG=type;return browserSJCLRandom;}
        else if(hasCryptoRandomBytes()){config.typeCSPRNG="nodeCryptoRandomBytes";return nodeCryptoRandomBytes;}
        else if(hasCryptoGetRandomValues()){config.typeCSPRNG="browserCryptoGetRandomValues";return browserCryptoGetRandomValues;}
        else if(hasSJCL()){runCSPRNGTest=false;config.typeCSPRNG="browserSJCLRandom";return browserSJCLRandom;}
    }
    function splitNumStringToIntArray(str, padLength) {
        var parts=[],i;
        if(padLength)str=padLeft(str,padLength);
        for(i=str.length;i>config.bits;i-=config.bits)parts.push(parseInt(str.slice(i-config.bits,i),2));
        parts.push(parseInt(str.slice(0,i),2));
        return parts;
    }
    function horner(x,coeffs){var logx=config.logs[x],fx=0,i;for(i=coeffs.length-1;i>=0;i--){if(fx!==0)fx=config.exps[(logx+config.logs[fx])%config.maxShares]^coeffs[i];else fx=coeffs[i];}return fx;}
    function lagrange(at,x,y){var sum=0,len,product,i,j;for(i=0,len=x.length;i<len;i++){if(y[i]){product=config.logs[y[i]];for(j=0;j<len;j++){if(i!==j){if(at===x[j]){product=-1;break;}product=(product+config.logs[at^x[j]]-config.logs[x[i]^x[j]]+config.maxShares)%config.maxShares;}}sum=product===-1?sum:sum^config.exps[product];}}return sum;}
    function getShares(secret,numShares,threshold){var shares=[],coeffs=[secret],i,len;for(i=1;i<threshold;i++)coeffs[i]=parseInt(config.rng(config.bits),2);for(i=1,len=numShares+1;i<len;i++)shares[i-1]={x:i,y:horner(i,coeffs)};return shares;}
    function constructPublicShareString(bits,id,data){var bitsBase36,idHex,idMax,idPaddingLen,newShareString;id=parseInt(id,config.radix);bits=parseInt(bits,10)||config.bits;bitsBase36=bits.toString(36).toUpperCase();idMax=Math.pow(2,bits)-1;idPaddingLen=idMax.toString(config.radix).length;idHex=padLeft(id.toString(config.radix),idPaddingLen);if(typeof id!=="number"||id%1!==0||id<1||id>idMax)throw new Error("Nieprawidłowy klucz: identyfikator musi być liczbą całkowitą od 1 do "+idMax+".");newShareString=bitsBase36+idHex+data;return newShareString;}
    var secrets = {
        init: function(bits,rngType){var logs=[],exps=[],x=1,primitive,i;reset();if(bits&&(typeof bits!=="number"||bits%1!==0||bits<defaults.minBits||bits>defaults.maxBits))throw new Error("Wewnętrzny błąd: liczba bitów musi być liczbą całkowitą między "+defaults.minBits+" a "+defaults.maxBits+".");if(rngType&&CSPRNGTypes.indexOf(rngType)===-1)throw new Error("Wewnętrzny błąd: nieprawidłowy typ generatora losowości '"+rngType+"'");config.radix=defaults.radix;config.bits=bits||defaults.bits;config.size=Math.pow(2,config.bits);config.maxShares=config.size-1;primitive=defaults.primitivePolynomials[config.bits];for(i=0;i<config.size;i++){exps[i]=x;logs[x]=i;x=x<<1;if(x>=config.size){x=x^primitive;x=x&config.maxShares;}}config.logs=logs;config.exps=exps;if(rngType)this.setRNG(rngType);if(!isSetRNG())this.setRNG();if(!isSetRNG()||!config.bits||!config.size||!config.maxShares||!config.logs||!config.exps||config.logs.length!==config.size||config.exps.length!==config.size)throw new Error("Inicjalizacja biblioteki kryptograficznej nie powiodła się.");},
        combine: function(shares,at){var i,j,len,len2,result="",setBits,share,splitShare,x=[],y=[];at=at||0;for(i=0,len=shares.length;i<len;i++){share=this.extractShareComponents(shares[i]);if(setBits===undefined)setBits=share.bits;else if(share.bits!==setBits)throw new Error("Niezgodne klucze: klucze pochodzą z różnych zestawów (różne ustawienia bitów).");if(config.bits!==setBits)this.init(setBits);if(x.indexOf(share.id)===-1){x.push(share.id);splitShare=splitNumStringToIntArray(hex2bin(share.data));for(j=0,len2=splitShare.length;j<len2;j++){y[j]=y[j]||[];y[j][x.length-1]=splitShare[j];}}}for(i=0,len=y.length;i<len;i++)result=padLeft(lagrange(at,x,y[i]).toString(2))+result;return bin2hex(at>=1?result:result.slice(result.indexOf("1")+1));},
        extractShareComponents: function(share){var bits,id,idLen,max,obj={},regexStr,shareComponents;bits=parseInt(share.substr(0,1),36);if(bits&&(typeof bits!=="number"||bits%1!==0||bits<defaults.minBits||bits>defaults.maxBits))throw new Error("Nieprawidłowy klucz: liczba bitów musi być między "+defaults.minBits+" a "+defaults.maxBits+".");max=Math.pow(2,bits)-1;idLen=(Math.pow(2,bits)-1).toString(config.radix).length;regexStr="^([a-kA-K3-9]{1})([a-fA-F0-9]{"+idLen+"})([a-fA-F0-9]+)$";shareComponents=new RegExp(regexStr).exec(share);if(shareComponents)id=parseInt(shareComponents[2],config.radix);if(typeof id!=="number"||id%1!==0||id<1||id>max)throw new Error("Nieprawidłowy klucz: identyfikator musi być liczbą całkowitą od 1 do "+config.maxShares+".");if(shareComponents&&shareComponents[3]){obj.bits=bits;obj.id=id;obj.data=shareComponents[3];return obj;}throw new Error("Podany klucz jest nieprawidłowy lub uszkodzony: "+share);},
        setRNG: function(rng){var errPrefix="Generator losowości jest nieprawidłowy ",errSuffix=" Skontaktuj się z administratorem strony.";if(rng&&typeof rng==="string"&&CSPRNGTypes.indexOf(rng)===-1)throw new Error("Nieprawidłowy typ generatora losowości: '"+rng+"'");if(!rng)rng=getRNG();if(rng&&typeof rng==="string")rng=getRNG(rng);if(runCSPRNGTest){if(rng&&typeof rng!=="function")throw new Error(errPrefix+"(nie jest funkcją)."+errSuffix);if(rng&&typeof rng(config.bits)!=="string")throw new Error(errPrefix+"(wynik nie jest ciągiem znaków)."+errSuffix);if(rng&&!parseInt(rng(config.bits),2))throw new Error(errPrefix+"(wynik binarny nie może być przekonwertowany na liczbę)."+errSuffix);if(rng&&rng(config.bits).length>config.bits)throw new Error(errPrefix+"(długość wyniku przekracza config.bits)."+errSuffix);if(rng&&rng(config.bits).length<config.bits)throw new Error(errPrefix+"(długość wyniku jest mniejsza niż config.bits)."+errSuffix);}config.rng=rng;return true;},
        str2hex: function(str,bytesPerChar){var hexChars,max,out="",neededBytes,num,i,len;if(typeof str!=="string")throw new Error("Dane wejściowe muszą być ciągiem znaków.");if(!bytesPerChar)bytesPerChar=defaults.bytesPerChar;if(typeof bytesPerChar!=="number"||bytesPerChar<1||bytesPerChar>defaults.maxBytesPerChar||bytesPerChar%1!==0)throw new Error("Wewnętrzny błąd: liczba bajtów na znak musi być liczbą całkowitą od 1 do "+defaults.maxBytesPerChar+".");hexChars=2*bytesPerChar;max=Math.pow(16,hexChars)-1;for(i=0,len=str.length;i<len;i++){num=str[i].charCodeAt();if(isNaN(num))throw new Error("Niedozwolony znak w danych wejściowych: "+str[i]);if(num>max){neededBytes=Math.ceil(Math.log(num+1)/Math.log(256));throw new Error("Niedozwolony kod znaku ("+num+"). Maksymalna wartość to "+max+". Sprawdź poprawność danych wejściowych.");}out=padLeft(num.toString(16),hexChars)+out;}return out;},
        hex2str: function(str,bytesPerChar){var hexChars,out="",i,len;if(typeof str!=="string")throw new Error("Dane wejściowe muszą być ciągiem szesnastkowym.");bytesPerChar=bytesPerChar||defaults.bytesPerChar;if(typeof bytesPerChar!=="number"||bytesPerChar%1!==0||bytesPerChar<1||bytesPerChar>defaults.maxBytesPerChar)throw new Error("Wewnętrzny błąd: liczba bajtów na znak musi być liczbą całkowitą od 1 do "+defaults.maxBytesPerChar+".");hexChars=2*bytesPerChar;str=padLeft(str,hexChars);for(i=0,len=str.length;i<len;i+=hexChars)out=String.fromCharCode(parseInt(str.slice(i,i+hexChars),16))+out;return out;},
        share: function(secret,numShares,threshold,padLength){var neededBits,subShares,x=new Array(numShares),y=new Array(numShares),i,j,len;padLength=padLength||128;if(typeof secret!=="string")throw new Error("Dane do podzielenia muszą być ciągiem znaków.");if(typeof numShares!=="number"||numShares%1!==0||numShares<2)throw new Error("Liczba części musi być liczbą całkowitą od 2 do "+config.maxShares+".");if(numShares>config.maxShares){neededBits=Math.ceil(Math.log(numShares+1)/Math.LN2);throw new Error("Liczba części musi być liczbą całkowitą od 2 do "+config.maxShares+". To create "+numShares+" shares, use at least "+neededBits+" bits.");}if(typeof threshold!=="number"||threshold%1!==0||threshold<2)throw new Error("Próg wymaganych części musi być liczbą całkowitą od 2 do "+config.maxShares+".");if(threshold>config.maxShares){neededBits=Math.ceil(Math.log(threshold+1)/Math.LN2);throw new Error("Próg wymaganych części musi być liczbą całkowitą od 2 do "+config.maxShares+".  To use a threshold of "+threshold+", use at least "+neededBits+" bits.");}if(threshold>numShares)throw new Error("Próg ("+threshold+") nie może być większy niż łączna liczba części ("+numShares+").");if(typeof padLength!=="number"||padLength%1!==0||padLength<0||padLength>1024)throw new Error("Wewnętrzny błąd: długość dopełnienia musi być liczbą od 0 do 1024.");secret="1"+hex2bin(secret);secret=splitNumStringToIntArray(secret,padLength);for(i=0,len=secret.length;i<len;i++){subShares=getShares(secret[i],numShares,threshold);for(j=0;j<numShares;j++){x[j]=x[j]||subShares[j].x.toString(config.radix);y[j]=padLeft(subShares[j].y.toString(2))+(y[j]||"");}}for(i=0;i<numShares;i++)x[i]=constructPublicShareString(config.bits,x[i],bin2hex(y[i]));return x;}
    };
    secrets.init();
    return secrets;
}));
    </script>

    <!-- ═══════════════════════════════════════ -->
    <!-- PREVENT ACTIONS (improved) -->
    <!-- ═══════════════════════════════════════ -->

    <!-- ═══════════════════════════════════════ -->
    <!-- PAGE LOGIC -->
    <!-- ═══════════════════════════════════════ -->
    <script>
    var CSRF_TOKEN = '<?= generateCsrfToken() ?>';
    // ─── Person data (fill with real data) ───
    var persons = [
        { label: "1.", name: "Jan Kowalski", tel: "123-456-789" },
        { label: "2.", name: "Anna Kowalska", tel: "123-456-789" },
        { label: "3.", name: "Piotr Kowalski", tel: "123-456-789" },
        { label: "4.", name: "Maria Kowalska", tel: "123-456-789" },
        { label: "5.", name: "Andrzej Kowalski", tel: "123-456-789" }
    ];

    // ─── Build person list with decrypt animation ───
    var CHARS = '0123456789abcdefghijklmnopqrstuvwxyz@#$%&?!';

    function scrambleAnimate(el, finalText, duration, onDone) {
        var steps = 18;
        var stepDuration = duration / steps;
        var current = 0;
        el.style.color = 'var(--accent)';
        var interval = setInterval(function() {
            current++;
            if (current >= steps) {
                clearInterval(interval);
                el.textContent = finalText;
                el.style.color = '';
                if (onDone) onDone();
                return;
            }
            var progress = current / steps;
            var revealed = Math.floor(progress * finalText.length);
            var scrambled = finalText.slice(0, revealed);
            for (var i = revealed; i < finalText.length; i++) {
                if (finalText[i] === ' ' || finalText[i] === ':' || finalText[i] === '-') {
                    scrambled += finalText[i];
                } else {
                    scrambled += CHARS[Math.floor(Math.random() * CHARS.length)];
                }
            }
            el.textContent = scrambled;
        }, stepDuration);
    }

    var list = document.getElementById('person-list');
    persons.forEach(function(p) {
        var row = document.createElement('div');
        row.className = 'person-row';

        // Number badge
        var num = document.createElement('span');
        num.className = 'person-num';
        num.textContent = p.label;

        // Locked placeholder
        var info = document.createElement('span');
        info.className = 'person-info person-locked';
        info.innerHTML = '<span class="lock-icon">&#128274;</span> zaszyfrowane';

        // Reveal button
        var btn = document.createElement('button');
        btn.className = 'reveal-btn';
        btn.textContent = 'odszyfruj';

        // Top line: num + info (locked) + btn
        var topLine = document.createElement('div');
        topLine.className = 'person-topline';
        topLine.appendChild(num);
        topLine.appendChild(info);
        topLine.appendChild(btn);
        row.appendChild(topLine);
        list.appendChild(row);

        btn.addEventListener('click', (function(rowEl, infoEl, btnEl, personData) {
            return function() {
                btnEl.disabled = true;
                btnEl.textContent = '···';
                infoEl.classList.remove('person-locked');

                // Build revealed structure: name on one line, tel on second
                var revealedWrap = document.createElement('div');
                revealedWrap.className = 'person-revealed';

                var nameEl = document.createElement('span');
                nameEl.className = 'person-name';
                nameEl.textContent = '';

                var telEl = document.createElement('span');
                telEl.className = 'person-tel';
                telEl.textContent = '';

                revealedWrap.appendChild(nameEl);
                revealedWrap.appendChild(telEl);
                rowEl.appendChild(revealedWrap);

                // Animate name first, then tel
                scrambleAnimate(nameEl, personData.name, 500, function() {
                    scrambleAnimate(telEl, 'Telefon: ' + personData.tel, 400, null);
                });

                // Update original info to hide it cleanly
                infoEl.style.display = 'none';
                setTimeout(function() { btnEl.textContent = '✓'; }, 950);
            };
        })(row, info, btn, p));
    });

    // ─── Modal ───
    var overlay = document.getElementById('modal-overlay');

    function openModal() {
        overlay.classList.add('open');
        document.body.style.overflow = 'hidden';
        document.dispatchEvent(new CustomEvent('cursorReset'));
    }
    function closeModal() {
        overlay.classList.remove('open');
        document.body.style.overflow = '';
        document.dispatchEvent(new CustomEvent('cursorReset'));
    }

    document.getElementById('hint-btn').addEventListener('click', function(e) {
        e.preventDefault();
        openModal();
    });
    document.getElementById('modal-close').addEventListener('click', closeModal);
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) closeModal();
    });
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });

    // ─── Decrypt logic ───
    var partsInput = document.getElementById('parts-input');
    var resultLocked = document.getElementById('result-locked');
    var resultValue = document.getElementById('result-value');
    var resultError = document.getElementById('result-error');

    function showLocked() {
        resultLocked.style.display = 'flex';
        resultValue.style.display = 'none';
        resultError.style.display = 'none';
    }
    function showValue(val) {
        resultLocked.style.display = 'none';
        resultValue.style.display = 'block';
        resultError.style.display = 'none';
        resultValue.textContent = val;
    }
    function showError(msg) {
        resultLocked.style.display = 'none';
        resultValue.style.display = 'none';
        resultError.style.display = 'block';
        resultError.textContent = msg;
    }

    partsInput.addEventListener('input', function() {
        var raw = partsInput.value.trim();

        var keyCountEl = document.getElementById('key-count');
        var dots = [
            document.getElementById('kdot-1'),
            document.getElementById('kdot-2'),
            document.getElementById('kdot-3')
        ];
        var lines = raw ? raw.split(/\n/).map(function(l){ return l.trim(); }).filter(function(l){ return l.length > 0; }) : [];
        var n = lines.length;
        keyCountEl.textContent = n;

        // Koloruj liczbę
        if (n >= 3) {
            keyCountEl.classList.add('ready');
        } else {
            keyCountEl.classList.remove('ready');
        }
        // Kropki — max 3
        dots.forEach(function(dot, i) {
            if (i < n) {
                dot.classList.add('active');
                if (n >= 3) dot.classList.add('done');
                else        dot.classList.remove('done');
            } else {
                dot.classList.remove('active', 'done');
            }
        });

        if (!raw) { showLocked(); return; }

        var parts = raw.split(/\s+/).filter(function(p) { return p.length > 0; });
        if (parts.length < 3) { showLocked(); return; }

        try {
            var combinedHex = secrets.combine(parts);
            var combined = secrets.hex2str(combinedHex);
            if (combined && combined.trim().length > 0) {
                showValue(combined.trim());
                clearTimeout(window._decryptLogTimer);
                window._decryptLogTimer = setTimeout(function() {
                    fetch('log.php', { method: 'POST', headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ event: 'DECRYPT SUCCESS', keys: parts.length, csrf_token: CSRF_TOKEN }) });
                }, 1500);
            } else {
                showError('Nie można odszyfrować hasła. Sprawdź, czy klucze są wpisane poprawnie (jeden klucz w linii, bez spacji).');
                clearTimeout(window._decryptLogTimer);
                window._decryptLogTimer = setTimeout(function() {
                    fetch('log.php', { method: 'POST', headers: {'Content-Type': 'application/json'},
                        body: JSON.stringify({ event: 'DECRYPT FAILED', keys: parts.length, csrf_token: CSRF_TOKEN }) });
                }, 1500);
            }
        } catch(e) {
            showError('Błąd: ' + e.message);
            clearTimeout(window._decryptLogTimer);
            window._decryptLogTimer = setTimeout(function() {
                fetch('log.php', { method: 'POST', headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({ event: 'DECRYPT ERROR', keys: parts.length, csrf_token: CSRF_TOKEN }) });
            }, 1500);
        }
    });
    </script>

    <!-- ══ CUSTOM CURSOR ══ -->
    <!-- Strzałka SVG — outline z glowem, styl inspirowany UI Cursor Kit -->
    <svg id="cur-arrow" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
        <path
            d="M4 2 L20 11 L13 13 L10 20 Z"
            fill="none"
            stroke="#c084fc"
            stroke-width="1.4"
            stroke-linejoin="round"
            stroke-linecap="round"
        />
    </svg>
    <div id="cur-ring"></div>

    <script>
    // Kursor działa tylko na urządzeniach z myszą — na mobile/touch pomijamy całość
    if (window.matchMedia('(pointer: fine)').matches) {
    (function() {
        var arrow = document.getElementById('cur-arrow');
        var ring  = document.getElementById('cur-ring');

        var mx = -200, my = -200;
        var rx = -200, ry = -200;
        var isMorphed   = false;
        var morphTarget = null;

        var MORPH_ALL = '.instruction-item, .person-row, .download-btn, .logout-btn, .hint-link, .modal-close';

        // ─── Ruch myszy ───
        var cursorVisible = false;
        document.addEventListener('mousemove', function(e) {
            mx = e.clientX;
            my = e.clientY;
            arrow.style.left = mx + 'px';
            arrow.style.top  = my + 'px';
            // Pokaż kursor dopiero po pierwszym ruchu myszy
            if (!cursorVisible) {
                cursorVisible = true;
                arrow.style.opacity = '1';
                ring.style.opacity  = '0.75';
            }
            if (isMorphed && morphTarget) applyMorph(morphTarget);
        });

        // ─── Lerp loop — tylko gdy nie morphuje ───
        (function lerpLoop() {
            if (!isMorphed) {
                rx += (mx - rx) * 0.13;
                ry += (my - ry) * 0.13;
                ring.style.left = (rx - 18) + 'px';
                ring.style.top  = (ry - 18) + 'px';
            }
            requestAnimationFrame(lerpLoop);
        })();

        // ─── Nałóż ring dokładnie na element ───
        function applyMorph(el) {
            var r  = el.getBoundingClientRect();
            var br = getComputedStyle(el).borderRadius || '10px';
            ring.style.left         = r.left   + 'px';
            ring.style.top          = r.top    + 'px';
            ring.style.width        = r.width  + 'px';
            ring.style.height       = r.height + 'px';
            ring.style.borderRadius = br;
        }

        // ─── Reset do małego kółka przy kursorze ───
        function resetToCircle() {
            // Wyłącz transition → teleportuj do myszy → włącz z powrotem
            ring.style.transition = 'none';
            ring.style.width        = '36px';
            ring.style.height       = '36px';
            ring.style.borderRadius = '50%';
            ring.style.left = (mx - 18) + 'px';
            ring.style.top  = (my - 18) + 'px';
            rx = mx; ry = my;
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    ring.style.transition = '';
                });
            });
        }

        // ─── Scroll — ukryj ring i zresetuj morph ───
        var scrollTimer;
        document.addEventListener('scroll', function() {
            ring.style.opacity = '0';
            clearTimeout(scrollTimer);
            scrollTimer = setTimeout(function() {
                isMorphed   = false;
                morphTarget = null;
                arrow.classList.remove('is-morphed');
                ring.classList.remove('is-morphed');
                resetToCircle();
                if (cursorVisible) ring.style.opacity = '0.75';

                // Sprawdź czy kursor jest nad elementem morph
                var elUnder = document.elementFromPoint(mx, my);
                if (elUnder) {
                    var target = elUnder.closest(MORPH_ALL);
                    if (target) {
                        isMorphed   = true;
                        morphTarget = target;
                        arrow.classList.add('is-morphed');
                        ring.classList.add('is-morphed');
                        applyMorph(target);
                        ring.style.opacity = '1';
                    }
                }
            }, 150);
        }, { passive: true });

        // ─── Reset morpha po zamknięciu modala ───
        document.addEventListener('cursorReset', function() {
            isMorphed   = false;
            morphTarget = null;
            arrow.classList.remove('is-morphed');
            ring.classList.remove('is-morphed');
            resetToCircle();
        });

        // ─── Wejście na element ───
        document.addEventListener('mouseover', function(e) {
            var target = e.target.closest(MORPH_ALL);
            if (!target) return;
            // Jeśli przeskakujemy z jednego elementu na drugi sąsiedni:
            // isMorphed jest już true → po prostu zaktualizuj cel bez resetu
            isMorphed   = true;
            morphTarget = target;
            arrow.classList.add('is-morphed');
            ring.classList.add('is-morphed');
            applyMorph(target);
        });

        // ─── Wyjście z elementu ───
        document.addEventListener('mouseout', function(e) {
            var target = e.target.closest(MORPH_ALL);
            if (!target) return;
            // Czy wchodzimy na inny morph-element? Jeśli tak, mouseover go obsłuży
            var related = e.relatedTarget;
            if (related && related.closest(MORPH_ALL)) return;
            // Wychodzimy w "puste miejsce" — wróć do kółka
            isMorphed   = false;
            morphTarget = null;
            arrow.classList.remove('is-morphed');
            ring.classList.remove('is-morphed');
            resetToCircle();
        });

        // ─── MutationObserver — odśwież morph po reveal person-row ───
        new MutationObserver(function() {
            setTimeout(function() {
                if (isMorphed && morphTarget) applyMorph(morphTarget);
            }, 20);
        }).observe(document.body, { childList: true, subtree: true });

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

        // ─── Textarea / input ───
        document.querySelectorAll('textarea, input').forEach(function(el) {
            el.addEventListener('mouseenter', function() {
                arrow.style.opacity = '0';
                ring.style.opacity  = '0';
            });
            el.addEventListener('mouseleave', function() {
                arrow.style.opacity = '1';
                ring.style.opacity  = '0.75';
            });
        });

        // ─── Czerwony ring na przycisku wyloguj ───
        var logoutBtn = document.querySelector('.logout-btn');
        if (logoutBtn) {
            logoutBtn.addEventListener('mouseenter', function() {
                ring.style.borderColor = 'rgba(248,113,113,0.85)';
                ring.style.boxShadow   = '0 0 12px rgba(248,113,113,0.15), 0 0 0 1px rgba(248,113,113,0.06)';
            });
            logoutBtn.addEventListener('mouseleave', function() {
                ring.style.borderColor = '';
                ring.style.boxShadow   = '';
            });
        }

    })();
    } // end pointer: fine
    </script>

    <script>
    // ─── Session info — dane przeglądarki i licznik ───
    (function() {
        var ua = navigator.userAgent;

        // Przeglądarka
        function getBrowser() {
            if (/Edg\//.test(ua))     return 'Edge ' + (ua.match(/Edg\/([\d.]+)/) || [])[1];
            if (/OPR\//.test(ua))     return 'Opera ' + (ua.match(/OPR\/([\d.]+)/) || [])[1];
            if (/Brave/.test(ua))     return 'Brave';
            if (/Chrome\//.test(ua))  return 'Chrome ' + (ua.match(/Chrome\/([\d.]+)/) || [])[1];
            if (/Firefox\//.test(ua)) return 'Firefox ' + (ua.match(/Firefox\/([\d.]+)/) || [])[1];
            if (/Safari\//.test(ua))  return 'Safari ' + (ua.match(/Version\/([\d.]+)/) || [])[1];
            return 'Nieznana';
        }

        // System operacyjny
        function getOS() {
            if (/Windows NT 10/.test(ua)) return 'Windows 10/11';
            if (/Windows NT 6.3/.test(ua)) return 'Windows 8.1';
            if (/Windows NT 6.1/.test(ua)) return 'Windows 7';
            if (/Mac OS X ([\d_]+)/.test(ua)) return 'macOS ' + ua.match(/Mac OS X ([\d_]+)/)[1].replace(/_/g,'.');
            if (/Android ([\d.]+)/.test(ua)) return 'Android ' + ua.match(/Android ([\d.]+)/)[1];
            if (/iPhone OS ([\d_]+)/.test(ua)) return 'iOS ' + ua.match(/iPhone OS ([\d_]+)/)[1].replace(/_/g,'.');
            if (/Linux/.test(ua)) return 'Linux';
            return 'Nieznany';
        }

        // Urządzenie
        function getDevice() {
            if (/iPhone/.test(ua)) return 'iPhone';
            if (/iPad/.test(ua)) return 'iPad';
            if (/Android/.test(ua) && /Mobile/.test(ua)) return 'Telefon';
            if (/Android/.test(ua)) return 'Tablet';
            return 'Komputer';
        }

        function setText(id, value) {
            var el = document.getElementById(id);
            if (el) el.textContent = value;
        }

        setText('si-browser', getBrowser());
        setText('si-os', getOS());
        setText('si-device', getDevice());
        setText('si-screen', screen.width + '×' + screen.height);
        setText('si-tz', Intl.DateTimeFormat().resolvedOptions().timeZone);

        // Live licznik czasu sesji
        var loginTs = <?= $session_login_ts ?> * 1000;
        function updateDuration() {
            var el = document.getElementById('si-duration');
            if (!el) return;
            var diff = Math.floor((Date.now() - loginTs) / 1000);
            var h = Math.floor(diff / 3600);
            var m = Math.floor((diff % 3600) / 60);
            var s = diff % 60;
            var str = '';
            if (h > 0) str += h + 'h ';
            str += (m < 10 ? '0' : '') + m + 'm ' + (s < 10 ? '0' : '') + s + 's';
            el.textContent = str;
        }
        updateDuration();
        setInterval(updateDuration, 1000);
    })();
    </script>

    <script>
    // ─── Auto-logout po nieaktywności (30 min) ───
    (function() {
        var TIMEOUT_MS = <?= SESSION_TIMEOUT ?> * 1000;
        var timer;

        function resetTimer() {
            clearTimeout(timer);
            timer = setTimeout(function() {
                window.location.href = '../logout.php?timeout';
            }, TIMEOUT_MS);
        }

        ['mousemove', 'mousedown', 'keydown', 'scroll', 'touchstart'].forEach(function(evt) {
            document.addEventListener(evt, resetTimer, { passive: true });
        });

        resetTimer();
    })();
    </script>

<!-- ══ JOKER SCREEN ══ -->
<div id="sk-joker" aria-hidden="true">
  <div class="sk-jk-inner">
    <div class="sk-jk-header">
      <div class="sk-jk-header-left">
        <div class="sk-jk-status-dot"></div>
        <span class="sk-jk-header-title">Naruszenie bezpieczeństwa</span>
      </div>
      <span class="sk-jk-header-id" id="sk-ref">REF #00000</span>
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
      <div class="sk-jk-title">Dostęp wstrzymany</div>
      <div class="sk-jk-desc">Wykryto próbę inspekcji chronionego zasobu.<br>Sesja została wstrzymana do czasu zamknięcia narzędzi deweloperskich.</div>
      <div class="sk-jk-divider">
        <div class="sk-jk-divider-line"></div>
        <span class="sk-jk-divider-text">Zarejestrowane naruszenia</span>
        <div class="sk-jk-divider-line"></div>
      </div>
      <div class="sk-jk-violations">
        <div class="sk-jk-viol">
          <div class="sk-jk-viol-icon"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#c084fc" stroke-width="2.5" stroke-linecap="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></div>
          <span class="sk-jk-viol-text">Inspekcja kodu źródłowego</span>
          <span class="sk-jk-viol-tag red">WYKRYTO</span>
        </div>
        <div class="sk-jk-viol">
          <div class="sk-jk-viol-icon"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#c084fc" stroke-width="2.5" stroke-linecap="round"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg></div>
          <span class="sk-jk-viol-text">Panel deweloperski aktywny</span>
          <span class="sk-jk-viol-tag red">WYKRYTO</span>
        </div>
        <div class="sk-jk-viol">
          <div class="sk-jk-viol-icon"><svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="#c084fc" stroke-width="2.5" stroke-linecap="round"><circle cx="12" cy="12" r="10"/><path d="M12 8v4M12 16h.01"/></svg></div>
          <span class="sk-jk-viol-text">Debugowanie sesji użytkownika</span>
          <span class="sk-jk-viol-tag red">WYKRYTO</span>
        </div>
      </div>
      <div class="sk-jk-timer-wrap">
        <div class="sk-jk-timer-label">
          <span class="sk-jk-timer-lbl">Czas naruszenia</span>
          <span class="sk-jk-timer-val" id="sk-timer">00:00</span>
        </div>
        <div class="sk-jk-timer-track">
          <div class="sk-jk-timer-pulse"></div>
        </div>
      </div>
    </div>
    <div class="sk-jk-footer">
      <span class="sk-jk-footer-text">zamknij devtools → strona wróci</span>
      <div class="sk-jk-footer-badge">
        <div class="sk-jk-footer-badge-dot"></div>
        <span>Ochrona aktywna</span>
      </div>
    </div>
  </div>
</div>

<!-- devtools-detector (embedded, no CDN) -->
<script>
!function(t,n){"object"==typeof exports&&"object"==typeof module?module.exports=n():"function"==typeof define&&define.amd?define([],n):"object"==typeof exports?exports.devtoolsDetector=n():t.devtoolsDetector=n()}("undefined"!=typeof self?self:this,function(){return function(t){var n={};function e(r){if(n[r])return n[r].exports;var o=n[r]={i:r,l:!1,exports:{}};return t[r].call(o.exports,o,o.exports,e),o.l=!0,o.exports}return e.m=t,e.c=n,e.d=function(t,n,r){e.o(t,n)||Object.defineProperty(t,n,{configurable:!1,enumerable:!0,get:r})},e.n=function(t){var n=t&&t.__esModule?function(){return t.default}:function(){return t};return e.d(n,"a",n),n},e.o=function(t,n){return Object.prototype.hasOwnProperty.call(t,n)},e.p="",e(e.s=4)}([function(t,n,e){"use strict";e.d(n,"i",function(){return l}),e.d(n,"d",function(){return f}),e.d(n,"e",function(){return h}),e.d(n,"c",function(){return d}),e.d(n,"h",function(){return p}),e.d(n,"f",function(){return b}),e.d(n,"b",function(){return v}),e.d(n,"g",function(){return y}),e.d(n,"a",function(){return w});var r,o,i,u,c,a=e(3),s=Object(a.b)(),l=(null===(r=null===s||void 0===s?void 0:s.navigator)||void 0===r?void 0:r.userAgent)||"unknown",f="InstallTrigger"in((null===s||void 0===s?void 0:s.window)||{})||/firefox/i.test(l),h=/trident/i.test(l)||/msie/i.test(l),d=/edge/i.test(l)||/EdgiOS/i.test(l),p=/webkit/i.test(l),b=/IqiyiApp/.test(l),v=void 0!==(null===(o=null===s||void 0===s?void 0:s.window)||void 0===o?void 0:o.chrome)||/chrome/i.test(l)||/CriOS/i.test(l),y="[object SafariRemoteNotification]"===((null===(u=null===(i=null===s||void 0===s?void 0:s.window)||void 0===i?void 0:i.safari)||void 0===u?void 0:u.pushNotification)||!1).toString()||/safari/i.test(l)&&!v,w="function"==typeof(null===(c=s.document)||void 0===c?void 0:c.createElement)},function(t,n,e){"use strict";e.d(n,"b",function(){return i}),e.d(n,"c",function(){return u}),e.d(n,"a",function(){return c});var r=e(0);function o(t){if(r.a&&console){if(!r.e&&!r.c)return console[t];if("log"===t||"clear"===t)return function(){for(var n=[],e=0;e<arguments.length;e++)n[e]=arguments[e];console[t].apply(console,n)}}return function(){for(var t=[],n=0;n<arguments.length;n++)t[n]=arguments[n]}}var i=o("log"),u=o("table"),c=o("clear")},function(t,n,e){"use strict";n.a=function(t){void 0===t&&(t={});for(var n=t.includes,e=void 0===n?[]:n,r=t.excludes,o=void 0===r?[]:r,i=!1,u=!1,c=0,a=e;c<a.length;c++){var s=a[c];if(!0===s){i=!0;break}}for(var l=0,f=o;l<f.length;l++){var s=f[l];if(!0===s){u=!0;break}}return i&&!u}},function(t,n,e){"use strict";(function(t){n.b=c,n.a=function(){for(var t,n=[],e=0;e<arguments.length;e++)n[e]=arguments[e];var r=c();if(null===r||void 0===r?void 0:r.document)return(t=r.document).createElement.apply(t,n);return{}},n.c=function(){if(r)return r;if(!a)return;var t=new Blob([o.a.workerScript]);try{var n=URL.createObjectURL(t);r=new o.a(new Worker(n)),URL.revokeObjectURL(n)}catch(t){try{r=new o.a(new Worker("data:text/javascript;base64,".concat(btoa(o.a.workerScript))))}catch(t){a=!1}}return r},e.d(n,"d",function(){return s});var r,o=e(10),i=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},u=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}};function c(){return"undefined"!=typeof self?self:"undefined"!=typeof window?window:void 0!==t?t:this}var a=!0;var s=function(){return i(this,void 0,void 0,function(){var t;return u(this,function(n){switch(n.label){case 0:if(t=!1,!navigator.brave)return[3,4];if(!navigator.brave.isBrave)return[3,4];n.label=1;case 1:return n.trys.push([1,3,,4]),[4,Promise.race([navigator.brave.isBrave(),new Promise(function(t){return setTimeout(function(){return t(!1)},1e3)})])];case 2:return t=n.sent(),[3,4];case 3:return n.sent(),[3,4];case 4:return s=function(){return i(this,void 0,void 0,function(){return u(this,function(n){return[2,t]})})},[2,t]}})})}}).call(n,e(9))},function(t,n,e){"use strict";Object.defineProperty(n,"__esModule",{value:!0}),n.addListener=function(t){h.addListener(t)},n.removeListener=function(t){h.removeListener(t)},n.isLaunch=function(){return h.isLaunch()},n.launch=function(){h.launch()},n.stop=function(){h.stop()},n.setDetectDelay=function(t){h.setDetectDelay(t)};var r=e(8),o=e(12);e.d(n,"DevtoolsDetector",function(){return r.a}),e.d(n,"checkers",function(){return o});var i=e(23);e.d(n,"crashBrowserCurrentTab",function(){return i.b}),e.d(n,"crashBrowser",function(){return i.a});var u=e(2);e.d(n,"match",function(){return u.a});var c=e(3);e.d(n,"getGlobalThis",function(){return c.b}),e.d(n,"createElement",function(){return c.a}),e.d(n,"getWorkerConsole",function(){return c.c}),e.d(n,"isBrave",function(){return c.d});var a=e(24);e.d(n,"versionMap",function(){return a.a});var s=e(0);e.d(n,"userAgent",function(){return s.i}),e.d(n,"isFirefox",function(){return s.d}),e.d(n,"isIE",function(){return s.e}),e.d(n,"isEdge",function(){return s.c}),e.d(n,"isWebkit",function(){return s.h}),e.d(n,"isIqiyiApp",function(){return s.f}),e.d(n,"isChrome",function(){return s.b}),e.d(n,"isSafari",function(){return s.g}),e.d(n,"inBrowser",function(){return s.a});var l=e(1);e.d(n,"log",function(){return l.b}),e.d(n,"table",function(){return l.c}),e.d(n,"clear",function(){return l.a});var f=e(5);e.d(n,"isMac",function(){return f.d}),e.d(n,"isIpad",function(){return f.b}),e.d(n,"isIphone",function(){return f.c}),e.d(n,"isAndroid",function(){return f.a}),e.d(n,"isWindows",function(){return f.e});var h=new r.a({checkers:[o.erudaChecker,o.elementIdChecker,o.devtoolsFormatterChecker,o.performanceChecker,o.debuggerChecker]});n.default=h},function(t,n,e){"use strict";e.d(n,"d",function(){return o}),e.d(n,"b",function(){return i}),e.d(n,"c",function(){return u}),e.d(n,"a",function(){return c}),e.d(n,"e",function(){return a});var r=e(0),o=/macintosh/i.test(r.i),i=/ipad/i.test(r.i)||o&&navigator.maxTouchPoints>1,u=/iphone/i.test(r.i),c=/android/i.test(r.i),a=/windows/i.test(r.i)},function(t,n,e){"use strict";n.a=function(){if("undefined"!=typeof performance)return performance.now();return Date.now()}},function(t,n,e){"use strict";n.a=function(){null===r&&(r=function(){for(var t=function(){for(var t={},n=0;n<500;n++)t["".concat(n)]="".concat(n);return t}(),n=[],e=0;e<50;e++)n.push(t);return n}());return r};var r=null},function(t,n,e){"use strict";e.d(n,"a",function(){return u});var r=e(0),o=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},i=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},u=function(){function t(t){var n=t.checkers;this._listeners=[],this._isOpen=!1,this._detectLoopStopped=!0,this._detectLoopDelay=500,this._checkers=n.slice()}return Object.defineProperty(t.prototype,"isOpen",{get:function(){return this._isOpen},enumerable:!1,configurable:!0}),t.prototype.launch=function(){r.a&&(this._detectLoopDelay<=0&&this.setDetectDelay(500),this._detectLoopStopped&&(this._detectLoopStopped=!1,this._detectLoop()))},t.prototype.stop=function(){this._detectLoopStopped||(this._detectLoopStopped=!0,this._isOpen=!1,clearTimeout(this._timer))},t.prototype.isLaunch=function(){return!this._detectLoopStopped},t.prototype.setDetectDelay=function(t){this._detectLoopDelay=t},t.prototype.addListener=function(t){this._listeners.push(t)},t.prototype.removeListener=function(t){this._listeners=this._listeners.filter(function(n){return n!==t})},t.prototype._broadcast=function(t){for(var n=0,e=this._listeners;n<e.length;n++){var r=e[n];try{r(t.isOpen,t)}catch(t){}}},t.prototype._detectLoop=function(){return o(this,void 0,void 0,function(){var t,n,e,r,o,u=this;return i(this,function(i){switch(i.label){case 0:t=!1,n="",e=0,r=this._checkers,i.label=1;case 1:return e<r.length?[4,(o=r[e]).isEnable()]:[3,6];case 2:return i.sent()?(n=o.name,[4,o.isOpen()]):[3,4];case 3:t=i.sent(),i.label=4;case 4:if(t)return[3,6];i.label=5;case 5:return e++,[3,1];case 6:return t!==this._isOpen&&(this._isOpen=t,this._broadcast({isOpen:t,checkerName:n})),this._detectLoopDelay>0&&!this._detectLoopStopped?this._timer=setTimeout(function(){return u._detectLoop()},this._detectLoopDelay):this.stop(),[2]}})})},t}()},function(t,n){var e;e=function(){return this}();try{e=e||Function("return this")()||(0,eval)("this")}catch(t){"object"==typeof window&&(e=window)}t.exports=e},function(t,n,e){"use strict";e.d(n,"a",function(){return c});var r=e(11),o=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},i=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},u=this&&this.__spreadArray||function(t,n,e){if(e||2===arguments.length)for(var r,o=0,i=n.length;o<i;o++)!r&&o in n||(r||(r=Array.prototype.slice.call(n,0,o)),r[o]=n[o]);return t.concat(r||Array.prototype.slice.call(n))},c=function(){function t(t){var n=this;this.callbacks=new Map,this.worker=t,this.worker.onmessage=function(t){var e=t.data,r=e.id,o=n.callbacks.get(e.id);o&&(o({time:e.time}),n.callbacks.delete(r))},this.log=function(){for(var t=[],e=0;e<arguments.length;e++)t[e]=arguments[e];return n.send.apply(n,u(["log"],t,!1))},this.table=function(){for(var t=[],e=0;e<arguments.length;e++)t[e]=arguments[e];return n.send.apply(n,u(["table"],t,!1))},this.clear=function(){for(var t=[],e=0;e<arguments.length;e++)t[e]=arguments[e];return n.send.apply(n,u(["clear"],t,!1))}}return t.prototype.send=function(t){for(var n=[],e=1;e<arguments.length;e++)n[e-1]=arguments[e];return o(this,void 0,void 0,function(){var e,o=this;return i(this,function(i){return e=Object(r.a)(),[2,new Promise(function(r,i){o.callbacks.set(e,r),o.worker.postMessage({id:e,type:t,payload:n}),setTimeout(function(){i(new Error("timeout")),o.callbacks.delete(e)},2e3)})]})})},t.workerScript="\nonmessage = function(event) {\n  var action = event.data;\n  var startTime = performance.now()\n\n  console[action.type](...action.payload);\n  postMessage({\n    id: action.id,\n    time: performance.now() - startTime\n  })\n}\n",t}()},function(t,n,e){"use strict";n.a=function(){r>Number.MAX_SAFE_INTEGER&&(r=0);return r++};var r=0},function(t,n,e){"use strict";Object.defineProperty(n,"__esModule",{value:!0});var r=e(13);e.d(n,"depRegToStringChecker",function(){return r.a});var o=e(14);e.d(n,"elementIdChecker",function(){return o.a});var i=e(15);e.d(n,"functionToStringChecker",function(){return i.a});var u=e(16);e.d(n,"regToStringChecker",function(){return u.a});var c=e(17);e.d(n,"debuggerChecker",function(){return c.a});var a=e(18);e.d(n,"dateToStringChecker",function(){return a.a});var s=e(19);e.d(n,"performanceChecker",function(){return s.a});var l=e(20);e.d(n,"erudaChecker",function(){return l.a});var f=e(21);e.d(n,"devtoolsFormatterChecker",function(){return f.a});var h=e(22);e.d(n,"workerPerformanceChecker",function(){return h.a})},function(t,n,e){"use strict";e.d(n,"a",function(){return l});var r=e(0),o=e(1),i=e(2),u=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},c=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},a=/ /,s=!1;a.toString=function(){return s=!0,l.name};var l={name:"dep-reg-to-string",isOpen:function(){return u(this,void 0,void 0,function(){return c(this,function(t){return s=!1,Object(o.c)({dep:a}),Object(o.a)(),[2,s]})})},isEnable:function(){return u(this,void 0,void 0,function(){return c(this,function(t){return[2,Object(i.a)({includes:[!0],excludes:[r.d,r.e]})]})})}}},function(t,n,e){"use strict";e.d(n,"a",function(){return f});var r=e(0),o=e(1),i=e(2),u=e(3),c=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},a=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},s=Object(u.a)("div"),l=!1;Object.defineProperty(s,"id",{get:function(){return l=!0,f.name},configurable:!0});var f={name:"element-id",isOpen:function(){return c(this,void 0,void 0,function(){return a(this,function(t){return l=!1,Object(o.b)(s),Object(o.a)(),[2,l]})})},isEnable:function(){return c(this,void 0,void 0,function(){return a(this,function(t){return[2,Object(i.a)({includes:[r.g]})]})})}}},function(t,n,e){"use strict";e.d(n,"a",function(){return f});var r=e(0),o=e(1),i=e(5),u=e(2),c=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},a=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}};function s(){}var l=0;s.toString=function(){return l++,""};var f={name:"function-to-string",isOpen:function(){return c(this,void 0,void 0,function(){return a(this,function(t){return l=0,Object(o.b)(s),Object(o.a)(),[2,2===l]})})},isEnable:function(){return c(this,void 0,void 0,function(){var t;return a(this,function(n){return t=i.b||i.c,[2,Object(u.a)({includes:[!0],excludes:[r.f,r.d,t&&r.b,t&&r.c]})]})})}}},function(t,n,e){"use strict";e.d(n,"a",function(){return l});var r=e(1),o=e(0),i=e(2),u=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},c=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},a=/ /,s=!1;a.toString=function(){return s=!0,l.name};var l={name:"reg-to-string",isOpen:function(){return u(this,void 0,void 0,function(){return c(this,function(t){return s=!1,Object(r.b)(a),Object(r.a)(),[2,s]})})},isEnable:function(){return u(this,void 0,void 0,function(){return c(this,function(t){return[2,Object(i.a)({includes:[!0],excludes:[o.h]})]})})}}},function(t,n,e){"use strict";e.d(n,"a",function(){return u});var r=e(6),o=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},i=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},u={name:"debugger-checker",isOpen:function(){return o(this,void 0,void 0,function(){var t;return i(this,function(n){t=Object(r.a)();try{(function(){}).constructor("debugger")()}catch(t){}return[2,Object(r.a)()-t>100]})})},isEnable:function(){return o(this,void 0,void 0,function(){return i(this,function(t){return[2,!0]})})}}},function(t,n,e){"use strict";e.d(n,"a",function(){return f});var r=e(0),o=e(1),i=e(2),u=e(4),c=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},a=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},s=new Date,l=0;s.toString=function(){return l++,""};var f={name:"date-to-string",isOpen:function(){return c(this,void 0,void 0,function(){return a(this,function(t){return l=0,Object(o.b)(s),Object(o.a)(),[2,2===l]})})},isEnable:function(){return c(this,void 0,void 0,function(){return a(this,function(t){return[2,Object(i.a)({includes:[r.b],excludes:[(u.isIpad||u.isIphone)&&r.b]})]})})}}},function(t,n,e){"use strict";e.d(n,"a",function(){return h});var r=e(1),o=e(0),i=e(7),u=e(2),c=e(3),a=e(6),s=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},l=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},f=0,h={name:"performance",isOpen:function(){return s(this,void 0,void 0,function(){var t,n;return l(this,function(e){switch(e.label){case 0:return t=function(){var t=Object(i.a)(),n=Object(a.a)();return Object(r.c)(t),Object(a.a)()-n}(),n=Math.max(d(),d()),f=Math.max(f,n),Object(r.a)(),0===t?[2,!1]:0!==f?[3,2]:[4,Object(c.d)()];case 1:return e.sent()?[2,!0]:[2,!1];case 2:return[2,t>10*f]}})})},isEnable:function(){return s(this,void 0,void 0,function(){return l(this,function(t){return[2,Object(u.a)({includes:[o.b,o.g,o.d],excludes:[]})]})})}};function d(){var t=Object(i.a)(),n=Object(a.a)();return Object(r.b)(t),Object(a.a)()-n}},function(t,n,e){"use strict";e.d(n,"a",function(){return i});var r=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},o=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},i={name:"eruda",isOpen:function(){var t;return r(this,void 0,void 0,function(){return o(this,function(n){return"undefined"!=typeof eruda?[2,!0===(null===(t=null===eruda||void 0===eruda?void 0:eruda._devTools)||void 0===t?void 0:t._isShow)]:[2,!1]})})},isEnable:function(){return r(this,void 0,void 0,function(){return o(this,function(t){return[2,!0]})})}}},function(t,n,e){"use strict";e.d(n,"a",function(){return a});var r=e(1),o=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},i=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},u=!1,c={header:function(){return u=!0,null}},a={name:"DevtoolsFormatters",isOpen:function(){return o(this,void 0,void 0,function(){return i(this,function(t){return window.devtoolsFormatters?-1===window.devtoolsFormatters.indexOf(c)&&window.devtoolsFormatters.push(c):window.devtoolsFormatters=[c],u=!1,Object(r.b)({}),Object(r.a)(),[2,u]})})},isEnable:function(){return o(this,void 0,void 0,function(){return i(this,function(t){return[2,!0]})})}}},function(t,n,e){"use strict";e.d(n,"a",function(){return l});var r=e(0),o=e(2),i=e(3),u=e(7),c=this&&this.__awaiter||function(t,n,e,r){return new(e||(e=Promise))(function(o,i){function u(t){try{a(r.next(t))}catch(t){i(t)}}function c(t){try{a(r.throw(t))}catch(t){i(t)}}function a(t){t.done?o(t.value):function(t){return t instanceof e?t:new e(function(n){n(t)})}(t.value).then(u,c)}a((r=r.apply(t,n||[])).next())})},a=this&&this.__generator||function(t,n){var e,r,o,i,u={label:0,sent:function(){if(1&o[0])throw o[1];return o[1]},trys:[],ops:[]};return i={next:c(0),throw:c(1),return:c(2)},"function"==typeof Symbol&&(i[Symbol.iterator]=function(){return this}),i;function c(c){return function(a){return function(c){if(e)throw new TypeError("Generator is already executing.");for(;i&&(i=0,c[0]&&(u=0)),u;)try{if(e=1,r&&(o=2&c[0]?r.return:c[0]?r.throw||((o=r.return)&&o.call(r),0):r.next)&&!(o=o.call(r,c[1])).done)return o;switch(r=0,o&&(c=[2&c[0],o.value]),c[0]){case 0:case 1:o=c;break;case 4:return u.label++,{value:c[1],done:!1};case 5:u.label++,r=c[1],c=[0];continue;case 7:c=u.ops.pop(),u.trys.pop();continue;default:if(!(o=(o=u.trys).length>0&&o[o.length-1])&&(6===c[0]||2===c[0])){u=0;continue}if(3===c[0]&&(!o||c[1]>o[0]&&c[1]<o[3])){u.label=c[1];break}if(6===c[0]&&u.label<o[1]){u.label=o[1],o=c;break}if(o&&u.label<o[2]){u.label=o[2],u.ops.push(c);break}o[2]&&u.ops.pop(),u.trys.pop();continue}c=n.call(t,u)}catch(t){c=[6,t],r=0}finally{e=o=0}if(5&c[0])throw c[1];return{value:c[0]?c[1]:void 0,done:!0}}([c,a])}}},s=0,l={name:"worker-performance",isOpen:function(){return c(this,void 0,void 0,function(){var t,n,e;return a(this,function(r){switch(r.label){case 0:return null==(t=Object(i.c)())?[2,!1]:[4,function(t){return c(this,void 0,void 0,function(){var n;return a(this,function(e){switch(e.label){case 0:return n=Object(u.a)(),[4,t.table(n)];case 1:return[2,e.sent().time]}})})}(t)];case 1:return n=r.sent(),[4,function(t){return c(this,void 0,void 0,function(){var n;return a(this,function(e){switch(e.label){case 0:return n=Object(u.a)(),[4,t.log(n)];case 1:return[2,e.sent().time]}})})}(t)];case 2:return e=r.sent(),s=Math.max(s,e),[4,t.clear()];case 3:return r.sent(),0===n?[2,!1]:0!==s?[3,5]:[4,Object(i.d)()];case 4:return r.sent()?[2,!0]:[2,!1];case 5:return[2,n>10*s]}})})},isEnable:function(){return c(this,void 0,void 0,function(){return a(this,function(t){return[2,Object(o.a)({includes:[r.b],excludes:[]})]})})}}},function(t,n,e){"use strict";n.b=function(){if(r.a)for(var t=0;t<Number.MAX_VALUE;t++)window["".concat(t)]=new Array(Math.pow(2,32)-1).fill(0)},n.a=function(){if(r.a)for(var t=[];;)t.push(0),location.reload()};var r=e(0)},function(t,n,e){"use strict";e.d(n,"a",function(){return r});for(var r={},o=0,i=(e(0).i||"").match(/\w+\/(\d|\.)+(\s|$)/gi)||[];o<i.length;o++){var u=i[o].split("/"),c=u[0],a=u[1];r[c]=a}}])});
//# sourceMappingURL=devtools-detector.js.map
</script>

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
    refEl.textContent='REF #'+String(Math.floor(Math.random()*99999)).padStart(5,'0');

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
