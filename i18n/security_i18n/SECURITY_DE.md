# 🔐 Sicherheitsrichtlinie

<p align="center">
  <a href="../../SECURITY.md">🇵🇱 Polski</a> •
  <a href="SECURITY_EN.md">🇬🇧 English</a> •
  <a href="SECURITY_ES.md">🇪🇸 Español</a> •
  <kbd>🇩🇪 Deutsch</kbd> •
  <a href="SECURITY_PT_BR.md">🇧🇷 Português (Brasil)</a> •
  <a href="SECURITY_FR.md">🇫🇷 Français</a> •
  <a href="SECURITY_ZH.md">🇨🇳 简体中文</a> •
  <a href="SECURITY_AR.md">🇸🇦 العربية</a> •
  <a href="SECURITY_HI.md">🇮🇳 हिन्दी</a> •
  <a href="SECURITY_JA.md">🇯🇵 日本語</a> •
  <a href="SECURITY_RU.md">🇷🇺 Русский</a> •
  <a href="SECURITY_UK.md">🇺🇦 Українська</a>
</p>

---

Normale Fehler, Tippfehler in der Dokumentation oder Verbesserungsvorschläge meldest du ganz normal als [Issue auf GitHub](https://github.com/mateuszkarpierz/secret-key/issues) — das ist der Standardweg, daran ist nichts Geheimes. Diese Seite behandelt ausschließlich **Sicherheitslücken** (siehe [Umfang der Meldungen](#-umfang-der-meldungen) unten) — diese bitte **nicht** öffentlich melden.

---

## 📬 Sicherheitslücken melden

> [!CAUTION]
> Melde Sicherheitslücken ausschließlich privat, an **dev@secretkey.website** — niemals als öffentliches GitHub-Issue. Die öffentliche Offenlegung eines Exploits vor Veröffentlichung eines Patches gefährdet alle, die das System im Einsatz haben.

### So meldest du

Sende eine ausführliche Beschreibung an: **dev@secretkey.website**

### Was die Meldung enthalten sollte

| | Element |
|---|---|
| 📝 | Beschreibung der Lücke und der möglichen Auswirkungen |
| 🔁 | Schritte zur Reproduktion (Proof of Concept) |
| 🏷️ | Betroffene Systemversion |
| 💡 | Vorschlag zur Behebung *(optional)* |

### Was du erwarten kannst

| Zeit | Antwort |
|---|---|
| **48 Std.** | Bestätigung des Eingangs |
| **7 Tage** | Information über den Fortschritt |
| Nach der Behebung | Öffentliche Danksagung *(falls gewünscht)* |

---

## 🏷️ Unterstützte Versionen

Unterstützt wird immer die **zuletzt veröffentlichte Version**. Meldungen zu älteren Versionen werden angenommen, es wird jedoch zunächst um ein Update auf das neueste Release gebeten — einige Lücken könnten bereits behoben sein.

Die aktuelle Version findest du auf der [Releases-Seite](https://github.com/mateuszkarpierz/secret-key/releases).

---

## 🎯 Umfang der Meldungen

### ✅ Im Umfang

- Umgehung der Authentifizierung (bcrypt, 2FA)
- CSRF-Schwachstellen trotz vorhandener Schutzmaßnahmen
- Möglichkeit, das Shamir-Geheimnis ohne die erforderliche Anzahl an Anteilen zu rekonstruieren
- Offenlegung von Daten aus dem Verzeichnis `/private/`
- Herunterladen sensibler Dateien (z. B. der Passwortdatenbank) unter Umgehung der Anmeldung, z. B. über eine direkte URL
- Anfälligkeit für Brute-Force-Angriffe trotz Rate Limiting
- XSS, SQL-Injection *(obwohl das System kein SQL verwendet)*

### ❌ Außerhalb des Umfangs

- Angriffe, die physischen Zugriff auf den Server erfordern
- Social-Engineering-Angriffe gegen Inhaber von Secret-Key-Karten
- Probleme mit der Webserver-Konfiguration auf Nutzerseite
- Automatisch von Scannern generierte Berichte ohne PoC

---

## 🛡️ Best Practices für die Bereitstellung

> [!WARNING]
> Secret Key ist ein **selbst gehostetes** System — die Sicherheit Ihrer Installation hängt maßgeblich von der Konfiguration Ihres eigenen Servers ab.

| | Praxis |
|---|---|
| 📁 | Verzeichnis `/private/` **außerhalb** von `public_html` halten |
| 🔒 | HTTPS (SSL/TLS) auf dem Server verwenden |
| 🔄 | PHP regelmäßig auf die neueste 8.x-Version aktualisieren |
| 🚫 | Die Datei `secret-key.php` niemals öffentlich zugänglich machen |
| 📥 | Nicht direkt auf sensible Dateien im öffentlichen Verzeichnis verlinken — über `download.php` ausliefern (erfordert eine Sitzung, protokolliert Downloads) |
| 🔑 | Starke, eindeutige Passwörter für jedes Konto verwenden |

---

<div align="center">

Copyright © 2026 · [karpierz.me](https://karpierz.me)

</div>
