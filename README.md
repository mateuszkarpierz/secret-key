<div align="center">

<img src="img/secret-key-logo.svg" width="400" alt="Secret Key Logo">

### Kryptograficzny system awaryjnego dostępu do bazy haseł

[![PHP](https://img.shields.io/badge/PHP-8%2B-7c3aed?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![License](https://img.shields.io/badge/Licencja-MIT-818cf8?style=flat-square)](LICENSE)
[![Self-hosted](https://img.shields.io/badge/Hosting-Self--hosted-c084fc?style=flat-square)](#instalacja)
[![Zero SQL](https://img.shields.io/badge/Baza_danych-Zero_SQL-4ade80?style=flat-square)](#struktura-plików)
[![2FA](https://img.shields.io/badge/2FA-SMS_·_SMSPlanet-38bdf8?style=flat-square)](#bezpieczeństwo)
[![Shamir](https://img.shields.io/badge/Kryptografia-Shamir_SSS-f472b6?style=flat-square)](#algorytm-shamira)

<br>

**Co się stanie z Twoimi hasłami po śmierci?**  
Secret Key to system, który dzieli hasło główne między zaufane osoby.  
Nikt nie zna go samodzielnie — dopiero razem mogą je odtworzyć.

<br>

![Secret Key Demo](img/SecretKeyGif.gif)

</div>

---

## 📋 Spis treści

- [Czym jest Secret Key](#czym-jest-secret-key)
- [Jak to działa](#jak-to-działa)
- [Algorytm Shamira](#algorytm-shamira)
- [Bezpieczeństwo](#bezpieczeństwo)
- [Instalacja](#instalacja)
- [Struktura plików](#struktura-plików)
- [FAQ](#faq)

---

## Czym jest Secret Key

Secret Key rozwiązuje dwa problemy jednocześnie:

| Problem | Rozwiązanie |
|---|---|
| 🔐 Nieautoryzowany dostęp za życia właściciela | Każda próba dostępu wymaga kodu SMS + hasła |
| 💀 Utrata dostępu do kont po śmierci | Wyznaczone osoby odtwarzają hasło algorytmem Shamira |

System jest **w pełni self-hosted** — dane nigdy nie opuszczają Twojego serwera. Nie ma centralnej bazy danych, nie ma chmury, nie ma zależności od zewnętrznych usług (poza wysyłką SMS).

### Stack technologiczny

```
Backend:       PHP 8+, pliki flat (JSON + PHP config), zero SQL
Autoryzacja:   bcrypt cost=10, 2FA SMS via SMSPlanet API
Kryptografia:  Shamir Secret Sharing (secrets.js)
Frontend:      Czysty HTML + JS, zero zależności zewnętrznych
```

---

## Jak to działa

W sytuacji kryzysowej wyznaczone osoby wykonują **4 kroki**:

```
┌─────────────────────────────────────────────────────────┐
│                                                         │
│  01 ZEBRANIE        Minimum 3 z 5 wyznaczonych osób     │
│     ────────        Każda ma kartę z fragmentem klucza  │
│                                                         │
│  02 LOGOWANIE       Dane z karty Secret Key             │
│     ─────────       + kod SMS na przypisany telefon     │
│                                                         │
│  03 UDZIAŁY         Każda osoba wpisuje swój kod        │
│     ────────        lub skanuje kod QR z karty          │
│                                                         │
│  04 DOSTĘP          Hasło odtworzone lokalnie w         │
│     ───────         przeglądarce — nie trafia na serwer │
│                                                         │
└─────────────────────────────────────────────────────────┘
```

### Karta Secret Key

Każda wyznaczona osoba otrzymuje kartę zawierającą:

| Element | Opis |
|---|---|
| 🔑 Login i hasło | Unikalne dane dostępowe per osoba |
| 🔢 Udział Shamira | Fragment klucza hex — jeden z pięciu |
| ⊞ Kod QR | Ten sam udział dla wygody skanowania |
| 🌐 Adres systemu | URL własnej instancji Secret Key |

Fizyczny format karty jest **dowolny** — wydruk na kartce, PDF, karta plastikowa. Ważne żeby zawierała te cztery elementy.

---

## Algorytm Shamira

Secret Key używa biblioteki [`secrets.js`](https://github.com/amper5and/secrets.js) — **tej samej co [iancoleman.io/shamir](https://iancoleman.io/shamir/)**, co zapewnia pełną kompatybilność.

### Jak działa podział sekretu

```
Hasło główne: "MojeHasloDoKeePass2024!"
         │
         ▼ Podział na 5 udziałów (próg: 3)
         │
    ┌────┴────────────────────────────┐
    │                                 │
  S1: 801a3f9c2e4b7d1...  → Osoba A   │
  S2: 802c8f1a5e9b3d7...  → Osoba B   │  Każdy udział
  S3: 803e2a7f4c1b9d5...  → Osoba C   │  jest bezużyteczny
  S4: 804b6d3e8f2a1c9...  → Osoba D   │  bez pozostałych
  S5: 805d9f7b2e4c3a1...  → Osoba E   │
    │                                 │
    └────┬────────────────────────────┘
         │
         ▼ Rekonstrukcja (wystarczą 3 z 5)
         │
    S1 + S2 + S3 = "MojeHasloDoKeePass2024!" ✓
```

### Parametry konfiguracyjne

| Parametr | Wartość | Opis |
|---|---|---|
| Biblioteka | secrets.js (amper5and) | Kompatybilna z iancoleman.io |
| Format udziałów | `8` + 2-hex x-coord + data | Standard biblioteki |
| minPad | 1024 | Ochrona przed atakami na małe sekrety |
| Kodowanie | UTF-8 (str2hex) | |
| Rekonstrukcja | JavaScript (przeglądarka) | Hasło nie trafia na serwer |

### Bezpieczeństwo matematyczne

Posiadanie **mniejszej niż wymagana** liczby udziałów nie daje żadnej informacji o sekrecie (**information-theoretic security**). Nawet dysponując 4 z 5 udziałów, bez piątego sekret pozostaje matematycznie niemożliwy do odtworzenia.

```
f(x) = a₀ + a₁x + a₂x² + ... + aₖ₋₁xᵏ⁻¹  (mod p)
```

---

## Bezpieczeństwo

System łączy **6 niezależnych warstw ochrony** — kompromitacja jednej nie daje dostępu.

### Warstwy ochrony

| Warstwa | Mechanizm | Szczegóły |
|---|---|---|
| 🔒 Hasła | bcrypt | cost=10, format `$2y$`, timing-safe verify |
| 🛡️ CSRF | Token 64 hex | `bin2hex(random_bytes(32))` + `hash_equals()` |
| 🚫 Brute-force | Rate limiting | 3 próby/IP + 3/login w oknie 15 min |
| 📱 2FA SMS | Kod 6-cyfrowy | `random_int()`, TTL 10 min, cooldown 60s |
| 💻 Trusted devices | SHA-256 cookie | HttpOnly + Secure + SameSite=Strict, TTL 7 dni |
| ⏱️ Sesja | Auto-logout | 30 min timeout, `session_regenerate_id()` po każdej weryfikacji |

### Przechowywanie danych

```
public_html/
├── key/
│   ├── login.php        # Formularz logowania
│   ├── auth.php         # Logika: bcrypt, 2FA, brute-force, CSRF
│   ├── verify.php       # Weryfikacja kodu SMS
│   ├── resend.php       # Ponowna wysyłka SMS
│   └── logout.php       # Wylogowanie
└── decrypt/
    ├── index.php        # Panel odszyfrowania (Shamir w JS)
    └── log.php          # Logowanie zdarzeń

private/                 # POZA public_html
├── secret-key.php       # Hasze bcrypt, numery tel. (zamaskowane)
├── trusted_devices.json # Tokeny urządzeń (SHA-256)
└── secret-key.log       # Logi zdarzeń
```

> ⚠️ Pliki konfiguracyjne przechowywane są **poza katalogiem publicznym** serwera — nawet w przypadku błędnej konfiguracji webservera nie są dostępne z zewnątrz.

---

## Instalacja

### Wymagania

- PHP 8.0+
- Serwer WWW (Apache / Nginx)
- Konto w [SMSPlanet](https://smsplanet.pl) (wysyłka 2FA)
- Dostęp do katalogu poza `public_html`

### Krok 1 — Przygotowanie konfiguracji (offline)

Otwórz `dashboard.html` lokalnie w przeglądarce. Panel zawiera dwie zakładki:

**Konfiguracja** — wygeneruj plik `secret-key.php`:
1. Wprowadź loginy, hasła i numery telefonów dla każdej osoby
2. Kliknij „Generuj konfigurację"
3. Pobierz plik `secret-key.php`

**Szyfrowanie** — podziel hasło główne na udziały:
1. Wpisz hasło główne do bazy haseł
2. Ustaw łączną liczbę udziałów i wymagane minimum
3. Kliknij „Generuj udziały"
4. Pobierz plik `secret-key-shares.txt`

### Krok 2 — Wgranie na serwer

```bash
# Struktura katalogów na serwerze
/home/user/
├── public_html/
│   ├── key/           ← skopiuj zawartość folderu /key/
│   └── decrypt/       ← skopiuj zawartość folderu /decrypt/
└── private/           ← POZA public_html
    └── secret-key.php ← wgraj wygenerowany plik konfiguracyjny
```

### Krok 3 — Konfiguracja ścieżki

W pliku `auth.php` zaktualizuj ścieżkę do pliku konfiguracyjnego:

```php
require_once '/home/user/private/secret-key.php';
```

### Krok 4 — Dystrybucja kart

Dla każdej wyznaczonej osoby przygotuj kartę z:
- Loginem i hasłem (z panelu Konfiguracja)
- Udziałem Shamira (z pliku `secret-key-shares.txt`)
- Adresem Twojej instancji systemu

---

## Struktura plików

```
secret-key/
│
├── 📁 key/                    # Publiczny — system logowania
│   ├── login.php
│   ├── auth.php
│   ├── verify.php
│   ├── resend.php
│   ├── logout.php
│   └── prevent-actions.js
│
├── 📁 decrypt/                # Chroniony — panel użytkownika
│   ├── index.php
│   ├── log.php
│   └── prevent-actions.js
│
├── 📁 img/                    # Zasoby graficzne
│   ├── key.svg
│   └── ...
│
├── dashboard.html             # Panel konfiguracyjny (offline)
├── generate-hash.html         # Generator konfiguracji
├── generate-shamir.html       # Generator udziałów Shamira
├── presentation.html          # Prezentacja systemu
└── favicon.ico
```

---

## FAQ

<details>
<summary><strong>Czy system działa bez internetu?</strong></summary>

Rekonstrukcja hasła odbywa się **całkowicie lokalnie w przeglądarce** (JavaScript). Połączenie internetowe potrzebne jest tylko do logowania (weryfikacja hasła przez PHP) i wysyłki kodu SMS.

</details>

<details>
<summary><strong>Co jeśli jedna z wyznaczonych osób umrze lub straci kartę?</strong></summary>

Wystarczy wygenerować nową konfigurację w panelu `dashboard.html`, zmienić dane tej osoby i rozdać nowe karty wszystkim uczestnikom. Stare karty przestają działać po zastąpieniu pliku `secret-key.php` na serwerze.

</details>

<details>
<summary><strong>Czy udziały Shamira są bezpieczne do przesłania emailem?</strong></summary>

Matematycznie tak — pojedynczy udział nie ujawnia żadnej informacji o sekrecie. Jednak dla maksymalnego bezpieczeństwa zaleca się przekazanie kart osobiście.

</details>

<details>
<summary><strong>Jak wybrać próg (minimum udziałów)?</strong></summary>

Zalecane konfiguracje:

| Łączna liczba | Minimum | Scenariusz |
|---|---|---|
| 3 | 2 | Mała rodzina |
| 5 | 3 | Standardowy (zalecany) |
| 7 | 4 | Większa rodzina / firma |

Im wyższy próg, tym większe bezpieczeństwo — ale też większa trudność zebrania się w sytuacji kryzysowej.

</details>

<details>
<summary><strong>Czy mogę używać systemu z innym menedżerem haseł niż KeePassXC?</strong></summary>

Tak. Secret Key przechowuje i odtwarza **dowolne hasło główne** — niezależnie od używanego menedżera haseł. Kompatybilny z każdym menedżerem obsługującym hasło główne.

</details>

---

<div align="center">

Prawo autorskie © 2026 · [karpierz.me](https://karpierz.me)

</div>
