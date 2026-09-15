<?php
// SPDX-License-Identifier: MIT
// Copyright (c) 2026 Mateusz Karpierz (karpierz.me)
// ════════════════════════════════════════════════════════
//  panic.php — "Panic Button" z maila alarmowego. Celowo BEZ wymogu
//  logowania (właściciel może być w sytuacji, w której nie ma jak się
//  szybko zalogować) — autoryzacja przez losowy 256-bitowy token
//  związany z konkretnym timelockiem, nie do odgadnięcia.
// ════════════════════════════════════════════════════════

require_once '../auth.php'; // sesja, config ($people, $lang przez t()), sk_log() — celowo bez requireLogin()
require_once 'timelock.php';

$people = $people ?? [];
$token  = trim($_GET['token'] ?? '');
$ip     = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

$success = false;
if ($token !== '') {
    $current = tl_read();
    $validToken = $current !== null && hash_equals((string)($current['panic_token'] ?? ''), $token);
    if ($validToken) {
        $success = tl_block($people);
        if ($success) {
            sk_log("PANIC BUTTON: dostęp do plików zablokowany przez właściciela. IP: $ip");
        }
    }
}
if (!$success) {
    sk_log("PANIC BUTTON FAILED: próba użycia nieprawidłowego/nieaktualnego tokena. IP: $ip");
}
?><!DOCTYPE html>
<html lang="<?= htmlspecialchars($lang['_html_lang'] ?? 'pl') ?>">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="robots" content="noindex, nofollow">
<title><?= $success ? htmlspecialchars(t('tl_panic_success_title')) : htmlspecialchars(t('tl_panic_invalid_title')) ?></title>
<style>
    :root {
        --bg: #0a0c10; --surface: #12151c; --border: #262b38;
        --text: #e2e8f0; --text-dim: #94a3b8; --text-muted: #64748b;
        --accent: #c084fc; --danger: #f87171; --success: #4ade80;
    }
    * { box-sizing: border-box; }
    body {
        margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
        background: var(--bg); color: var(--text);
        font-family: 'Space Mono', monospace; padding: 24px;
    }
    .card {
        max-width: 480px; width: 100%; background: var(--surface);
        border: 1px solid var(--border); border-radius: 16px; padding: 36px 32px; text-align: center;
    }
    .icon { font-size: 40px; margin-bottom: 14px; }
    h1 { font-size: 1.2rem; margin: 0 0 14px; color: <?= $success ? 'var(--danger)' : 'var(--text)' ?>; }
    p { color: var(--text-dim); font-size: 0.9rem; line-height: 1.6; margin: 0; }
</style>
</head>
<body>
    <div class="card">
        <div class="icon"><?= $success ? '🔒' : '⚠️' ?></div>
        <h1><?= $success ? htmlspecialchars(t('tl_panic_success_title')) : htmlspecialchars(t('tl_panic_invalid_title')) ?></h1>
        <p><?= $success ? htmlspecialchars(t('tl_panic_success_body')) : htmlspecialchars(t('tl_panic_invalid_body')) ?></p>
    </div>
</body>
</html>
