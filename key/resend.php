<?php
// ════════════════════════════════════════════════════════
//  resend.php — ponowne wysłanie kodu 2FA
// ════════════════════════════════════════════════════════

ob_start();
require_once 'auth.php';

ob_clean();
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Musi istnieć pending_2fa — czyli użytkownik przeszedł krok 1
if (empty($_SESSION['pending_2fa']) || empty($_SESSION['pending_username'])) {
    echo json_encode(['status' => 'error', 'message' => 'Sesja wygasła. Zaloguj się ponownie.']);
    exit;
}

// Sprawdź cooldown po stronie serwera
$elapsed = time() - ($_SESSION['sms_sent_at'] ?? 0);
if ($elapsed < RESEND_COOLDOWN) {
    $wait = RESEND_COOLDOWN - $elapsed;
    echo json_encode(['status' => 'error', 'message' => "Poczekaj jeszcze {$wait} s przed ponownym wysłaniem."]);
    exit;
}

// Sprawdź limit resendów na sesję
$resendCount = $_SESSION['resend_count'] ?? 0;
if ($resendCount >= 3) {
    echo json_encode(['status' => 'error', 'message' => 'Przekroczono limit wysyłania kodów. Zaloguj się ponownie.']);
    exit;
}

$username = $_SESSION['pending_username'];
$ip       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

global $phone_numbers, $display_names;

$phone = $phone_numbers[$username] ?? null;
if (!$phone) {
    echo json_encode(['status' => 'error', 'message' => 'Błąd konfiguracji.']);
    exit;
}

// Wygeneruj nowy kod i wyślij
$code      = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
$smsResult = sendSmsCode($phone, $code);

if ($smsResult !== true) {
    sk_log("2FA RESEND ERROR: '$username' ($phone) — $smsResult");
    echo json_encode(['status' => 'error', 'message' => 'Nie udało się wysłać SMS. Spróbuj za chwilę.']);
    exit;
}

// Zaktualizuj kod, czas wygaśnięcia i timestamp wysłania w sesji
$_SESSION['2fa_code']     = $code;
$_SESSION['2fa_expires']  = time() + TWO_FA_TTL;
$_SESSION['2fa_attempts'] = 0;
$_SESSION['sms_sent_at']  = time();
$_SESSION['resend_count'] = ($resendCount + 1);

sk_log("2FA RESENT: " . ($display_names[$username] ?? $username) . " ('$username') IP: $ip");

echo json_encode(['status' => 'ok']);
