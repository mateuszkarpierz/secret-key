<?php
// ════════════════════════════════════════════════════════
//  devtools-log.php — rejestruje wykrycie DevTools
//  Lokalizacja: /public_html/app.secretkey.website/decrypt/
// ════════════════════════════════════════════════════════

require_once '../auth.php';

// Nie używamy requireLogin() — zdarzenie może przyjść z login.php
// gdzie użytkownik jeszcze nie jest zalogowany.
// Sesja jest już wystartowana przez auth.php (session_start()).

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

$allowed = ['DEVTOOLS OPEN', 'DEVTOOLS CLOSE'];
if (!in_array($data['event'], $allowed)) {
    http_response_code(400);
    exit;
}

// Dane użytkownika — zalogowany lub gość
$display  = $_SESSION['display_name'] ?? 'VISITOR';
$username = $_SESSION['username']     ?? 'guest';
$ip       = $_SERVER['REMOTE_ADDR']   ?? 'unknown';

// Dane z JS — sanityzacja
$ref      = isset($data['ref'])      ? preg_replace('/[^A-Z0-9# ]/', '', $data['ref'])         : 'REF #?????';
$page     = isset($data['page'])     ? preg_replace('/[^a-zA-Z0-9._\-\/]/', '', $data['page']) : '?';
$duration = isset($data['duration']) ? (int)$data['duration']                                  : null;

if ($data['event'] === 'DEVTOOLS OPEN') {
    sk_log("DEVTOOLS OPEN: $display ('$username') IP: $ip page: $page ref: $ref");
} else {
    $dur = $duration !== null
        ? sprintf('%02dm %02ds', intdiv($duration, 60), $duration % 60)
        : '?';
    sk_log("DEVTOOLS CLOSE: $display ('$username') IP: $ip page: $page ref: $ref duration: $dur");
}

http_response_code(200);
