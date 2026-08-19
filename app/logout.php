<?php
// SPDX-License-Identifier: MIT
// Copyright (c) 2026 Mateusz Karpierz (karpierz.me)
// ════════════════════════════════════════════════════════
//  logout.php — wylogowanie użytkownika
// ════════════════════════════════════════════════════════

require_once 'auth.php';

// Wylogowanie tylko przez POST + poprawny token CSRF — zapobiega
// "logout CSRF" (np. wymuszeniu wylogowania ofiary przez <img src="…/logout.php">
// osadzony na obcej stronie).
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit('Method not allowed');
}

$csrfToken = trim($_POST['csrf_token'] ?? '');
if (!validateCsrfToken($csrfToken)) {
    http_response_code(403);
    exit(t('common_error_bad_token'));
}

$reason = isset($_POST['timeout']) ? 'timeout' : 'wylogowano';
logout($reason); // Niszczy sesję i przekierowuje na login.php?$reason
