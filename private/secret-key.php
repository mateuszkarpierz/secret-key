<?php
// ════════════════════════════════════════════════════════
//  secret-key.php — wrażliwe dane systemu Secret Key
//  Plik poza public_html: /private/demo-secret-key.php
// ════════════════════════════════════════════════════════

// Token API SMSPlanet
define('SMSPLANET_TOKEN', '');
define('SMS_SENDER',      'Secret Key');

// Hashe haseł użytkowników (bcrypt)
$users = [
  'demo' => '$2y$10$t4qj7ionahDnPRX0rI2CBOxE27Sk/6qOdfbvuGW/B2be19n5/XAn2',
];

// Wyświetlane imiona dla każdego loginu
$display_names = [
  'demo'          => 'VISITOR',
];

// Numery telefonów do 2FA (format: 48XXXXXXXXX)
$phone_numbers = [
  'demo'          => '48123456789',
];

// Zamaskowane numery wyświetlane w UI
$phone_masked = [
  'demo'          => '+48 *** *** 789',
];
