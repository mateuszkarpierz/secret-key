<?php
// ════════════════════════════════════════════════════════
//  rate-limit.php — trwały rate-limiting niezależny od sesji/cookies
//
//  W przeciwieństwie do liczników trzymanych w $_SESSION, dane tutaj
//  żyją w pliku na serwerze i są kluczowane po IP / nazwie użytkownika.
//  Atakujący nie może ich "zresetować" czyszcząc ciasteczka —
//  jedyny sposób na reset to upłynięcie okna czasowego (np. 15 minut).
// ════════════════════════════════════════════════════════

define('RATE_LIMIT_FILE', __DIR__ . '/rate_limits.json');

/**
 * Otwiera plik z licznikami na wyłączność (LOCK_EX), wykonuje na nim
 * podaną operację i zapisuje wynik. Gwarantuje brak race condition
 * przy równoczesnych żądaniach (np. wielu próbach logowania naraz).
 *
 * @param callable $mutator function(array $data): array — zwraca zmodyfikowane dane
 * @return array Dane po modyfikacji
 */
function rlWithLock(callable $mutator): array {
    // 'c+' — utwórz plik jeśli nie istnieje, nie ucinaj zawartości przy otwarciu
    $fp = fopen(RATE_LIMIT_FILE, 'c+');
    if ($fp === false) {
        // Nie powinno się zdarzyć przy poprawnych uprawnieniach katalogu private/,
        // ale w razie czego nie chcemy wywalać się fatalnym błędem logowania.
        return $mutator([]);
    }

    flock($fp, LOCK_EX);

    $size = filesize(RATE_LIMIT_FILE);
    $raw  = $size > 0 ? fread($fp, $size) : '';
    $data = json_decode($raw, true);
    if (!is_array($data)) {
        $data = [];
    }

    $data = $mutator($data);

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($data));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    return $data;
}

/**
 * Sprawdza i zwiększa licznik prób dla danego klucza (np. IP lub username).
 * Okno czasowe liczone jest od pierwszej próby w danej serii.
 *
 * @param string $key           Unikalny identyfikator licznika (np. 'ip_' . md5($ip))
 * @param int    $maxAttempts   Maksymalna dozwolona liczba prób w oknie
 * @param int    $windowSeconds Długość okna czasowego w sekundach
 * @return array ['blocked' => bool, 'count' => int]
 *               Jeśli 'blocked' === true, licznik NIE został zwiększony
 *               (żeby kolejne próby w trakcie blokady nie przedłużały jej w nieskończoność).
 */
function rateLimitCheckAndIncrement(string $key, int $maxAttempts, int $windowSeconds): array {
    $result = ['blocked' => false, 'count' => 0];

    rlWithLock(function (array $data) use ($key, $maxAttempts, $windowSeconds, &$result): array {
        $now   = time();
        $entry = $data[$key] ?? ['count' => 0, 'since' => $now];

        // Okno czasowe minęło — zacznij liczyć od nowa
        if ($now - $entry['since'] > $windowSeconds) {
            $entry = ['count' => 0, 'since' => $now];
        }

        if ($entry['count'] >= $maxAttempts) {
            $result['blocked'] = true;
            $result['count']   = $entry['count'];
            $data[$key] = $entry;
            return $data;
        }

        $entry['count']++;
        $data[$key] = $entry;
        $result['blocked'] = false;
        $result['count']   = $entry['count'];
        return $data;
    });

    return $result;
}

/**
 * Zeruje licznik dla danego klucza (np. po udanym logowaniu).
 */
function rateLimitReset(string $key): void {
    rlWithLock(function (array $data) use ($key): array {
        unset($data[$key]);
        return $data;
    });
}
