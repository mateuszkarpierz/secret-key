<p align="center">
  <a href="../CONTRIBUTING.md">🇵🇱 Polski</a> •
  <kbd>🇬🇧 English</kbd>
</p>

---

# 🤝 How to contribute

Thanks for wanting to help with Secret Key! This document describes the best way to do that.

---

## 🔐 Security vulnerabilities

> [!CAUTION]
> Report security vulnerabilities **privately only**, following the [Security policy](../SECURITY.md) — never as a public Issue or Pull Request.

---

## 💡 Ways to contribute

Not every contribution has to mean writing code. All of these are welcome:

- 🐛 **Bug reports** via [Issues](https://github.com/mateuszkarpierz/secret-key/issues) — the more precise the description and reproduction steps, the faster the fix.
- 💬 **Questions and discussions** in [Discussions](https://github.com/mateuszkarpierz/secret-key/discussions) — instead of an Issue, if it's not a concrete bug.
- 🌍 **Translation fixes** — README and SECURITY.md have versions in 11 languages (`readme_i18n/`, `security_i18n/`). Found a typo or awkward phrasing in a translation? Report it or fix it directly.
- 🛠️ **Pull requests** with bug fixes or new features — see the section below.

---

## 🧭 Before you start

For larger changes (a new feature, an architecture change), open an Issue or a Discussions thread first to talk through the idea before investing time in an implementation — this saves both sides the disappointment of an approach not fitting the project's direction.

For small fixes (a typo, a small bugfix, a translation correction), you can send a Pull Request directly without a prior discussion.

---

## ⚙️ Development setup

The project deliberately has **no** build process, package manager, or framework — that's a conscious design decision (see below), so the setup is simple:

- **Backend** (`/app/`) — you only need PHP 8+ and a web server (Apache/Nginx) or PHP's built-in server (`php -S localhost:8000`).
- **Offline tools** (`dashboard.html` and others) — open them directly in the browser, no server needed.
- **SMS test mode** — `app/auth.php` has a built-in test mode (code is always `123456`), described in the [installation docs](https://secretkey.website/docs) — use it for local testing so you don't burn through SMSPlanet tokens.

---

## 🎨 Code conventions

> [!IMPORTANT]
> **Zero external dependencies is the project's philosophy, not an accident.** The backend is plain PHP (no Composer), the frontend is plain HTML/JS (no npm, no build step, no frameworks). If your PR adds a dependency, explain in the description *why* it's necessary — the default assumption is that it can be done without one.

A few conventions already present in the code that are worth following:

- **Interface texts** go through the `t()` function and keys in `private/lang.php` — don't hardcode text directly in `.php` files.
- **User-facing text formatting** (e.g. in `$instructions`) uses the simple `md_lite()` (`**bold**` / `*italic*`) — not full Markdown or raw HTML.
- **Rate-limiting counters** live in a server-side file (`rate-limit.php`), never in `$_SESSION` — the session is too easy for the client to reset.
- **Sensitive files** always live outside `public_html` (`/private/`) — if you add a new file with sensitive data, it belongs there too.

---

## ✅ Before submitting a PR

- [ ] I tested the change locally (in SMS test mode, if it touches login/2FA)
- [ ] I didn't add new dependencies without justifying them in the PR description
- [ ] If I changed behavior described in the docs (secretkey.website/docs), I flagged it in the PR description — the documentation lives on a separate site, outside this repository
- [ ] Commit messages are concise and describe *what* changed and *why*

---

## 📜 License

By submitting a Pull Request, you agree that your contribution will be licensed under the [MIT License](../LICENSE), same as the rest of the project.

---

<div align="center">

Thanks for helping! 🔑

</div>
