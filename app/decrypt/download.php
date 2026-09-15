<?php
// SPDX-License-Identifier: MIT
// Copyright (c) 2026 Mateusz Karpierz (karpierz.me)
// ════════════════════════════════════════════════════════
//  download.php — bramkowane pobieranie plików (baza haseł, Aegis 2FA)
//
//  Pliki NIE leżą w katalogu publicznym — są bezpośrednio w private/,
//  poza public_html (tam gdzie rate-limit.php). Ten skrypt wymaga aktywnej sesji (requireLogin()) i
//  loguje każde pobranie po stronie serwera, więc log jest gwarantowany
//  niezależnie od tego, czy JS w przeglądarce się wykonał.
// ════════════════════════════════════════════════════════

require_once '../auth.php';
require_once 'timelock.php';
requireLogin();

// ─── Timelock — twarda walidacja niezależna od panelu. Nawet bezpośrednie wejście
// na ten URL z pominięciem interfejsu (?file=...) respektuje blokadę. ───
$tlStatus = tl_status($people ?? []);
if (in_array($tlStatus['state'], ['none', 'pending', 'blocked'], true)) {
    http_response_code($tlStatus['state'] === 'blocked' ? 403 : 423);
    header('Content-Type: text/html; charset=UTF-8');
    $isBlocked = ($tlStatus['state'] === 'blocked');
    $title = $isBlocked ? t('tl_download_blocked_owner_title') : t('tl_download_blocked_title');
    $body  = $isBlocked ? t('tl_download_blocked_owner_body')
           : ($tlStatus['state'] === 'pending' ? t('tl_download_blocked_pending_body') : t('tl_state_a_tooltip'));
    ?><!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang['_html_lang'] ?? 'pl') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= htmlspecialchars($title) ?></title>
<style>
    :root { --bg:#0a0c10; --surface:#111318; --border:#222630; --text:#e2e8f0; --text-dim:#94a3b8; --accent:#c084fc; --danger:#f87171; }
    * { box-sizing: border-box; }
    body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; background:var(--bg); color:var(--text); font-family:'Space Mono',monospace; padding:24px; }
    .card { max-width:460px; width:100%; background:var(--surface); border:1px solid var(--border); border-radius:16px; padding:34px 30px; text-align:center; }
    .icon { font-size:38px; margin-bottom:12px; }
    h1 { font-size:1.15rem; margin:0 0 12px; color: <?= $isBlocked ? 'var(--danger)' : 'var(--accent)' ?>; }
    p { color:var(--text-dim); font-size:0.88rem; line-height:1.6; margin:0; }
</style>
</head>
<body>
    <div class="card">
        <div class="icon"><?= $isBlocked ? '🔒' : '⏳' ?></div>
        <h1><?= htmlspecialchars($title) ?></h1>
        <p><?= htmlspecialchars($body) ?></p>
    </div>
</body>
</html><?php
    $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    sk_log("DOWNLOAD BLOCKED (timelock state: {$tlStatus['state']}): IP: $ip");
    exit;
}

// Biała lista: klucz z URL → prawdziwa nazwa pliku w private/.
$allowed = [];
foreach (($downloads ?? []) as $d) {
    $allowed[$d['key']] = $d['filename'];
}

$key = $_GET['file'] ?? '';

if (!isset($allowed[$key])) {
    http_response_code(404);
    exit('Nie znaleziono pliku.');
}

$filename = $allowed[$key];
$filepath = PRIVATE_DIR . '/' . $filename;

if (!is_file($filepath)) {
    http_response_code(404);
    exit('Nie znaleziono pliku.');
}

// Log po stronie serwera — dzieje się zawsze, niezależnie od JS klienta
$display  = $_SESSION['display_name'] ?? $_SESSION['username'] ?? 'nieznany';
$username = $_SESSION['username']     ?? '—';
$ip       = $_SERVER['REMOTE_ADDR']   ?? 'unknown';
sk_log("DOWNLOAD: $display ('$username') plik: $filename IP: $ip");

// Strumieniowanie pliku
header('Content-Type: application/octet-stream');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . filesize($filepath));
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

readfile($filepath);
exit;
