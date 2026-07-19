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
    echo json_encode(['status' => 'error', 'message' => t('common_error_bad_token')]);
    exit;
}

if ($code === '' || !preg_match('/^\d{6}$/', $code)) {
    echo json_encode(['status' => 'error', 'message' => t('verify_bad_code_format')]);
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
        $msg = t('verify_invalid_code');
        if ($remaining > 0) {
            $plKey = $remaining === 1 ? 'verify_attempts_left_1' : ($remaining < 5 ? 'verify_attempts_left_few' : 'verify_attempts_left_many');
            $msg .= ' ' . t($plKey, $remaining);
        }
        echo json_encode(['status' => 'invalid', 'message' => $msg]);
        break;

    case 'expired':
        echo json_encode(['status' => 'expired', 'message' => t('verify_expired')]);
        break;

    case 'blocked':
        echo json_encode(['status' => 'blocked', 'message' => t('verify_blocked')]);
        break;

    case 'ip_blocked':
        echo json_encode(['status' => 'blocked', 'message' => t('verify_ip_blocked')]);
        break;

    case 'no_2fa':
    default:
        echo json_encode(['status' => 'error', 'message' => t('verify_session_expired')]);
        break;
}
