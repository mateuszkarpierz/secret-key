<?php
// SPDX-License-Identifier: MIT
// Copyright (c) 2026 Mateusz Karpierz (karpierz.me)
// ════════════════════════════════════════════════════════
//  timelock.php — logika blokady czasowej (Collusion Risk Protection)
//
//  Filozofia: nie walczymy z matematyką Shamira — zakładamy, że powiernicy
//  MOGĄ odzyskać samo hasło (nawet poza panelem). Bramkujemy za to fizyczny
//  plik: pierwsza potwierdzona udana rekonstrukcja w panelu uzbraja 48h
//  blokadę pobierania + alert e-mail z linkiem "Panic Button" do właściciela.
//
//  Stan trzymany w private/timelock.json — POZA public_html, tak jak reszta
//  wrażliwych plików. Ważność stanu jest powiązana z odciskiem (hashem)
//  aktualnego $people: jeśli właściciel wygeneruje NOWY config (np. po
//  kliknięciu Panic Button i zmianie hasła głównego + udziałów), stary
//  timelock automatycznie traci ważność — bez potrzeby ręcznego usuwania
//  pliku przez FTP/SSH w stresującej sytuacji powypadkowej.
// ════════════════════════════════════════════════════════

function tl_path(): string {
    return PRIVATE_DIR . '/timelock.json';
}

// Odcisk aktualnej konfiguracji osób — zmienia się przy każdej regeneracji
// secret-key.php (nowe hashe haseł), nawet jeśli loginy zostają te same.
function tl_config_hash(array $people): string {
    $parts = array_map(function ($p) {
        return ($p['login'] ?? '') . '|' . ($p['password'] ?? '');
    }, $people);
    sort($parts);
    return hash('sha256', implode(',', $parts));
}

function tl_read(): ?array {
    $path = tl_path();
    if (!is_file($path)) return null;
    $raw = @file_get_contents($path);
    if ($raw === false || $raw === '') return null;
    $data = json_decode($raw, true);
    return is_array($data) ? $data : null;
}

function tl_write(array $data): bool {
    return @file_put_contents(tl_path(), json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE), LOCK_EX) !== false;
}

// Zwraca ['state' => 'none'|'pending'|'blocked'|'expired', 'data' => array|null]
function tl_status(array $people): array {
    $data = tl_read();
    if ($data === null) {
        return ['state' => 'none', 'data' => null];
    }

    // Auto-reset: config (hasła/loginy) zmienił się od czasu uzbrojenia —
    // traktujemy stary timelock jako nieważny, niezależnie od jego statusu.
    $currentHash = tl_config_hash($people);
    if (!isset($data['config_hash']) || !hash_equals((string)$data['config_hash'], $currentHash)) {
        return ['state' => 'none', 'data' => null];
    }

    if (($data['status'] ?? '') === 'blocked') {
        return ['state' => 'blocked', 'data' => $data];
    }

    $unlockAt = (int)($data['unlock_at'] ?? 0);
    if (time() < $unlockAt) {
        return ['state' => 'pending', 'data' => $data];
    }

    return ['state' => 'expired', 'data' => $data];
}

// Uzbraja nowy timelock — wywoływane tylko gdy obecny stan to 'none'.
// Zwraca pełne dane (potrzebne do zbudowania linku Panic Button w mailu).
function tl_arm(array $people, string $armedBy, string $armedIp): array {
    $data = [
        'status'      => 'pending',
        'armed_at'    => time(),
        'unlock_at'   => time() + (1 * 60),
        'config_hash' => tl_config_hash($people),
        'panic_token' => bin2hex(random_bytes(32)),
        'armed_by'    => $armedBy,
        'armed_ip'    => $armedIp,
    ];
    tl_write($data);
    return $data;
}

// Oznacza aktualny timelock jako zablokowany przez właściciela (Panic Button).
// Zwraca false, jeśli nie ma aktywnego, ważnego timelocka do zablokowania.
function tl_block(array $people): bool {
    $status = tl_status($people);
    if ($status['state'] !== 'pending' && $status['state'] !== 'expired') {
        return false;
    }
    $data = $status['data'];
    $data['status']     = 'blocked';
    $data['blocked_at'] = time();
    return tl_write($data);
}

// Buduje bezwzględny URL do panic.php na podstawie bieżącego żądania —
// niezależny od freeform pola panel_url w configu (to tylko etykieta w mailu).
function tl_build_panic_url(string $token): string {
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host   = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $dir    = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    return $scheme . '://' . $host . $dir . '/panic.php?token=' . urlencode($token);
}
