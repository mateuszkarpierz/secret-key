# 🔐 Polityka bezpieczeństwa

<p align="center">
  <kbd>🇵🇱 Polski</kbd> •
  <a href="i18n/security_i18n/SECURITY_EN.md">🇬🇧 English</a> •
  <a href="i18n/security_i18n/SECURITY_ES.md">🇪🇸 Español</a> •
  <a href="i18n/security_i18n/SECURITY_DE.md">🇩🇪 Deutsch</a> •
  <a href="i18n/security_i18n/SECURITY_PT_BR.md">🇧🇷 Português (Brasil)</a> •
  <a href="i18n/security_i18n/SECURITY_FR.md">🇫🇷 Français</a> •
  <a href="i18n/security_i18n/SECURITY_ZH.md">🇨🇳 简体中文</a> •
  <a href="i18n/security_i18n/SECURITY_AR.md">🇸🇦 العربية</a> •
  <a href="i18n/security_i18n/SECURITY_HI.md">🇮🇳 हिन्दी</a> •
  <a href="i18n/security_i18n/SECURITY_JA.md">🇯🇵 日本語</a> •
  <a href="i18n/security_i18n/SECURITY_RU.md">🇷🇺 Русский</a> •
  <a href="i18n/security_i18n/SECURITY_UK.md">🇺🇦 Українська</a>
</p>

---

Zwykłe błędy, literówki w dokumentacji czy propozycje ulepszeń zgłaszaj normalnie jako [Issue na GitHubie](https://github.com/mateuszkarpierz/secret-key/issues) — to standardowa droga i nie ma w tym nic tajnego. Poniższa strona dotyczy wyłącznie **luk bezpieczeństwa** (patrz [zakres zgłoszeń](#-zakres-zgłoszeń) niżej) — tych proszę **nie** zgłaszać publicznie.

---

## 📬 Zgłaszanie luk bezpieczeństwa

> [!CAUTION]
> Luki bezpieczeństwa zgłaszaj wyłącznie prywatnie, na **dev@secretkey.website** — nigdy jako publiczne Issue na GitHubie. Publiczne zgłoszenie exploita przed wydaniem łatki naraża wszystkich, którzy mają wdrożony system.

### Jak zgłosić

Wyślij szczegółowy opis na: **dev@secretkey.website**

### Co zawrzeć w zgłoszeniu

| | Element |
|---|---|
| 📝 | Opis luki i potencjalnego wpływu |
| 🔁 | Kroki do odtworzenia (proof of concept) |
| 🏷️ | Wersja systemu, której dotyczy |
| 💡 | Propozycja naprawy *(opcjonalnie)* |

### Czego oczekiwać

| Czas | Odpowiedź |
|---|---|
| **48h** | Potwierdzenie otrzymania zgłoszenia |
| **7 dni** | Informacja o postępach |
| Po naprawie | Publiczne podziękowanie *(jeśli sobie życzysz)* |

---

## 🏷️ Wspierane wersje

Wspierana jest zawsze **najnowsza wydana wersja**. Zgłoszenia dotyczące starszych wersji przyjmuję, ale w pierwszej kolejności proszę o aktualizację do najnowszego release'a — część luk mogła zostać już naprawiona.

Aktualną wersję znajdziesz na [stronie Releases](https://github.com/mateuszkarpierz/secret-key/releases).

---

## 🎯 Zakres zgłoszeń

### ✅ W zakresie

- Obejście uwierzytelnienia (bcrypt, 2FA)
- Podatności CSRF mimo zastosowanych zabezpieczeń
- Możliwość odtworzenia sekretu Shamira bez wymaganej liczby udziałów
- Ujawnienie danych z katalogu `/private/`
- Pobranie wrażliwych plików (np. bazy haseł) z pominięciem logowania, np. przez bezpośredni URL
- Podatności na ataki brute-force mimo rate limitingu
- XSS, SQL Injection *(choć system nie używa SQL)*

### ❌ Poza zakresem

- Ataki wymagające fizycznego dostępu do serwera
- Ataki socjotechniczne na posiadaczy kart Secret Key
- Problemy z konfiguracją serwera WWW po stronie użytkownika
- Raporty generowane automatycznie przez skanery bez PoC

---

## 🛡️ Dobre praktyki przy wdrożeniu

> [!WARNING]
> Secret Key jest systemem **self-hosted** — bezpieczeństwo instalacji zależy w dużej mierze od konfiguracji Twojego serwera.

| | Praktyka |
|---|---|
| 📁 | Katalog `/private/` trzymaj **poza** `public_html` |
| 🔒 | Używaj HTTPS (SSL/TLS) na serwerze |
| 🔄 | Regularnie aktualizuj PHP do najnowszej wersji 8.x |
| 🚫 | Nie udostępniaj pliku `secret-key.php` publicznie |
| 📥 | Nie linkuj bezpośrednio do wrażliwych plików w katalogu publicznym — serwuj je przez `download.php` (wymaga sesji, loguje pobrania) |
| 🔑 | Używaj silnych, unikalnych haseł dla każdego konta |

---

<div align="center">

Prawo autorskie © 2026 · [karpierz.me](https://karpierz.me)

</div>
