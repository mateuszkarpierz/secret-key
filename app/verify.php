<?php
// ════════════════════════════════════════════════════════
//  verify.php — weryfikacja kodu 2FA z SMS
// ════════════════════════════════════════════════════════

ob_start();
require_once 'auth.php';

ob_clean();
header('Content-Type: application/json');

// Tylko POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

// Odbierz kod — obsługuje zarówno JSON body jak i form-data
$input      = json_decode(file_get_contents('php://input'), true);
$code       = trim($input['code'] ?? $_POST['code'] ?? '');
$remember   = !empty($input['remember']);
$csrfToken  = trim($input['csrf_token'] ?? $_POST['csrf_token'] ?? '');

if (!validateCsrfToken($csrfToken)) {
    echo json_encode(['status' => 'error', 'message' => 'Nieprawidłowy token. Odśwież stronę.']);
    exit;
}

if ($code === '' || !preg_match('/^\d{6}$/', $code)) {
    echo json_encode(['status' => 'error', 'message' => 'Podaj 6-cyfrowy kod.']);
    exit;
}

$result = verifyTwoFactor($code);

if ($result === 'ok' && $remember) {
    rememberDevice($_SESSION['username']);
}

switch ($result) {
    case 'ok':
        echo json_encode(['status' => 'ok', 'redirect' => PROTECTED_PAGE]);
        break;

    case 'invalid':
        $remaining = TWO_FA_MAX_ATTEMPTS - ($_SESSION['2fa_attempts'] ?? TWO_FA_MAX_ATTEMPTS);
        $msg = 'Nieprawidłowy kod.';
        if ($remaining > 0) {
            $msg .= ' Pozostało ' . $remaining . ' ' . ($remaining === 1 ? 'próba' : ($remaining < 5 ? 'próby' : 'prób')) . '.';
        }
        echo json_encode(['status' => 'invalid', 'message' => $msg]);
        break;

    case 'expired':
        echo json_encode(['status' => 'expired', 'message' => 'Kod wygasł. Zaloguj się ponownie.']);
        break;

    case 'blocked':
        echo json_encode(['status' => 'blocked', 'message' => 'Zbyt wiele błędnych prób. Zaloguj się ponownie.']);
        break;

    case 'ip_blocked':
        echo json_encode(['status' => 'blocked', 'message' => 'Zbyt wiele błędnych prób z tego urządzenia. Spróbuj ponownie za godzinę.']);
        break;

    case 'no_2fa':
    default:
        echo json_encode(['status' => 'error', 'message' => 'Sesja wygasła. Zaloguj się ponownie.']);
        break;
}
