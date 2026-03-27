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
    $panel   = 'https://karpierz.me/key/decrypt/';

    $subject = '🔐 Logowanie do Secret Key Panel — ' . $display;
    $message = "Nowe logowanie do panelu Secret Key.\n\n"
             . "─────────────────────────────\n"
             . "Użytkownik:   " . $display . " (" . ($_SESSION['username'] ?? '—') . ")\n"
             . "Data i czas:  " . $dt . "\n"
             . "Adres IP:     " . $ip . "\n"
             . "Przeglądarka: " . $ua . "\n"
             . "─────────────────────────────\n\n"
             . "Panel: " . $panel . "\n";

    $messageId = sprintf("<%s.%s@karpierz.me>", date('YmdHis'), uniqid());
    $headers   = "Message-ID: $messageId\r\n";
    $headers  .= "From: Secret Key <no-reply@karpierz.me>\r\n";
    $headers  .= "Reply-To: no-reply@karpierz.me\r\n";
    $headers  .= "Return-Path: no-reply@karpierz.me\r\n";
    $headers  .= "X-Sender: no-reply@karpierz.me\r\n";
    $headers  .= "X-Mailer: karpierz.me Secret Key Panel\r\n";
    $headers  .= "X-Priority: 3\r\n";
    $headers  .= "MIME-Version: 1.0\r\n";
    $headers  .= "Content-Type: text/plain; charset=UTF-8\r\n";

    @mail('mateusz@karpierz.me', $subject, $message, $headers, '-fno-reply@karpierz.me');
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
    <link href="https://fonts.googleapis.com/css2?family=Space+Mono:wght@400;700&family=Syne:wght@400;600;700;800&family=Barlow+Condensed:wght@700;800;900&display=swap" rel="stylesheet">
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
            width: 80px; height: 80px;
            filter: drop-shadow(0 0 18px rgba(192,132,252,0.4));
            animation: float 4s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-6px); }
        }
        .header h1 {
            font-family: 'Barlow Condensed', 'Impact', sans-serif;
            font-size: 3.2rem;
            font-weight: 900;
            letter-spacing: 0.08em;
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
            resize: vertical;
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
        @media (max-width: 500px) { .download-grid { grid-template-columns: 1fr; } }

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
            <div class="card">
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
                <h3>Moja baza haseł oraz program</h3>
            </div>
            <p style="font-size:0.85rem; color:var(--text-dim); margin-bottom:16px;">
                Pobierz program i plik z hasłami. Będą Ci potrzebne w następnym kroku.
            </p>
            <div class="alert-box info">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Ważne: oprócz hasła będzie potrzebne fizyczne urządzenie USB — bez niego baza haseł pozostanie zablokowana.
            </div>
            <div class="download-grid">
                <a href="mateusz-karpierz-baza-hasel.kdbx" download class="download-btn" onclick="logDownload('mateusz-karpierz-baza-hasel.kdbx')">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Pobierz bazę haseł
                </a>
                <a href="KeePassXC-2.7.9-Win64.msi" download class="download-btn">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    Pobierz KeePassXC
                </a>
            </div>
        </div>

    </main>

    <footer class="footer">
        <div class="footer-version">WERSJA SYSTEMU: v2.5.0 (build 87)</div>
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
    <script src="prevent-actions.js"></script>

    <!-- ═══════════════════════════════════════ -->
    <!-- PAGE LOGIC -->
    <!-- ═══════════════════════════════════════ -->
    <script>
    var CSRF_TOKEN = '<?= generateCsrfToken() ?>';
    // ─── Person data (fill with real data) ───
    var persons = [
        { label: "1.", name: "Agata Karpierz", tel: "728-479-928" },
        { label: "2.", name: "Piotr Gibas", tel: "663-364-177" },
        { label: "3.", name: "Wojtek Dybał", tel: "722-364-355" },
        { label: "4.", name: "Piotr Rymarczyk", tel: "602-241-220" },
        { label: "5.", name: "Łukasz Suski", tel: "886-352-448" }
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

        document.getElementById('si-browser').textContent = getBrowser();
        document.getElementById('si-os').textContent      = getOS();
        document.getElementById('si-device').textContent  = getDevice();
        document.getElementById('si-screen').textContent  = screen.width + '×' + screen.height;
        document.getElementById('si-tz').textContent      = Intl.DateTimeFormat().resolvedOptions().timeZone;

        // Live licznik czasu sesji
        var loginTs = <?= $session_login_ts ?> * 1000;
        function updateDuration() {
            var diff = Math.floor((Date.now() - loginTs) / 1000);
            var h = Math.floor(diff / 3600);
            var m = Math.floor((diff % 3600) / 60);
            var s = diff % 60;
            var str = '';
            if (h > 0) str += h + 'h ';
            str += (m < 10 ? '0' : '') + m + 'm ' + (s < 10 ? '0' : '') + s + 's';
            document.getElementById('si-duration').textContent = str;
        }
        updateDuration();
        setInterval(updateDuration, 1000);
    })();
    </script>

    <script>
    // ─── Logowanie pobierania pliku ───
    function logDownload(filename) {
        fetch('log.php', {
            method:  'POST',
            headers: { 'Content-Type': 'application/json' },
            body:    JSON.stringify({ event: 'DOWNLOAD', file: filename })
        });
    }
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

</body>
</html>
