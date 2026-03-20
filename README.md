# Projekt: Secret Key — Instrukcje projektowe

## Język i styl
- Odpowiadaj zawsze po polsku
- Styl komunikacji: bezpośredni, techniczny ale zrozumiały
- Nie używaj skrótów kodowych w opisach dla użytkownika końcowego (np. nie pisz `password_verify()` — zamiast tego opisz słownie co robi)

---

## Czym jest Secret Key
Osobisty system bezpieczeństwa z dwoma celami:
1. Ochrona bazy haseł KeePassXC przed nieautoryzowanym dostępem
2. Awaryjny dostęp dla bliskich po śmierci właściciela

Właściciel dzieli hasło główne do bazy haseł algorytmem Shamira między zaufane osoby (karty Secret Key). Do odtworzenia hasła potrzebna jest minimalna liczba kart + klucz sprzętowy USB.

---

## Stack technologiczny
- **Backend:** PHP 8+, zero SQL, pliki flat (JSON, PHP config)
- **Autoryzacja:** bcrypt (cost=10), 2FA SMS via SMSPlanet API
- **Kryptografia:** Shamir Secret Sharing (secrets.js — kompatybilny z iancoleman.io/shamir)
- **Frontend generatorów:** czysty HTML + JS, zero zależności zewnętrznych (offline)
- **Hosting:** Self-hosted, serwer właściciela

---

## Struktura plików na serwerze

### Folder /key/ (publiczny — system logowania)
- `login.php` — strona logowania z formularzem
- `auth.php` — główna logika: bcrypt, 2FA, brute-force, trusted devices, CSRF
- `verify.php` — weryfikacja kodu SMS
- `resend.php` — ponowne wysłanie kodu SMS
- `logout.php` — wylogowanie
- `prevent-actions.js` — blokada prawego przycisku myszy i DevTools

### Folder /decrypt/ (chroniony — panel użytkownika)
- `index.php` — panel odszyfrowania (instrukcja, lista posiadaczy kart, Shamir w JS)
- `log.php` — logowanie zdarzeń
- `prevent-actions.js` — jak wyżej

### Poza public_html — /private/
- `secret-key.php` — plik konfiguracyjny: hasze bcrypt, numery telefonów (zamaskowane), tokeny SMS
- `trusted_devices.json` — tokeny zaufanych urządzeń (SHA-256, TTL 7 dni)
- `secret-key.log` — logi zdarzeń (logowania, błędy, 2FA)

### Pliki lokalne (nie na serwerze — używane offline)
- `dashboard.html` — panel z dwoma zakładkami: Konfiguracja + Szyfrowanie
- `generate-hash.html` — generator konfiguracji (bcryptjs inline, generuje secret-key.php)
- `generate-shamir.html` — generator udziałów Shamira (secrets.js inline)
- `presentation.html` — prezentacja systemu (one-pager, wszystkie sekcje)
- `favicon.ico` — ikona systemu
- `key.svg` — logo (gradient różowo-niebieski klucz, "Secret Key")

---

## Kluczowe mechanizmy bezpieczeństwa (auth.php)
- **bcrypt** cost=10, format $2y$
- **CSRF** — token 64 hex znaków, bin2hex(random_bytes(32)), weryfikacja hash_equals()
- **Brute-force** — 3 próby/IP + 3 próby/username w oknie 15 min; 3 błędne kody 2FA/sesja i /IP w oknie 1h
- **Trusted devices** — SHA-256, HttpOnly + Secure + SameSite=Strict, TTL 7 dni, plik JSON poza public_html
- **Sesja** — auto-logout 30 min, session_regenerate_id() po każdej weryfikacji, dwuetapowa: pending_2fa → logged_in
- **2FA SMS** — random_int(), kod 6-cyfrowy, TTL 10 min, cooldown 60s między wysyłkami, max 3 resendy/sesję

---

## Shamir Secret Sharing
- Biblioteka: secrets.js (amper5and/secrets.js — ta sama co iancoleman.io/shamir)
- Format udziałów: `8` + 2-hex x-coordinate + data
- minPad: 1024 (identyczny z iancoleman — zapewnia kompatybilność)
- Kodowanie: UTF-8 (str2hex)
- Udziały $2b$ vs $2y$: bcryptjs generuje $2b$, zastępowane na $2y$ dla kompatybilności z PHP

---

## Design systemu (wspólny dla wszystkich plików)
- **Motyw:** ciemny, #0a0c10 tło
- **Kolory:** --accent: #c084fc, --accent2: #818cf8, --success: #4ade80, --danger: #f87171
- **Fonty:** Space Mono (mono), Syne (sans), Barlow Condensed (heading)
- **Kursor:** SVG strzałka + morfujący ring, ripple przy kliknięciu
- **Animacje:** cardFloat, fadeUp, scroll reveal (IntersectionObserver)
- **Favicon:** favicon.ico (zewnętrzny plik)

---

## Karta Secret Key (fizyczny nośnik)
Każda wyznaczona osoba otrzymuje spersonalizowaną kartę zawierającą:
- Login i hasło do systemu (unikalne per osoba)
- Udział Shamira (hex) — jeden z N fragmentów
- Kod QR z tym samym udziałem
- Adres własnej instancji systemu

---

## Ważne decyzje projektowe (historia)
- Nazwy w menu dashboard: "Konfiguracja" (generate-hash) + "Szyfrowanie" (generate-shamir)
- Plik konfiguracyjny nazywa się `secret-key.php` (nie `/private/secret-key.php` w UI)
- Plik udziałów Shamira: `secret-key-shares.txt`
- Nagłówek pliku udziałów: `Secret Key Sharing — udziały`
- Parametry Shamira bez skrótów N/K — używać: "Łączna liczba udziałów" i "Wymagane udziały do odszyfrowania"
- Prezentacja jest self-hosted — nie zawiera adresu karpierz.me/key w treści opisowej
- Stopka prezentacji: `Prawo autorskie © 2026 · karpierz.me` (link do https://karpierz.me)
