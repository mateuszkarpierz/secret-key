<?php
// SPDX-License-Identifier: MIT
// Copyright (c) 2026 Mateusz Karpierz (karpierz.me)
// ════════════════════════════════════════════════════════
//  reveal.php: zwraca dane JEDNEJ osoby z listy posiadaczy (imię, telefon)
//  dopiero po kliknięciu „odszyfruj” w panelu (od v3.2.1).
//
//  Wcześniej cała lista trafiała do HTML-a strony od razu po zalogowaniu, więc
//  dało się ją podejrzeć w źródle strony przed kliknięciem. Teraz strona dostaje
//  tylko numery wierszy, a dane pobiera po jednym wierszu: wymaga to sesji,
//  tokena CSRF i mieści się w limicie żądań, a każde odsłonięcie trafia do logu.
//  To nie jest szyfrowanie: dane nadal leżą w secret-key.php, ale nie są
//  wysyłane do przeglądarki, dopóki zalogowana osoba o nie nie poprosi.
// ════════════════════════════════════════════════════════

require_once '../auth.php';
requireLogin();

// Odsłanianie wierszy to ruch „ręczny”. Limit to dwukrotność liczby widocznych wierszy (dwa pełne
// przejścia po liście), ale nie mniej niż 15, więc rośnie razem z liczbą osób w configu.
// Zatrzymuje pętlę, która zaśmiecałaby log. Przed pobraniem samej listy nie chroni (to kilka wierszy),
// a faktyczną kontrolą jest wpis w logu przy każdym odsłonięciu.
define('REVEAL_MIN_REQUESTS', 15);
define('REVEAL_WINDOW', 600); // 10 minut

header('Content-Type: application/json; charset=UTF-8');
header('Cache-Control: no-store, no-cache, must-revalidate');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

if (!validateCsrfToken(trim((string) ($data['csrf_token'] ?? '')))) {
    http_response_code(403);
    echo json_encode(['ok' => false]);
    exit;
}

$username = $_SESSION['username']     ?? 'unknown';
$display  = $_SESSION['display_name'] ?? $username;
$ip       = $_SERVER['REMOTE_ADDR']   ?? 'unknown';

// Ta sama lista i ta sama kolejność co w panelu (index.php): $people z filtrem show_in_panel.
$visiblePeople = array_values(array_filter($people ?? [], fn($p) => $p['show_in_panel'] ?? true));

// Limit per konto (nie per IP), tak jak w arm-timelock.php.
$rlKey = 'reveal_' . md5($username);
$rl = rateLimitCheckAndIncrement($rlKey, max(REVEAL_MIN_REQUESTS, 2 * count($visiblePeople)), REVEAL_WINDOW);
if ($rl['blocked']) {
    sk_log("PERSON REVEAL RATE LIMITED: $display ('$username') IP: $ip");
    http_response_code(429);
    echo json_encode(['ok' => false, 'rate_limited' => true]);
    exit;
}

$idx = $data['i'] ?? null;
if (!is_int($idx) || $idx < 0 || $idx >= count($visiblePeople)) {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

$p = $visiblePeople[$idx];

// W logu tylko numer wiersza, bez imienia i telefonu osoby.
sk_log("PERSON REVEAL: $display ('$username') wiersz: " . ($idx + 1) . " IP: $ip");

echo json_encode([
    'ok'   => true,
    'name' => trim(($p['first_name'] ?? '') . ' ' . ($p['last_name'] ?? '')),
    'tel'  => formatPhoneDisplay((string) ($p['phone'] ?? '')),
], JSON_UNESCAPED_UNICODE);
