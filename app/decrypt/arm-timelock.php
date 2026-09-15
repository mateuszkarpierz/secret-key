<?php
// SPDX-License-Identifier: MIT
// Copyright (c) 2026 Mateusz Karpierz (karpierz.me)
// ════════════════════════════════════════════════════════
//  arm-timelock.php — wywoływane przez panel po potwierdzonej,
//  poprawnej rekonstrukcji sekretu (patrz index.php, verifiedSubsets /
//  verifiedFormat). Idempotentne: jeśli timelock jest już uzbrojony
//  (pending/expired/blocked) dla aktualnego configu, nic nie robi
//  i nie wysyła kolejnego maila — uzbrojenie ma się zdarzyć raz.
// ════════════════════════════════════════════════════════

// Jawne buforowanie od startu — niezależnie od ustawień output_buffering na serwerze,
// żeby echo poniżej NA PEWNO nie wysłało nic do przeglądarki przed header('Connection: close')
// w gałęzi fallback (bez tego, na serwerze z wyłączonym buforowaniem, ten header rzuciłby
// ostrzeżenie "headers already sent" i cała sztuczka z szybką odpowiedzią by nie zadziałała).
ob_start();

require_once '../auth.php';
require_once 'timelock.php';
requireLogin();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$csrfToken = trim($data['csrf_token'] ?? '');
if (!validateCsrfToken($csrfToken)) {
    http_response_code(403);
    exit;
}

header('Content-Type: application/json');

$people = $people ?? [];
$status = tl_status($people);

if ($status['state'] !== 'none') {
    // Już uzbrojony (albo trwa odliczanie, albo minęło, albo zablokowany) — nic nie robimy.
    echo json_encode(['armed' => false, 'state' => $status['state']]);
    exit;
}

$display = $_SESSION['display_name'] ?? $_SESSION['username'] ?? 'nieznany';
$username = $_SESSION['username'] ?? '—';
$ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
$armedBy = $display . ' (' . $username . ')';

$tlData = tl_arm($people, $armedBy, $ip);

sk_log("TIMELOCK ARMED: $armedBy IP: $ip unlock_at: " . date('d.m.Y H:i:s', $tlData['unlock_at']));

// ─── Odpowiadamy przeglądarce OD RAZU, zanim wyślemy maila — synchroniczna wysyłka przez
// mail()/SMTP potrafi zająć kilka-kilkanaście sekund, a nie chcemy, żeby licznik na ekranie
// startował z tym opóźnieniem "z tyłu" (użytkownik zgłosił, że licznik startował np. od
// 47h 59m 47s zamiast równych 48h). fastcgi_finish_request() (dostępne pod PHP-FPM, czyli
// na zdecydowanej większości hostingów) kończy połączenie z klientem, ale pozwala skryptowi
// dalej działać w tle — dokładnie tam wysyłamy maila. Fallback (flush) dla środowisk bez FPM.
echo json_encode(['armed' => true, 'unlock_at' => $tlData['unlock_at']]);

if (function_exists('fastcgi_finish_request')) {
    fastcgi_finish_request();
} else {
    ignore_user_abort(true);
    header('Connection: close');
    if (ob_get_level() > 0) { ob_end_flush(); }
    flush();
}

// ─── Mail alarmowy z linkiem Panic Button — ta sama infrastruktura co powiadomienie o logowaniu ───
// (wykonuje się już PO odesłaniu odpowiedzi do przeglądarki — patrz komentarz wyżej)
$email_notify = $email_notify ?? ['enabled' => false];
$to        = $email_notify['to'] ?? '';
$fromEmail = $email_notify['from_email'] ?? '';
$fromName  = $email_notify['from_name'] ?? 'Secret Key';

if (empty($email_notify['enabled'])) {
    sk_log("TIMELOCK MAIL SKIPPED: email_notify.enabled=false w private/secret-key.php.");
} elseif ($to === '' || $fromEmail === '') {
    sk_log("TIMELOCK MAIL SKIPPED: brak 'to' lub 'from_email' w \$email_notify (private/secret-key.php).");
} else {
    $panicUrl = tl_build_panic_url($tlData['panic_token']);
    $dt       = date('d.m.Y H:i:s', $tlData['armed_at']);

    $subject = t('tl_mail_subject');
    $message = t('tl_mail_intro') . "\n\n"
             . "─────────────────────────────\n"
             . t('tl_mail_by_label') . $armedBy . "\n"
             . t('tl_mail_date_label') . $dt . "\n"
             . t('tl_mail_ip_label') . $ip . "\n"
             . "─────────────────────────────\n\n"
             . t('tl_mail_explain') . "\n\n"
             . t('tl_mail_panic_intro') . "\n"
             . t('tl_mail_panic_label') . $panicUrl . "\n\n"
             . t('tl_mail_footer') . "\n";

    $fromDomain = substr(strrchr($fromEmail, '@'), 1) ?: 'localhost';
    $messageId  = sprintf("<%s.%s@%s>", date('YmdHis'), uniqid(), $fromDomain);

    $headers  = "Message-ID: $messageId\r\n";
    $headers .= "From: " . $fromName . " <" . $fromEmail . ">\r\n";
    $headers .= "Reply-To: " . $fromEmail . "\r\n";
    $headers .= "Return-Path: " . $fromEmail . "\r\n";
    $headers .= "X-Sender: " . $fromEmail . "\r\n";
    $headers .= "X-Mailer: " . $fromName . " Panel\r\n";
    $headers .= "X-Priority: 1\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    if (!@mail($to, $subject, $message, $headers, '-f' . $fromEmail)) {
        sk_log("TIMELOCK MAIL FAILED: nie udało się wysłać alertu Panic Button — armed by: $armedBy IP: $ip");
    }
}
