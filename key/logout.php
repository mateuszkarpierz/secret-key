<?php
// ════════════════════════════════════════════════════════
//  logout.php — wylogowanie użytkownika
// ════════════════════════════════════════════════════════

require_once 'auth.php';

$reason = isset($_GET['timeout']) ? 'timeout' : 'wylogowano';
logout($reason); // Niszczy sesję i przekierowuje na login.php?$reason
