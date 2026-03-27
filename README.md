<div align="center">

<img src="img/secret-key-logo.svg" width="380" alt="Secret Key Logo">

<br>

### Kryptograficzny system awaryjnego dostępu do bazy haseł

<br>

[![PHP](https://img.shields.io/badge/PHP-8%2B-7c3aed?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Zero SQL](https://img.shields.io/badge/Baza_danych-Zero_SQL-4ade80?style=flat-square)](#stack)
[![Self-hosted](https://img.shields.io/badge/Hosting-Self--hosted-c084fc?style=flat-square)](#instalacja)
[![2FA](https://img.shields.io/badge/2FA-SMS_·_SMSPlanet-38bdf8?style=flat-square)](#bezpieczeństwo)
[![Shamir](https://img.shields.io/badge/Kryptografia-Shamir_SSS-f472b6?style=flat-square)](#algorytm-shamira)
[![License](https://img.shields.io/badge/Licencja-MIT-818cf8?style=flat-square)](LICENSE)

<br>

**Co się stanie z Twoimi kontami po śmierci?**

Secret Key dzieli hasło główne do bazy haseł kryptograficznie między zaufane osoby.  
Nikt nie zna go samodzielnie — dopiero razem, w uzgodnionym momencie, mogą je odtworzyć.

<br>

![Secret Key Demo](img/SecretKeyGif.gif)

<br>

[**→ Zobacz demo**](https://app.secretkey.website) &nbsp;·&nbsp; [**Prezentacja systemu**](#jak-to-działa) &nbsp;·&nbsp; [**Instalacja**](#instalacja)

</div>

---

## Spis treści

- 01 · 🔑 [Idea](#idea)
- 02 · ⚙️ [Jak to działa](#jak-to-działa)
- 03 · 💳 [Karta Secret Key](#karta-secret-key)
- 04 · 🔐 [Algorytm Shamira](#algorytm-shamira)
- 05 · 🏗️ [Architektura systemu](#architektura-systemu)
- 06 · 🛡️ [Bezpieczeństwo](#bezpieczeństwo)
- 07 · 🚀 [Instalacja](#instalacja)
- 08 · 📁 [Struktura plików](#struktura-plików)
- 09 · ❓ [FAQ](#faq)

---

## Idea

Każdy z nas przechowuje dziesiątki haseł — do banków, poczty, mediów społecznościowych, subskrypcji. **Co się z nimi stanie po naszej śmierci?** Rodzina zostaje odcięta od kont, nie może anulować subskrypcji, zamknąć kont, odzyskać pieniędzy.

Secret Key rozwiązuje ten problem, tworząc bezpieczny plan awaryjny z dwoma gwarancjami:

| &nbsp; | Problem | Rozwiązanie |
|---|---|---|
| 🔐 | Nieautoryzowany dostęp **za życia** właściciela | Każda próba dostępu wymaga hasła + kodu SMS |
| 💀 | Utrata dostępu do kont **po śmierci** | Wyznaczone osoby odtwarzają hasło algorytmem Shamira |

System jest **w pełni self-hosted** — dane nigdy nie opuszczają Twojego serwera. Żadnej centralnej bazy danych, żadnej chmury, żadnych zewnętrznych zależności poza wysyłką SMS.

<a name="stack"></a>

```
Backend       PHP 8+, pliki flat (JSON + PHP config), zero SQL
Autoryzacja   bcrypt cost=10, 2FA SMS via SMSPlanet API
Kryptografia  Shamir Secret Sharing (secrets.js)
Frontend      Czysty HTML + JS, zero zewnętrznych zależności (offline)
```

---

## Jak to działa

W sytuacji kryzysowej wyznaczone osoby wykonują cztery kroki:

```
┌──────────────────────────────────────────────────────────────────────┐
│                                                                      │
│  01  ZEBRANIE        Min. 3 z 5 wyznaczonych osób                    │
│      ─────────       Każda ma kartę z fragmentem klucza              │
│                                                                      │
│  02  LOGOWANIE       Dane logowania z karty Secret Key               │
│      ──────────      + kod SMS na przypisany numer telefonu          │
│                                                                      │
│  03  UDZIAŁY         Każda osoba wpisuje swój fragment lub           │
│      ─────────       skanuje kod QR z karty                          │
│                                                                      │
│  04  DOSTĘP          Hasło odtworzone lokalnie w przeglądarce —      │
│      ────────        nie trafia na serwer                            │
│                                                                      │
└──────────────────────────────────────────────────────────────────────┘
```

**Bez telefonu przypisanego do konta — logowanie niemożliwe**, nawet przy znajomości danych z karty.  
**Bez wymaganej liczby udziałów — odtworzenie hasła matematycznie niemożliwe**, nawet przy dostępie do serwera.

---

## Karta Secret Key

Każda wyznaczona osoba otrzymuje spersonalizowany nośnik z czterema elementami:

| Element | Opis |
|---|---|
| 🔑 **Login i hasło** | Unikalne dane dostępowe — każda osoba ma własne konto z przypisanym numerem telefonu |
| 🔢 **Udział Shamira** | Fragment klucza w formacie hex — jeden z pięciu, bezużyteczny bez pozostałych |
| ⊞ **Kod QR** | Ten sam udział zakodowany jako QR — skanowanie eliminuje błędy ręcznego przepisywania |
| 🌐 **Adres systemu** | URL własnej instancji Secret Key — prowadzi prosto do panelu logowania |

**Format nośnika jest dowolny** — wydruk na kartce, PDF, karta plastikowa. Ważne, aby zawierał powyższe cztery elementy.

---

## Algorytm Shamira

Secret Key używa biblioteki [`secrets.js`](https://github.com/amper5and/secrets.js) do podziału i rekonstrukcji sekretu.

> [!NOTE]
> Użyta biblioteka jest **identyczna z tą stosowaną przez [iancoleman.io/shamir](https://iancoleman.io/shamir/)** — format udziałów jest w pełni kompatybilny, co umożliwia niezależną weryfikację poza systemem.

### Podział i rekonstrukcja sekretu

```
Hasło główne: "MojeHasloDoKeePass2024!"
         │
         ▼  Podział na 5 udziałów (próg: 3)
         │
    ┌────┴──────────────────────────────────┐
    │                                       │
    │  S1: 801a3f9c2e4b7d1...  →  Osoba A   │  Każdy udział
    │  S2: 802c8f1a5e9b3d7...  →  Osoba B   │  jest bezużyteczny
    │  S3: 803e2a7f4c1b9d5...  →  Osoba C   │  bez wymaganej
    │  S4: 804b6d3e8f2a1c9...  →  Osoba D   │  liczby pozostałych
    │  S5: 805d9f7b2e4c3a1...  →  Osoba E   │
    │                                       │
    └────┬──────────────────────────────────┘
         │
         ▼  Rekonstrukcja — wystarczą dowolne 3 z 5
         │
    S1 + S2 + S3  →  "MojeHasloDoKeePass2024!"  ✓
    S2 + S4 + S5  →  "MojeHasloDoKeePass2024!"  ✓
    S1            →  brak informacji o sekrecie  ✗
```

### Właściwości kryptograficzne

Sekret jest zakodowany jako wyraz wolny wielomianu nad ciałem GF(2⁸). Każdy udział to punkt na tym wielomianie — znając wymaganą liczbę punktów można go jednoznacznie odtworzyć interpolacją Lagrange'a:

```
f(x) = a₀ + a₁x + a₂x² + ... + aₖ₋₁xᵏ⁻¹  (mod p)
```

> [!IMPORTANT]
> Posiadanie **mniejszej niż wymagana** liczby udziałów nie daje **żadnej** informacji o sekrecie (information-theoretic security). Dodany padding 1024 bitów uniemożliwia ataki na małe sekrety.

### Parametry implementacji

| Parametr | Wartość |
|---|---|
| Biblioteka | secrets.js (amper5and) — kompatybilna z iancoleman.io |
| Format udziałów | `8` + 2-hex x-coord + data |
| minPad | 1024 bitów |
| Kodowanie | UTF-8 (str2hex) |
| Rekonstrukcja | JavaScript po stronie przeglądarki — hasło nie trafia na serwer |

### Dobór parametrów podziału

| Łączna liczba udziałów | Wymagane minimum | Scenariusz |
|---|---|---|
| 3 | 2 | Mała rodzina |
| 5 | 3 | Standardowy *(zalecany)* |
| 7 | 4 | Większa rodzina / firma |

---

## Architektura systemu

Użytkownik przechodzi przez kolejne warstwy weryfikacji, zanim uzyska dostęp do chronionych zasobów:

```
Użytkownik   →  wpisuje login + hasło
                         │
System PHP   →  weryfikuje hasło (bcrypt), sprawdza rate limiting i CSRF
                         │
SMS / 2FA    →  wysyła jednorazowy kod na przypisany numer telefonu
                         │
Użytkownik   →  wpisuje kod SMS
                         │
System PHP   →  weryfikuje kod, tworzy sesję, opcjonalnie zapamiętuje urządzenie
                         │
Przeglądarka →  przyjmuje udziały Shamira, odtwarza sekret lokalnie w JS
```

### Warstwy systemu

**Warstwa autoryzacji** (`/app/`)
- `login.php` — formularz logowania
- `auth.php` — logika sesji, bcrypt, 2FA, brute-force, CSRF
- `verify.php` — weryfikacja kodu SMS
- `resend.php` — ponowna wysyłka SMS
- `logout.php` — wylogowanie

**Warstwa dostępu** (`/decrypt/`)
- `index.php` — panel odszyfrowania z rekonstrukcją Shamira w JS
- `log.php` — logowanie zdarzeń

**Warstwa danych** (`/private/` — poza `public_html`)
- `secret-key.php` — hasze bcrypt, zamaskowane numery telefonów
- `trusted_devices.json` — tokeny zaufanych urządzeń
- `secret-key.log` — logi zdarzeń

> [!WARNING]
> Wszystkie wrażliwe pliki konfiguracyjne przechowywane są **poza katalogiem publicznym** serwera — błędna konfiguracja webservera nie grozi ich ujawnieniem.

---

## Bezpieczeństwo

System łączy **sześć niezależnych warstw ochrony** — kompromitacja jednej nie daje dostępu do systemu.

| Warstwa | Mechanizm | Szczegóły |
|---|---|---|
| 🔒 **Hasła** | bcrypt | cost=10, format `$2y$`, weryfikacja odporna na ataki czasowe |
| 🛡️ **CSRF** | Token 64 hex | Generowany kryptograficznie, weryfikowany przy każdym żądaniu |
| 🚫 **Brute-force** | Rate limiting | 3 próby/IP + 3 próby/konto w oknie 15 min; 3 błędne kody SMS/h |
| 📱 **2FA SMS** | Kod 6-cyfrowy | Generowany kryptograficznie, ważny 10 min, cooldown 60s między wysyłkami |
| 💻 **Trusted devices** | SHA-256, HttpOnly | Secure + SameSite=Strict, plik poza `public_html`, TTL 7 dni |
| ⏱️ **Sesja** | Auto-logout | 30 min timeout, odnowienie identyfikatora sesji po każdej weryfikacji |

---

## Instalacja

### Wymagania

- PHP 8.0+
- Serwer WWW (Apache / Nginx)
- Konto w [SMSPlanet](https://smsplanet.pl) (wysyłka kodów 2FA)
- Dostęp do katalogu poza `public_html`

---

### Krok 1 — Konfiguracja (offline)

Otwórz `dashboard.html` lokalnie w przeglądarce. Zawiera dwie zakładki:

**Konfiguracja** — generuje plik `secret-key.php`:
1. Wprowadź loginy, hasła i numery telefonów dla każdej wyznaczonej osoby
2. Kliknij „Generuj konfigurację"
3. Pobierz wygenerowany plik `secret-key.php`

**Szyfrowanie** — dzieli hasło główne na udziały Shamira:
1. Wpisz hasło główne do bazy haseł
2. Ustaw łączną liczbę udziałów i wymagane minimum
3. Kliknij „Generuj udziały"
4. Pobierz plik `secret-key-shares.txt`

> [!TIP]
> Oba narzędzia działają **całkowicie offline** — żadne dane nie opuszczają przeglądarki.

---

### Krok 2 — Wgranie na serwer

```bash
/home/user/
├── public_html/
│   ├── app/           ← zawartość folderu /app/
│   └── decrypt/       ← zawartość folderu /decrypt/
└── private/           ← POZA public_html
    └── secret-key.php ← wygenerowany plik konfiguracyjny
```

---

### Krok 3 — Ścieżka do konfiguracji

W pliku `auth.php` zaktualizuj ścieżkę do pliku konfiguracyjnego:

```php
require_once '/home/user/private/secret-key.php';
```

---

### Krok 4 — Dystrybucja kart

Dla każdej wyznaczonej osoby przygotuj nośnik z:
- loginem i hasłem (z zakładki Konfiguracja)
- udziałem Shamira (z pliku `secret-key-shares.txt`)
- opcjonalnie: kodem QR z tym samym udziałem
- adresem Twojej instancji systemu

---

## Struktura plików

```
secret-key/
│
├── 📁 app/                    # Publiczny — system logowania
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
<summary><strong>Co się stanie jeśli zgubię kartę Secret Key?</strong></summary>

Sama karta jest bezużyteczna bez dostępu do telefonu przypisanego do danego konta — logowanie wymaga weryfikacji SMS. Ryzyko jest ograniczone, jednak właściciel powinien zostać poinformowany i rozważyć wygenerowanie nowej konfiguracji z nowym zestawem udziałów.

</details>

<details>
<summary><strong>Czy posiadając kartę mogę samodzielnie odczytać hasło?</strong></summary>

Nie. Jest to matematycznie niemożliwe. Pojedynczy udział nie ujawnia żadnej informacji o sekrecie — to właściwość algorytmu zwana information-theoretic security. Dopiero zebranie wymaganej liczby udziałów pozwala na odtworzenie hasła głównego.

</details>

<details>
<summary><strong>Co jeśli jedna z wyznaczonych osób umrze lub będzie niedostępna?</strong></summary>

System jest zaprojektowany z nadmiarowością — wystarczy zebrać minimalną wymaganą liczbę udziałów (np. 3 z 5). Niedostępność jednej lub dwóch osób nie blokuje procedury awaryjnej, o ile pozostałe mogą się zebrać.

</details>

<details>
<summary><strong>Czy hasło trafia na serwer podczas odszyfrowania?</strong></summary>

Nie. Rekonstrukcja hasła z udziałów Shamira odbywa się **całkowicie po stronie przeglądarki** (JavaScript). Serwer służy tylko do uwierzytelnienia użytkownika — sam sekret nigdy go nie opuszcza.

</details>

<details>
<summary><strong>Czy mogę używać systemu z innym menedżerem haseł niż KeePassXC?</strong></summary>

Tak. Secret Key przechowuje i odtwarza **dowolne hasło główne** — niezależnie od używanego menedżera. Kompatybilny z każdym programem obsługującym hasło główne: KeePassXC, Bitwarden, 1Password i innymi.

</details>

<details>
<summary><strong>Jak wybrać próg — ile udziałów jest wymaganych?</strong></summary>

Im wyższy próg, tym większe bezpieczeństwo — ale też trudniejsze zebranie się w sytuacji kryzysowej. Rekomendowany kompromis dla standardowego użycia to **3 z 5** — pozwala na niedostępność dwóch osób przy zachowaniu dobrego poziomu ochrony.

</details>

<details>
<summary><strong>Jak długo ważne są dane logowania z karty?</strong></summary>

Bezterminowo — o ile właściciel nie wygeneruje nowej konfiguracji i nie zastąpi pliku `secret-key.php` na serwerze. Po tej operacji stare karty przestają działać i konieczne jest rozdanie nowych wszystkim wyznaczonym osobom.

</details>

---

<div align="center">

Prawo autorskie © 2026 · [karpierz.me](https://karpierz.me)

</div>
