<?php
// ════════════════════════════════════════════════════════
//  download.php — bramkowane pobieranie plików (baza haseł, Aegis 2FA)
//
//  Pliki NIE leżą w katalogu publicznym — są bezpośrednio w private/,
//  poza public_html (tam gdzie rate-limit.php). Ten skrypt wymaga aktywnej sesji (requireLogin()) i
//  loguje każde pobranie po stronie serwera, więc log jest gwarantowany
//  niezależnie od tego, czy JS w przeglądarce się wykonał.
// ════════════════════════════════════════════════════════

require_once '../auth.php';
requireLogin();

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
