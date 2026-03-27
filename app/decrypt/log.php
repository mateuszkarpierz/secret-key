<?php
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

$allowed = ['DECRYPT SUCCESS', 'DECRYPT FAILED', 'DECRYPT ERROR', 'DOWNLOAD'];
$event   = $data['event'];
if (!in_array($event, $allowed)) {
    http_response_code(400);
    exit;
}

$display  = $_SESSION['display_name'] ?? '—';
$username = $_SESSION['username']     ?? '—';
$ip       = $_SERVER['REMOTE_ADDR']   ?? 'unknown';

if ($event === 'DOWNLOAD') {
    $file = isset($data['file']) ? preg_replace('/[^a-zA-Z0-9._\-]/', '', $data['file']) : '?';
    sk_log('DOWNLOAD: ' . $display . ' (\'' . $username . '\') plik: ' . $file . ' IP: ' . $ip);
} else {
    $keys = isset($data['keys']) ? (int)$data['keys'] : '?';
    sk_log($event . ': ' . $display . ' (' . $username . ') keys: ' . $keys . ' IP: ' . $ip);
}

http_response_code(200);
