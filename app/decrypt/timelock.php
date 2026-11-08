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

// Blokada na czas sekwencji „sprawdź stan → zapisz” (uzbrojenie / Panic Button).
// Bez niej dwa równoległe żądania mogłyby oba uzbroić timelock (dwa maile, ważny
// tylko token z ostatniego zapisu). Plik blokady leży w private/ obok timelock.json;
// jeśli nie da się go otworzyć, działamy jak dotąd (bez blokady).
function tl_with_lock(callable $fn) {
    $fp = @fopen(PRIVATE_DIR . '/timelock.lock', 'c');
    if ($fp === false) {
        return $fn();
    }
    flock($fp, LOCK_EX);
    try {
        return $fn();
    } finally {
        flock($fp, LOCK_UN);
        fclose($fp);
    }
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
        'unlock_at'   => time() + (48 * 3600),
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
    return (bool) tl_with_lock(function () use ($people) {
        $status = tl_status($people);
        if ($status['state'] !== 'pending' && $status['state'] !== 'expired') {
            return false;
        }
        $data = $status['data'];
        $data['status']     = 'blocked';
        $data['blocked_at'] = time();
        return tl_write($data);
    });
}

// Buduje bezwzględny URL do panic.php.
// Domena (schemat, host, port) pochodzi z $email_notify['panel_url'] w configu — nie z
// nagłówka Host żądania, który może podać klient (powiernik z kontem mógłby inaczej
// podmienić domenę w mailu alarmowym). Z panel_url używamy TYLKO domeny; ścieżka do
// katalogu panelu pochodzi z SCRIPT_NAME (od serwera), więc działa też instalacja
// w podkatalogu. Gdy panel_url jest pusty lub niepoprawny, link budowany jest jak dotąd
// z nagłówka Host (z ostrzeżeniem w logu).
function tl_build_panic_url(string $token): string {
    global $email_notify;
    $dir    = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
    $reqHost = (string) ($_SERVER['HTTP_HOST'] ?? '');
    $origin = null;
    $cfgHost = '';

    $cfg = trim((string) (is_array($email_notify ?? null) ? ($email_notify['panel_url'] ?? '') : ''));
    if ($cfg !== '') {
        if (!preg_match('~^https?://~i', $cfg)) {
            $cfg = 'https://' . $cfg;
        }
        $p = parse_url($cfg);
        if (is_array($p) && !empty($p['host']) && preg_match('/^[A-Za-z0-9.\-]+$/', $p['host'])) {
            $cfgHost = strtolower($p['host']);
            $origin  = strtolower($p['scheme'] ?? 'https') . '://' . $cfgHost
                     . (isset($p['port']) ? ':' . (int) $p['port'] : '');
        }
    }

    if ($origin !== null) {
        // Ostrzeżenie, gdy żądanie przyszło z innej domeny niż panel_url (błędny panel_url
        // albo próba podmiany Host) — link i tak wskazuje domenę z configu.
        $reqHostName = strtolower(preg_replace('/:\d+$/', '', $reqHost));
        if ($reqHostName !== '' && $reqHostName !== $cfgHost) {
            sk_log("PANIC URL: host z żądania ($reqHostName) różni się od panel_url ($cfgHost) — użyto panel_url");
        }
    } else {
        sk_log('PANIC URL: panel_url pusty lub niepoprawny — link zbudowano z nagłówka Host (ustaw panel_url w secret-key.php)');
        $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
        $host   = preg_match('/^([A-Za-z0-9.\-]+|\[[0-9A-Fa-f:]+\])(:\d+)?$/', $reqHost) ? $reqHost : 'localhost';
        $origin = $scheme . '://' . $host;
    }
    return $origin . $dir . '/panic.php?token=' . urlencode($token);
}
