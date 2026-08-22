<?php
// SPDX-License-Identifier: MIT
// Copyright (c) 2026 Mateusz Karpierz (karpierz.me)
// ════════════════════════════════════════════════════════
//  auth.php — obsługa sesji, logowania i 2FA via SMS
//  Bez SQL. Dane przechowywane bezpośrednio w tym pliku.
// ════════════════════════════════════════════════════════

session_set_cookie_params([
    'lifetime' => 0,
    'path'     => '/',
    'secure'   => true,
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

// ── KONFIGURACJA ─────────────────────────────────────────

require_once __DIR__ . '/../../../private/secret-key.php';
require_once __DIR__ . '/../../../private/rate-limit.php';
require_once __DIR__ . '/../../../private/lang.php';

define('PROTECTED_PAGE', 'decrypt');
define('LOGIN_PAGE',     'login.php');
define('LOG_FILE',       __DIR__ . '/../../../private/secret-key.log');
define('PRIVATE_DIR',    __DIR__ . '/../../../private');

// Czas ważności kodu 2FA w sekundach (10 minut)
define('TWO_FA_TTL',          600);

// Cooldown między wysłaniem SMS (w sekundach)
define('RESEND_COOLDOWN',     60);

// Maksymalna liczba błędnych prób logowania (hasło) w oknie czasowym
define('LOGIN_MAX_ATTEMPTS', 3);
define('LOGIN_WINDOW',       900); // 15 minut

// Maksymalna liczba zdarzeń DEVTOOLS OPEN/CLOSE per IP w oknie czasowym
// (endpoint publiczny, bez logowania — ochrona przed zaśmiecaniem logu)
define('DEVTOOLS_LOG_MAX_ATTEMPTS', 30);
define('DEVTOOLS_LOG_WINDOW',       300); // 5 minut

// Maksymalna liczba błędnych prób weryfikacji per sesja
define('TWO_FA_MAX_ATTEMPTS', 3);

// Maksymalna liczba błędnych prób 2FA per IP w oknie czasowym
define('TWO_FA_IP_MAX',       3);
define('TWO_FA_IP_WINDOW',    3600); // 1 godzina

// Czas ważności zapamiętanego urządzenia (sekundy)
define('DEVICE_TOKEN_TTL', 7 * 24 * 3600); // 7 dni

// Czas nieaktywności przed auto-logout (sekundy)
define('SESSION_TIMEOUT', 1800); // 30 minut




// ── CSRF TOKEN ───────────────────────────────────────────

function generateCsrfToken(): string {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCsrfToken(string $token): bool {
    return !empty($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}


// ── OSOBY — helpery nad $people (patrz private/secret-key.php) ──

function findPersonByLogin(string $login): ?array {
    global $people;
    foreach ($people as $p) {
        if ($p['login'] === $login) {
            return $p;
        }
    }
    return null;
}

// Do listy posiadaczy w panelu (osoby już zalogowane, widzą się nawzajem w pełni).
// $localNumber to SAM numer lokalny, bez kierunkowego (ten jest osobno w $phone_cc —
// świadomie tu pomijany, lista posiadaczy nigdy nie pokazywała kierunkowego, tylko
// pogrupowane cyfry, np. "512-345-678").
// Grupowanie po 3 cyfry działa dla numerów dowolnej długości, nie tylko 9-cyfrowych
// polskich — zagraniczne numery lokalne bywają krótsze lub dłuższe.
function formatPhoneDisplay(string $localNumber): string {
    $digits = preg_replace('/\D/', '', $localNumber);
    $groups = [];
    for ($i = 0; $i < strlen($digits); $i += 3) {
        $groups[] = substr($digits, $i, 3);
    }
    return implode('-', $groups);
}

// Do ekranu logowania/2FA (przed uwierzytelnieniem — celowo częściowo zamaskowany,
// żeby nie ujawniać pełnego numeru np. komuś patrzącemu przez ramię).
// Kierunkowy ($cc, np. "+48", "+49", "+31") jest teraz osobnym polem w configu
// ($people[...]['phone_cc']) i wyświetlany PRAWDZIWY — bez tego, dla numerów
// zagranicznych ekran logowania pokazywałby fałszywe "+48" niezależnie od kraju.
function formatPhoneMasked(string $cc, string $localNumber): string {
    $digits    = preg_replace('/\D/', '', $localNumber);
    $lastGroup = substr($digits, -3);
    $maskedLen = max(0, strlen($digits) - strlen($lastGroup));
    $maskGroups = [];
    for ($i = 0; $i < $maskedLen; $i += 3) {
        $maskGroups[] = '***';
    }
    $masked = trim(implode(' ', $maskGroups) . ' ' . $lastGroup);
    return trim($cc . ' ' . $masked);
}

// ── TREŚĆ WŁASNA (instrukcje) — markdown-lite ────────────
function md_lite(string $text): string {
    $text = htmlspecialchars($text, ENT_QUOTES, 'UTF-8');
    $text = preg_replace('/\*\*(.+?)\*\*/s', '<strong>$1</strong>', $text);
    $text = preg_replace('/\*(.+?)\*/s',     '<em>$1</em>',         $text);
    return $text;
}

// ── STAN "NIESKONFIGUROWANE" ─────────────────────────────
// Widoczny, czerwony komunikat zamiast cichej pustej sekcji — gdy deployer
// zapomni uzupełnić $instructions/$downloads w configu, albo ukryje w panelu
// (show_in_panel => false) wszystkich posiadaczy naraz.
function empty_state_box(string $message): string {
    return '<div class="alert-box danger">'
        . '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>'
        . htmlspecialchars($message)
        . '</div>';
}

// ── TEKSTY INTERFEJSU (private/lang.php) ─────────────────
function t(string $key, ...$args): string {
    global $lang;
    $text = $lang[$key] ?? $key;
    return $args ? vsprintf($text, $args) : $text;
}


// ── LOGOWANIE DO PLIKU ───────────────────────────────────

function sk_log(string $message): void {
    $tz   = new DateTimeZone('Europe/Warsaw');
    $dt   = new DateTime('now', $tz);

    // Usuń znaki kontrolne (w tym CR/LF) z treści komunikatu — zapobiega
    // "log injection" / "log forging", gdyby ktoś wpisał np. login zawierający
    // znaki nowej linii, próbując wstrzyknąć sfałszowany wpis do pliku logu.
    $safeMessage = preg_replace('/[\x00-\x1F\x7F]+/', ' ', $message);

    $line = '[' . $dt->format('d-M-Y H:i:s') . ' Europe/Warsaw] ' . $safeMessage . "\n";
    @file_put_contents(LOG_FILE, $line, FILE_APPEND | LOCK_EX);
}


// ── OCHRONA STRONY ───────────────────────────────────────

function requireLogin(): void {
    if (empty($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
        header('Location: ' . LOGIN_PAGE);
        exit;
    }

    // Auto-logout po nieaktywności
    $now = time();
    if (!empty($_SESSION['last_activity']) && ($now - $_SESSION['last_activity']) > SESSION_TIMEOUT) {
        sk_log("AUTO-LOGOUT (timeout): " . ($_SESSION['display_name'] ?? '—') . " ('" . ($_SESSION['username'] ?? '—') . "') IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown'));
        $_SESSION = [];
        session_destroy();
        header('Location: ' . LOGIN_PAGE . '?timeout');
        exit;
    }
    $_SESSION['last_activity'] = $now;
}


// ── KROK 1: WERYFIKACJA HASŁA ────────────────────────────
// Przy sukcesie NIE tworzy pełnej sesji — wysyła SMS i czeka na kod.
// Pełna sesja powstaje dopiero po weryfikacji kodu w verify.php.

function attemptLogin(string $username, string $password): array {
    $ip      = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $key     = 'ip_' . md5($ip);
    $userKey = 'user_' . md5($username);

    // Liczniki trwałe (plik na serwerze) — niezależne od ciasteczek/sesji klienta,
    // więc nie da się ich zresetować po prostu nie odsyłając Set-Cookie.
    $ipLimit   = rateLimitCheckAndIncrement($key, LOGIN_MAX_ATTEMPTS, LOGIN_WINDOW);
    $userLimit = rateLimitCheckAndIncrement($userKey, LOGIN_MAX_ATTEMPTS, LOGIN_WINDOW);

    if ($ipLimit['blocked']) {
        sk_log("LOGIN BLOCKED (IP): brute-force for '$username' IP: $ip");
        return ['status' => 'blocked', 'message' => t('auth_too_many_attempts')];
    }

    if ($userLimit['blocked']) {
        sk_log("LOGIN BLOCKED (user): brute-force for '$username' IP: $ip");
        return ['status' => 'blocked', 'message' => t('auth_too_many_attempts')];
    }

    $person = findPersonByLogin($username);

    // Atrapowy hash bcrypt używany, gdy login nie istnieje — dzięki temu
    // password_verify() ZAWSZE wykonuje pełne, kosztowne obliczenie (nawet
    // dla nieistniejącego loginu), a czas odpowiedzi nie zdradza atakującemu,
    // czy dany login w ogóle jest zarejestrowany w systemie (timing attack /
    // user enumeration). Sam hash nie ma znaczenia — nigdy nie pasuje do
    // żadnego realnego hasła, jego jedyną rolą jest "kosztować tyle samo".
    static $dummyHash = '$2b$10$DQfEHvfmYXXMiJKRhqz2c.WyiArnxq1qrtUwPzvT2IQU8dbl/dDvC';

    $hashToCheck = $person['password'] ?? $dummyHash;
    $passwordOk  = password_verify($password, $hashToCheck);

    if ($person === null || !$passwordOk) {
        sk_log("LOGIN FAILED: invalid credentials for '$username' IP: $ip");
        return ['status' => 'invalid', 'message' => t('auth_invalid_credentials')];
    }

    $displayName = $person['first_name'];

    // Hasło OK — wyczyść oba liczniki
    if (isTrustedDevice($username)) {
        rateLimitReset($key);
        rateLimitReset($userKey);
        $_SESSION['logged_in']    = true;
        $_SESSION['username']     = $username;
        $_SESSION['display_name'] = $displayName;
        $_SESSION['login_time']   = time();
        $_SESSION['pending_mail'] = true;
        $_SESSION['last_activity'] = time();
        session_regenerate_id(true);
        sk_log("LOGIN SUCCESS (trusted device): $displayName ('$username') IP: $ip");
        return ['status' => 'trusted', 'redirect' => PROTECTED_PAGE, 'display_name' => $displayName];
    }

    // Urządzenie niezaufane — generuj i wyślij kod 2FA
    $code     = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    $phoneCc  = $person['phone_cc'];   // np. '+48', '+49', '+31'
    $phone    = $person['phone'];      // sam numer lokalny, bez kierunkowego
    $phoneFull = $phoneCc . $phone;    // pełny numer E.164 do wysyłki SMS
    $masked   = formatPhoneMasked($phoneCc, $phone);

    // ── TRYB TESTOWY — usuń przed wdrożeniem na produkcję! ──
    // $smsResult = true; $code = '123456';
    // ────────────────────────────────────────────────────────

    $smsResult = sendSmsCode($phoneFull, $code);

    if ($smsResult !== true) {
        sk_log("2FA SMS ERROR: '$username' ($phoneFull) — $smsResult");
        return ['status' => 'sms_error', 'message' => t('auth_sms_send_failed')];
    }

    rateLimitReset($key);
    rateLimitReset($userKey);

    // Zachowaj resend_count przez regenerację sesji
    $prevResendCount = $_SESSION['resend_count'] ?? 0;

    // Regeneruj ID sesji po weryfikacji hasła — zapobiega session fixation
    session_regenerate_id(true);

    // Przywróć licznik
    $_SESSION['resend_count'] = $prevResendCount;

    // Dane tymczasowe 2FA — NIE ma 'logged_in'
    $_SESSION['pending_2fa']          = true;
    $_SESSION['pending_username']     = $username;
    $_SESSION['pending_display_name'] = $displayName;
    $_SESSION['pending_phone_masked'] = $masked;
    $_SESSION['2fa_phone']            = $phoneFull;
    $_SESSION['2fa_code']             = $code;
    $_SESSION['2fa_expires']          = time() + TWO_FA_TTL;
    $_SESSION['sms_sent_at']          = time();
    $_SESSION['2fa_attempts']         = 0;

    sk_log("2FA SENT: $displayName ('$username') IP: $ip");

    return ['status' => 'ok', 'phone_masked' => $masked];
}


// ── KROK 2: WERYFIKACJA KODU SMS ─────────────────────────

function verifyTwoFactor(string $code): string {
    if (empty($_SESSION['pending_2fa'])) {
        return 'no_2fa';
    }

    if (time() > ($_SESSION['2fa_expires'] ?? 0)) {
        clearPending2FA();
        return 'expired';
    }

    if (($_SESSION['2fa_attempts'] ?? 0) >= TWO_FA_MAX_ATTEMPTS) {
        clearPending2FA();
        return 'blocked';
    }

    // Sprawdź limit błędnych prób per IP — licznik trwały (plik na serwerze),
    // niezależny od sesji/cookies klienta, tak samo jak przy limicie hasła.
    $ip      = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    $ipKey   = 'twofa_ip_' . md5($ip);
    $ipLimit = rateLimitCheckAndIncrement($ipKey, TWO_FA_IP_MAX, TWO_FA_IP_WINDOW);

    if ($ipLimit['blocked']) {
        sk_log("2FA IP BLOCKED: too many failed attempts from IP: $ip");
        clearPending2FA();
        return 'ip_blocked';
    }

    $_SESSION['2fa_attempts']++;

    if (!hash_equals((string)$_SESSION['2fa_code'], trim($code))) {
        $remaining = TWO_FA_MAX_ATTEMPTS - $_SESSION['2fa_attempts'];
        sk_log("2FA FAILED: wrong code for '" . ($_SESSION['pending_username'] ?? '?') . "' IP: $ip (remaining: $remaining, ip_attempts: " . $ipLimit['count'] . ")");
        return 'invalid';
    }

    // Kod poprawny — wyczyść licznik IP i utwórz pełną sesję
    rateLimitReset($ipKey);
    $username = $_SESSION['pending_username'];
    $display  = $_SESSION['pending_display_name'];
    $ip       = $_SERVER['REMOTE_ADDR'] ?? 'unknown';

    clearPending2FA();

    $_SESSION['logged_in']    = true;
    $_SESSION['username']     = $username;
    $_SESSION['display_name'] = $display;
    $_SESSION['login_time']   = time();
    $_SESSION['pending_mail'] = true;
    session_regenerate_id(true);

    sk_log("LOGIN SUCCESS (2FA): $display ('$username') IP: $ip");

    return 'ok';
}

function clearPending2FA(): void {
    unset(
        $_SESSION['pending_2fa'],
        $_SESSION['pending_username'],
        $_SESSION['pending_display_name'],
        $_SESSION['pending_phone_masked'],
        $_SESSION['2fa_code'],
        $_SESSION['2fa_expires'],
        $_SESSION['2fa_attempts']
    );
}


// ── WYŚLIJ SMS ───────────────────────────────────────────
// Zwraca true przy sukcesie lub string z opisem błędu.

function sendSmsCode(string $phone, string $code) {
    $ttlMin = TWO_FA_TTL / 60;
    $msg    = t('twofa_sms_body', $code, $ttlMin) . "\n\n" . SMS_AUTOFILL_DOMAIN . " #$code";

    $params = http_build_query([
        'from'          => SMS_SENDER,
        'to'            => $phone,
        'msg'           => $msg,
        'transactional' => '1',
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL            => 'https://api2.smsplanet.pl/sms',
        CURLOPT_POST           => true,
        CURLOPT_POSTFIELDS     => $params,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_SSL_VERIFYPEER => true,
        CURLOPT_TIMEOUT        => 10,
        CURLOPT_HTTPHEADER     => [
            'Authorization: Bearer ' . SMSPLANET_TOKEN,
            'Content-Type: application/x-www-form-urlencoded',
        ],
    ]);

    $response = curl_exec($ch);
    $curlErr  = curl_error($ch);
    curl_close($ch);

    if ($curlErr) {
        return 'cURL error: ' . $curlErr;
    }

    $data = json_decode($response, true);

    if (!empty($data['messageId'])) {
        return true;
    }

    return $data['errorMsg'] ?? ('API error: ' . $response);
}


// ── ZAPAMIĘTAJ URZĄDZENIE ────────────────────────────────

function rememberDevice(string $username): void {
    $token   = bin2hex(random_bytes(32)); // 64-znakowy losowy token
    $expires = time() + DEVICE_TOKEN_TTL;
    $file    = __DIR__ . '/../../../private/trusted_devices.json';

    // Wczytaj istniejące tokeny
    $devices = [];
    if (file_exists($file)) {
        $devices = json_decode(file_get_contents($file), true) ?? [];
    }

    // Usuń wygasłe tokeny tego użytkownika
    $now = time();
    $devices = array_filter($devices, function($d) use ($now) { return isset($d['expires']) && $d['expires'] > $now; });

    // Dodaj nowy token
    $devices[] = [
        'token'    => hash('sha256', $token),
        'username' => $username,
        'expires'  => $expires,
        'ip'       => $_SERVER['REMOTE_ADDR'] ?? 'unknown',
    ];

    file_put_contents($file, json_encode(array_values($devices)), LOCK_EX);

    setcookie('sk_device', $token, [
        'expires'  => $expires,
        'path'     => '/',
        'secure'   => true,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    sk_log("DEVICE TRUSTED: $username IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'unknown') . " (7 dni)");
}

function isTrustedDevice(string $username): bool {
    $token = $_COOKIE['sk_device'] ?? '';
    if ($token === '') return false;

    $file = __DIR__ . '/../../../private/trusted_devices.json';
    if (!file_exists($file)) return false;

    $devices = json_decode(file_get_contents($file), true) ?? [];
    $hash    = hash('sha256', $token);
    $now     = time();

    // Wyczyść wygasłe tokeny przy każdym sprawdzeniu
    $cleaned = array_filter($devices, function($d) use ($now) {
        return isset($d['expires']) && $d['expires'] > $now;
    });
    if (count($cleaned) !== count($devices)) {
        file_put_contents($file, json_encode(array_values($cleaned)), LOCK_EX);
    }

    foreach ($cleaned as $d) {
        if (
            isset($d['token'], $d['username'], $d['expires']) &&
            $d['username'] === $username &&
            $d['expires']  > $now &&
            hash_equals($d['token'], $hash)
        ) {
            return true;
        }
    }

    return false;
}


// ── WYLOGOWANIE ──────────────────────────────────────────

function logout(string $reason = 'wylogowano'): void {
    $duration = '';
    if (!empty($_SESSION['login_time'])) {
        $diff     = time() - $_SESSION['login_time'];
        $h        = floor($diff / 3600);
        $m        = floor(($diff % 3600) / 60);
        $s        = $diff % 60;
        $duration = ($h > 0 ? $h . 'h ' : '') . sprintf('%02dm %02ds', $m, $s);
    }

    $display  = $_SESSION['display_name'] ?? '—';
    $username = $_SESSION['username']     ?? '—';
    $ip       = $_SERVER['REMOTE_ADDR']   ?? 'unknown';

    $logReason = $reason === 'timeout' ? 'AUTO-LOGOUT (timeout)' : 'LOGOUT';
    sk_log("$logReason: $display ('$username') IP: $ip session duration: $duration");

    $_SESSION = [];
    session_destroy();
    header('Location: ' . LOGIN_PAGE . '?' . $reason);
    exit;
}
