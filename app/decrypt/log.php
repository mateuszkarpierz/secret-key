<?php
// SPDX-License-Identifier: MIT
// Copyright (c) 2026 Mateusz Karpierz (karpierz.me)
// ════════════════════════════════════════════════════════
//  log.php — endpoint do logowania zdarzeń z przeglądarki
// ════════════════════════════════════════════════════════

require_once '../auth.php';
requireLogin();

// Tylko POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (empty($data['event'])) {
    http_response_code(400);
    exit;
}

$csrfToken = trim($data['csrf_token'] ?? '');
if (!validateCsrfToken($csrfToken)) {
    http_response_code(403);
    exit;
}

$allowed = ['DECRYPT SUCCESS', 'DECRYPT FAILED', 'DECRYPT ERROR'];
$event   = $data['event'];
if (!in_array($event, $allowed)) {
    http_response_code(400);
    exit;
}

$display  = $_SESSION['display_name'] ?? '—';
$username = $_SESSION['username']     ?? '—';
$ip       = $_SERVER['REMOTE_ADDR']   ?? 'unknown';

$keys = isset($data['keys']) ? (int)$data['keys'] : '?';

// Metoda weryfikacji poprawności złożonego sekretu (patrz index.php) — tylko przy DECRYPT SUCCESS,
// białą listę wartości sprawdzamy twardo, żeby nikt nie wstrzyknął dowolnego tekstu do logu.
$verifiedBySuffix = '';
if ($event === 'DECRYPT SUCCESS') {
    $allowedVerifiedBy = ['subset_consistency', 'format_check'];
    $verifiedBy = trim((string)($data['verified_by'] ?? ''));
    if (in_array($verifiedBy, $allowedVerifiedBy, true)) {
        $verifiedBySuffix = ' verified_by: ' . $verifiedBy;
    }
}

sk_log($event . ': ' . $display . ' (' . $username . ') keys: ' . $keys . $verifiedBySuffix . ' IP: ' . $ip);

http_response_code(200);
