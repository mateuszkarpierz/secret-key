<?php
// SPDX-License-Identifier: MIT
// Copyright (c) 2026 Mateusz Karpierz (karpierz.me)
// ════════════════════════════════════════════════════════
//  tl-status.php — lekki endpoint do odpytywania (polling) przez panel,
//  żeby zmiana stanu (Panic Button, reset przez regenerację configu)
//  odzwierciedlała się na żywo, bez potrzeby odświeżania strony.
// ════════════════════════════════════════════════════════

require_once '../auth.php';
require_once 'timelock.php';
requireLogin();

header('Content-Type: application/json');
$people = $people ?? [];
$status = tl_status($people);

echo json_encode([
    'state'     => $status['state'],
    'unlock_at' => $status['data']['unlock_at'] ?? null,
]);
