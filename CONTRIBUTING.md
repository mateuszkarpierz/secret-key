<p align="center">
  <kbd>🇵🇱 Polski</kbd> •
  <a href="contributing_i18n/CONTRIBUTING_EN.md">🇬🇧 English</a>
</p>

---

# 🤝 Jak współtworzyć

Dzięki, że chcesz pomóc przy Secret Key! Ten dokument opisuje, jak najskuteczniej to zrobić.

---

## 🔐 Luki bezpieczeństwa

> [!CAUTION]
> Luki bezpieczeństwa zgłaszaj **wyłącznie prywatnie**, zgodnie z [Polityką bezpieczeństwa](SECURITY.md) — nigdy jako publiczne Issue czy Pull Request.

---

## 💡 Sposoby na pomoc

Nie każda pomoc musi oznaczać pisanie kodu. Mile widziane są:

- 🐛 **Zgłoszenia błędów** przez [Issues](https://github.com/mateuszkarpierz/secret-key/issues) — im dokładniejszy opis i kroki do odtworzenia, tym szybciej naprawa.
- 💬 **Pytania i dyskusje** w [Discussions](https://github.com/mateuszkarpierz/secret-key/discussions) — zamiast Issue, jeśli to nie jest konkretny błąd.
- 🌍 **Poprawki tłumaczeń** — README i SECURITY.md mają wersje w 11 językach (`readme_i18n/`, `security_i18n/`). Literówka albo niezręczne sformułowanie w tłumaczeniu? Zgłoś lub popraw bezpośrednio.
- 🛠️ **Pull requesty** z poprawkami błędów lub nowymi funkcjami — patrz sekcja niżej.

---

## 🧭 Zanim zaczniesz

Przy większych zmianach (nowa funkcja, zmiana architektury) najpierw otwórz Issue albo wątek w Discussions, żeby omówić pomysł, zanim włożysz czas w implementację — oszczędza to obu stronom rozczarowania, gdyby podejście miało nie pasować do kierunku projektu.

Przy drobnych poprawkach (literówka, mały bugfix, poprawka tłumaczenia) możesz od razu wysłać Pull Request bez wcześniejszej dyskusji.

---

## ⚙️ Środowisko developerskie

Projekt celowo **nie ma** żadnego procesu budowania, menedżera pakietów ani frameworka — to świadoma decyzja projektowa (patrz niżej), więc setup jest prosty:

- **Backend** (`/app/`) — potrzebujesz tylko PHP 8+ i serwera WWW (Apache/Nginx) lub wbudowanego serwera PHP (`php -S localhost:8000`).
- **Narzędzia offline** (`dashboard.html` i inne) — otwierasz bezpośrednio w przeglądarce, bez żadnego serwera.
- **Tryb testowy SMS** — `app/auth.php` ma wbudowany tryb testowy (kod zawsze `123456`), opisany w [dokumentacji instalacji](https://secretkey.website/docs) — używaj go do lokalnych testów, żeby nie zużywać tokenów SMSPlanet.

---

## 🎨 Konwencje kodu

> [!IMPORTANT]
> **Zero zewnętrznych zależności to filozofia projektu, nie przypadek.** Backend to czyste PHP (bez Composera), frontend to czysty HTML/JS (bez npm, buildów, frameworków). Jeśli Twój PR dodaje zależność, wyjaśnij w opisie *dlaczego* jest konieczna — domyślnym założeniem jest, że da się to zrobić bez niej.

Kilka konwencji już obecnych w kodzie, których warto się trzymać:

- **Teksty interfejsu** przechodzą przez funkcję `t()` i klucze w `private/lang.php` — nie wpisuj tekstu na sztywno w plikach `.php`.
- **Formatowanie tekstu użytkownika** (np. w `$instructions`) korzysta z prostego `md_lite()` (`**pogrubienie**` / `*kursywa*`) — nie z pełnego Markdown ani surowego HTML.
- **Liczniki rate-limitingu** żyją w pliku po stronie serwera (`rate-limit.php`), nigdy w `$_SESSION` — sesja jest zbyt łatwa do zresetowania przez klienta.
- **Wrażliwe pliki** zawsze poza `public_html` (`/private/`) — jeśli dodajesz nowy plik z danymi wrażliwymi, on też tam trafia.

---

## ✅ Przed wysłaniem PR-a

- [ ] Przetestowałem/am zmianę lokalnie (w trybie testowym SMS, jeśli dotyczy logowania/2FA)
- [ ] Nie dodałem/am nowych zależności bez uzasadnienia w opisie PR-a
- [ ] Jeśli zmieniłem/am zachowanie opisane w dokumentacji (secretkey.website/docs), zaznaczyłem/am to w opisie PR-a — dokumentacja żyje na osobnej stronie, poza tym repozytorium
- [ ] Commit messages są zwięzłe i opisują *co* i *po co* się zmieniło

---

## 📜 Licencja

Wysyłając Pull Request, zgadzasz się, że Twój wkład zostanie objęty licencją [MIT](LICENSE), tak jak reszta projektu.

---

<div align="center">

Dzięki za pomoc! 🔑

</div>
