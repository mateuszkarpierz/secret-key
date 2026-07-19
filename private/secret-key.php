<?php
// ════════════════════════════════════════════════════════
//  secret-key.php — wrażliwe dane systemu Secret Key
//  Plik poza public_html: /private/secret-key.php
// ════════════════════════════════════════════════════════

// Token API SMSPlanet
define('SMSPLANET_TOKEN', '');
define('SMS_SENDER',      'Secret Key');

// Domena widoczna w treści SMS-a (autouzupełnianie kodu na Android/iOS) —
// musi zaczynać się od "@" i być domeną, na której faktycznie działa panel.
define('SMS_AUTOFILL_DOMAIN', '');

// Osoby systemu — jedno źródło prawdy (login, hasło, dane 2FA, widoczność w panelu)
$people = [
  [
    'login'         => 'demo',
    'password'      => '$2y$10$t4qj7ionahDnPRX0rI2CBOxE27Sk/6qOdfbvuGW/B2be19n5/XAn2',
    'first_name'    => 'VISITOR',
    'last_name'     => '',
    'phone'         => '+48123456789',
    'show_in_panel' => false, // konto demonstracyjne
  ],
  [
    'login'         => 'jan-kowalski',
    'password'      => '$2y$10$t4qj7ionahDnPRX0rI2CBOxE27Sk/6qOdfbvuGW/B2be19n5/XAn2',
    'first_name'    => 'Jan',
    'last_name'     => 'Kowalski',
    'phone'         => '+48123456789',
    'show_in_panel' => true,
  ],
  [
    'login'         => 'anna-kowalska',
    'password'      => '$2y$10$t4qj7ionahDnPRX0rI2CBOxE27Sk/6qOdfbvuGW/B2be19n5/XAn2',
    'first_name'    => 'Anna',
    'last_name'     => 'Kowalska',
    'phone'         => '+48123456789',
    'show_in_panel' => true,
  ],
  [
    'login'         => 'piotr-kowalski',
    'password'      => '$2y$10$t4qj7ionahDnPRX0rI2CBOxE27Sk/6qOdfbvuGW/B2be19n5/XAn2',
    'first_name'    => 'Piort',
    'last_name'     => 'Kowalski',
    'phone'         => '+48123456789',
    'show_in_panel' => true,
  ],
  [
    'login'         => 'maria-kowalska',
    'password'      => '$2y$10$t4qj7ionahDnPRX0rI2CBOxE27Sk/6qOdfbvuGW/B2be19n5/XAn2',
    'first_name'    => 'Maria',
    'last_name'     => 'Kowalska',
    'phone'         => '+48123456789',
    'show_in_panel' => true,
  ],
  [
    'login'         => 'andrzej-kowalski',
    'password'      => '$2y$10$t4qj7ionahDnPRX0rI2CBOxE27Sk/6qOdfbvuGW/B2be19n5/XAn2',
    'first_name'    => 'Andrzej',
    'last_name'     => 'Kowalski',
    'phone'         => '+48123456789',
    'show_in_panel' => true,
  ],
];

// Pliki do pobrania — jedno źródło dla przycisków w panelu (index.php)
// i białej listy pobierania (download.php). Same pliki wgrywane są
// ręcznie na serwer do /private/ — ten config trzyma tylko metadane.
$downloads = [
  ['key' => 'baza-hasel', 'label' => 'Pobierz przykładowy plik (demo)',     'filename' => 'demo-baza-hasel.txt'],
  ['key' => 'program',    'label' => 'Pobierz KeePassXC',                   'filename' => 'demo-baza-hasel.txt', 'name' => 'KeePassXC'],
];

// Teksty w sekcji „Pliki do pobrania" panelu — zwykły tekst, BEZ markdown-lite
// (formatowanie ** / * działa tylko w $instructions poniżej).
$download_heading = 'Baza haseł oraz program';
$download_intro = 'W wersji demonstracyjnej zamiast prawdziwej bazy haseł dostępny jest przykładowy plik tekstowy. W rzeczywistym wdrożeniu w tym miejscu znajduje się zaszyfrowana baza KeePassXC właściciela.';
$alert_box_text = 'W prawdziwym systemie: bazę haseł można opcjonalnie wzmocnić dodatkowym kluczem sprzętowym USB — bez niego baza nadal pozostaje chroniona hasłem głównym.';

// Powiadomienie e-mail o logowaniu — jedno źródło prawdy dla adresu/domeny/URL.
// enabled => false wyłącza wysyłkę całkowicie, bez ruszania kodu.
$email_notify = [
];

// Kroki instrukcji — markdown-lite: **pogrubienie** i *kursywa*,
// renderowane przez md_lite() w decrypt/index.php.
$instructions = [
  ['num' => '01', 'text' => '**Zbierzcie się razem.** Skontaktuj się z osobami z listy po prawej stronie (lub poniżej na telefonie). Każda z nich posiada swoją część specjalnego kodu (Secret Key). Potrzebujecie minimum **3 osoby z 5** — dopiero wtedy możliwe jest odblokowanie hasła.'],
  ['num' => '02', 'text' => '**Wejdźcie na tę stronę razem.** Każda osoba powinna mieć przy sobie swoją kartę Secret Key — znajdziecie na niej długi ciąg znaków (np. *8015c7c4f263a74d…*). Kliknijcie „Co to jest Secret Key?" jeśli nie wiecie, gdzie go szukać.'],
  ['num' => '03', 'text' => '**Wprowadźcie kody.** W polu tekstowym po prawej stronie (lub poniżej na telefonie) wpisujcie kolejno kody z kart — każdy kod w osobnej linii, dokładnie tak jak jest napisany na karcie, bez żadnych spacji ani dodatkowych znaków.'],
  ['num' => '04', 'text' => '**Hasło pojawi się automatycznie.** Gdy wpiszecie co najmniej 3 kody, hasło do bazy haseł wyświetli się poniżej pola tekstowego. To właśnie hasło posłuży do otwarcia programu KeePassXC.'],
  ['num' => '05', 'text' => '**Pobierzcie program i bazę haseł.** Na dole strony znajdziecie dwa przyciski: pobierzcie program KeePassXC oraz plik z bazą haseł. Zainstalujcie program, otwórzcie nim pobrany plik i wpiszcie uzyskane hasło. Uwaga: oprócz hasła potrzebny jest też **klucz sprzętowy** (fizyczne urządzenie USB)'],
  ['num' => '06', 'text' => '**Co dalej?** Po uzyskaniu dostępu do bazy haseł znajdziecie tam dane logowania do wszystkich moich kont internetowych. Możecie je wtedy zamknąć lub przejąć zgodnie z wolą rodziny.'],
];