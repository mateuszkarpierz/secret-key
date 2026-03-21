# Polityka bezpieczeństwa

## Zgłaszanie luk bezpieczeństwa

Jeśli odkryłeś lukę bezpieczeństwa w projekcie Secret Key, prosimy o **odpowiedzialne zgłoszenie** — nie twórz publicznego Issue na GitHubie.

### Jak zgłosić

Wyślij szczegółowy opis na: **karpierz.me**

lub skorzystaj z [GitHub Security Advisories](https://github.com/karpatka122/secret-key/security/advisories/new).

### Co zawrzeć w zgłoszeniu

- Opis luki i potencjalnego wpływu
- Kroki do odtworzenia (proof of concept)
- Wersja systemu której dotyczy
- Propozycja naprawy (opcjonalnie)

### Czego oczekiwać

- Potwierdzenie otrzymania zgłoszenia w ciągu **48 godzin**
- Informacja o postępach w ciągu **7 dni**
- Publiczne podziękowanie po naprawieniu luki (jeśli sobie życzysz)

---

## Wspierane wersje

| Wersja | Status |
|--------|--------|
| v1.0.x | ✅ Wspierana |

---

## Zakres

### W zakresie zgłoszeń

- Obejście uwierzytelnienia (bcrypt, 2FA)
- Podatności CSRF mimo zastosowanych zabezpieczeń
- Możliwość odtworzenia sekretu Shamira bez wymaganej liczby udziałów
- Ujawnienie danych z katalogu `/private/`
- Podatności na ataki brute-force mimo rate limitingu
- XSS, SQL Injection (choć system nie używa SQL)

### Poza zakresem

- Ataki wymagające fizycznego dostępu do serwera
- Ataki socjotechniczne na posiadaczy kart Secret Key
- Problemy z konfiguracją serwera WWW po stronie użytkownika
- Raporty generowane automatycznie przez skanery bez PoC

---

## Dobre praktyki przy wdrożeniu

> ⚠️ Secret Key jest systemem **self-hosted** — bezpieczeństwo instalacji zależy w dużej mierze od konfiguracji Twojego serwera.

- Upewnij się że katalog `/private/` jest **poza** `public_html`
- Używaj HTTPS (SSL/TLS) na serwerze
- Regularnie aktualizuj PHP do najnowszej wersji 8.x
- Nie udostępniaj pliku `secret-key.php` publicznie
- Używaj silnych, unikalnych haseł dla każdego konta w systemie

---

Prawo autorskie © 2026 · [karpierz.me](https://karpierz.me)
