<div align="center">

<img src="../../img/secret-key-logo.svg" width="380" alt="Secret Key Logo">

</div>

---

<p align="center">
  <a href="../../README.md">🇵🇱 Polski</a> •
  <a href="README_EN.md">🇬🇧 English</a> •
  <a href="README_ES.md">🇪🇸 Español</a> •
  <kbd>🇩🇪 Deutsch</kbd> •
  <a href="README_PT_BR.md">🇧🇷 Português (Brasil)</a> •
  <a href="README_FR.md">🇫🇷 Français</a> •
  <a href="README_ZH.md">🇨🇳 简体中文</a> •
  <a href="README_AR.md">🇸🇦 العربية</a> •
  <a href="README_HI.md">🇮🇳 हिन्दी</a> •
  <a href="README_JA.md">🇯🇵 日本語</a> •
  <a href="README_RU.md">🇷🇺 Русский</a> •
  <a href="README_UK.md">🇺🇦 Українська</a>
</p>

---

<div align="center">

### Ein kryptografisches Notfallzugriffssystem für Ihre Passwortdatenbank

<br>

[![PHP](https://img.shields.io/badge/PHP-8%2B-7c3aed?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Zero SQL](https://img.shields.io/badge/Datenbank-Zero_SQL-4ade80?style=flat-square)](#stack)
[![Self-hosted](https://img.shields.io/badge/Hosting-Self--hosted-c084fc?style=flat-square)](#installation)
[![2FA](https://img.shields.io/badge/2FA-SMS_·_SMSPlanet-38bdf8?style=flat-square)](#sicherheit)
[![Shamir](https://img.shields.io/badge/Kryptografie-Shamir_SSS-f472b6?style=flat-square)](#shamir-algorithmus)
[![License](https://img.shields.io/badge/Lizenz-MIT-818cf8?style=flat-square)](../../LICENSE)

<br>

**Was passiert mit Ihren Konten nach Ihrem Tod?**

Secret Key teilt das Master-Passwort Ihrer Passwortdatenbank kryptografisch unter vertrauenswürdigen Personen auf.  
Niemand kennt es allein — nur gemeinsam, zu einem vereinbarten Zeitpunkt, können sie es rekonstruieren.

<br>

![Secret Key Demo](../../img/SecretKeyGif.gif)

<br>

[**→ Demo ansehen**](https://app.secretkey.website) &nbsp;·&nbsp; [**Projektwebsite**](https://secretkey.website) &nbsp;·&nbsp; [**Dokumentation**](https://secretkey.website/docs)

</div>

---

## Inhaltsverzeichnis

- 01 · 🔑 [Idee](#idee)
- 02 · ⚙️ [So funktioniert es](#so-funktioniert-es)
- 03 · 🎬 [Systemübersicht](#systemübersicht)
- 04 · 💳 [Secret Key-Karte](#secret-key-karte)
- 05 · 🔐 [Shamir-Algorithmus](#shamir-algorithmus)
- 06 · 🏗️ [Systemarchitektur](#systemarchitektur)
- 07 · 🛡️ [Sicherheit](#sicherheit)
- 08 · 🚀 [Installation](#installation)
- 09 · 📁 [Dateistruktur](#dateistruktur)
- 10 · ❓ [FAQ](#faq)

---

## Idee

Jeder von uns speichert Dutzende von Passwörtern — für Banken, E-Mail, soziale Medien, Abonnements. **Was passiert damit nach unserem Tod?** Die Familie wird von den Konten abgeschnitten, kann Abonnements nicht kündigen, Konten nicht schließen, kein Geld zurückholen.

Secret Key löst dieses Problem, indem es einen sicheren Notfallplan mit zwei Garantien schafft:

| &nbsp; | Problem | Lösung |
|---|---|---|
| 🔐 | Unbefugter Zugriff **zu Lebzeiten** des Besitzers | Jeder Zugriffsversuch erfordert ein Passwort + einen SMS-Code |
| 💀 | Verlust des Kontozugriffs **nach dem Tod** | Beauftragte Personen rekonstruieren das Passwort mit dem Shamir-Algorithmus |

Das System ist **vollständig selbst gehostet** — Daten verlassen niemals Ihren Server. Keine zentrale Datenbank, keine Cloud, keine externen Abhängigkeiten außer dem SMS-Versand.

<a name="stack"></a>

```
Backend       PHP 8+, flache Dateien (JSON + PHP-Konfig), kein SQL
Autorisierung bcrypt cost=10, 2FA per SMS über die SMSPlanet-API
Kryptografie  Shamir Secret Sharing (secrets.js)
Frontend      Reines HTML + JS, keine externen Abhängigkeiten (offline)
```

---

## So funktioniert es

In einer Krisensituation führen die beauftragten Personen vier Schritte aus:

```
┌──────────────────────────────────────────────────────────────────────┐
│                                                                      │
│  01  VERSAMMELN      Min. 3 von 5 beauftragten Personen               │
│      ─────────       Jede besitzt eine Karte mit einem Schlüsselteil │
│                                                                      │
│  02  ANMELDEN        Anmeldedaten von der Secret-Key-Karte            │
│      ──────────      + ein SMS-Code an die hinterlegte Rufnummer     │
│                                                                      │
│  03  ANTEILE         Jede Person gibt ihren Anteil ein oder           │
│      ─────────       scannt den QR-Code von der Karte                │
│                                                                      │
│  04  ZUGRIFF         Passwort wird lokal im Browser rekonstruiert —   │
│      ────────        erreicht nie den Server                         │
│                                                                      │
└──────────────────────────────────────────────────────────────────────┘
```

**Ohne das der Karte zugeordnete Telefon ist eine Anmeldung unmöglich**, selbst mit Kenntnis der Kartendaten.  
**Ohne die erforderliche Anzahl an Anteilen ist die Rekonstruktion des Passworts mathematisch unmöglich**, selbst mit Zugriff auf den Server.

---

## Systemübersicht

<p align="center">
  <a href="https://www.youtube.com/watch?v=omx7mpQD5-M" target="_blank">
    <img src="../../img/video-player.webp" alt="Video zur Vorstellung des Secret-Key-Systems ansehen" width="780">
  </a>
</p>

> [!TIP]
> 🔊 Das Video ist in mehreren Sprachen verfügbar — klicken Sie im YouTube-Player auf ⚙️ → **Untertitel/Audio (Audiospur)**, um die Synchronisationssprache zu wählen oder Untertitel zu aktivieren.

---

## Secret Key-Karte

Jede beauftragte Person erhält einen personalisierten Träger mit vier Elementen:

| Element | Beschreibung |
|---|---|
| 🔑 **Login und Passwort** | Eindeutige Zugangsdaten — jede Person hat ein eigenes Konto mit hinterlegter Rufnummer |
| 🔢 **Shamir-Anteil** | Ein Schlüsselfragment im Hex-Format — eines von fünf, nutzlos ohne die übrigen |
| ⊞ **QR-Code** | Derselbe Anteil als QR-Code codiert — das Scannen vermeidet Fehler beim manuellen Abtippen |
| 🌐 **Systemadresse** | Die URL Ihrer eigenen Secret-Key-Instanz — führt direkt zum Anmeldebereich |

**Das Format des Trägers ist frei wählbar** — ein ausgedrucktes Blatt, ein PDF, eine Plastikkarte. Wichtig ist, dass er die vier oben genannten Elemente enthält.

---

## Shamir-Algorithmus

Secret Key verwendet die Bibliothek [`secrets.js`](https://github.com/amper5and/secrets.js), um das Geheimnis aufzuteilen und zu rekonstruieren.

> [!NOTE]
> Die verwendete Bibliothek ist **identisch mit der von [iancoleman.io/shamir](https://iancoleman.io/shamir/) verwendeten** — das Anteilsformat ist vollständig kompatibel, was eine unabhängige Überprüfung außerhalb des Systems ermöglicht.

### Aufteilung und Rekonstruktion des Geheimnisses

```
Master-Passwort: "MyKeePassPassword2024!"
         │
         ▼  Aufteilung in 5 Anteile (Schwellenwert: 3)
         │
    ┌────┴──────────────────────────────────┐
    │                                       │
    │  S1: 801a3f9c2e4b7d1...  →  Person A  │  Jeder Anteil
    │  S2: 802c8f1a5e9b3d7...  →  Person B  │  ist ohne die
    │  S3: 803e2a7f4c1b9d5...  →  Person C  │  übrigen
    │  S4: 804b6d3e8f2a1c9...  →  Person D  │  nutzlos
    │  S5: 805d9f7b2e4c3a1...  →  Person E  │
    │                                       │
    └────┬──────────────────────────────────┘
         │
         ▼  Rekonstruktion — beliebige 3 von 5 genügen
         │
    S1 + S2 + S3  →  "MyKeePassPassword2024!"  ✓
    S2 + S4 + S5  →  "MyKeePassPassword2024!"  ✓
    S1            →  keine Information über das Geheimnis  ✗
```

### Kryptografische Eigenschaften

Das Geheimnis wird als konstanter Term eines Polynoms über dem Körper GF(2⁸) codiert. Jeder Anteil ist ein Punkt auf diesem Polynom — bei Kenntnis der erforderlichen Anzahl an Punkten kann es durch Lagrange-Interpolation eindeutig rekonstruiert werden:

```
f(x) = a₀ + a₁x + a₂x² + ... + aₖ₋₁xᵏ⁻¹  (mod p)
```

> [!IMPORTANT]
> Der Besitz von **weniger als der erforderlichen** Anzahl an Anteilen liefert **keinerlei** Information über das Geheimnis (information-theoretic security). Das zusätzliche 1024-Bit-Padding verhindert Angriffe auf kleine Geheimnisse.

### Implementierungsparameter

| Parameter | Wert |
|---|---|
| Bibliothek | secrets.js (amper5and) — kompatibel mit iancoleman.io |
| Anteilsformat | `8` + 2-Hex-x-Koordinate + Daten |
| minPad | 1024 Bit |
| Kodierung | UTF-8 (str2hex) |
| Rekonstruktion | JavaScript auf der Browserseite — das Passwort erreicht nie den Server |

### Wahl der Aufteilungsparameter

| Gesamtzahl der Anteile | Erforderliches Minimum | Szenario |
|---|---|---|
| 3 | 2 | Kleine Familie |
| 5 | 3 | Standard *(empfohlen)* |
| 7 | 4 | Größere Familie / Unternehmen |

---

## Systemarchitektur

Der Benutzer durchläuft nacheinander mehrere Verifizierungsebenen, bevor er Zugriff auf geschützte Ressourcen erhält:

```
Benutzer     →  gibt Login + Passwort ein
                         │
PHP-System   →  überprüft das Passwort (bcrypt), prüft Rate Limiting und CSRF
                         │
SMS / 2FA    →  sendet einen Einmalcode an die hinterlegte Rufnummer
                         │
Benutzer     →  gibt den SMS-Code ein
                         │
PHP-System   →  überprüft den Code, erstellt eine Sitzung, merkt sich optional das Gerät
                         │
Browser      →  nimmt die Shamir-Anteile entgegen, rekonstruiert das Geheimnis lokal in JS
```

### Systemebenen

**Autorisierungsebene** (`/app/`)
- `login.php` — das Anmeldeformular
- `auth.php` — Sitzungslogik, bcrypt, 2FA, Brute-Force-Schutz, CSRF, sowie die Hilfsfunktionen `t()` (UI-Texte), `md_lite()` (Markdown-Lite), `empty_state_box()`
- `verify.php` — Überprüfung des SMS-Codes
- `resend.php` — erneuter SMS-Versand
- `logout.php` — Abmeldung

**Zugriffsebene** (`/decrypt/`)
- `index.php` — das Entschlüsselungspanel mit Shamir-Rekonstruktion in JS
- `download.php` — geschützte Dateidownloads (erfordert eine Sitzung, eine aus der Konfiguration erstellte Whitelist, serverseitige Protokollierung)
- `log.php` — Ereignisprotokollierung
- `devtools-log.php` — Protokollierung von DevTools-Inspektionsversuchen (mit IP-basiertem Rate Limiting)

**Datenebene** (`/private/` — außerhalb von `public_html`)
- `secret-key.php` — eine einzige Konfigurationsdatei: Personen (`$people`), herunterladbare Dateien (`$downloads`), Anleitung (`$instructions`), E-Mail-Benachrichtigung (`$email_notify`), SMS-Domain
- `lang.php` — Oberflächentexte (eine einzige Sprache, kein Umschalter — siehe [Installation](#installation))
- `rate-limit.php` — dauerhaftes Rate Limiting (Zähler unabhängig von der Sitzung)
- `rate_limits.json` — Anmeldeversuch-Zähler pro IP/Konto *(wird automatisch erstellt)*
- `trusted_devices.json` — Tokens für vertrauenswürdige Geräte *(wird automatisch erstellt)*
- `secret-key.log` — Ereignisprotokolle
- `moja-baza-hasel.kdbx` *(und weitere herunterladbare Dateien)* — werden ausschließlich über `download.php` ausgeliefert, nie direkt über HTTP

> [!WARNING]
> Alle sensiblen Konfigurationsdateien werden **außerhalb des öffentlichen Verzeichnisses** des Servers gespeichert — eine fehlerhafte Webserver-Konfiguration birgt kein Risiko, sie offenzulegen.

---

## Sicherheit

Das System kombiniert **acht unabhängige Schutzebenen** — die Kompromittierung einer Ebene gewährt keinen Zugriff auf das System.

| Ebene | Mechanismus | Details |
|---|---|---|
| 🔒 **Passwörter** | bcrypt | cost=10, Format `$2y$`, timing-attack-resistente Überprüfung |
| 🛡️ **CSRF** | 64-Hex-Token | Kryptografisch generiert, überprüft bei jedem zustandsändernden Endpunkt (Anmeldung, 2FA-Verifizierung, erneuter SMS-Versand, Ereignisprotokoll) |
| 🚫 **Brute-Force** | Rate Limiting | 3 Versuche/IP + 3 Versuche/Konto in einem 15-Minuten-Fenster; 3 falsche SMS-Codes/Stunde. Dauerhafte serverseitige Zähler (eine Datei, unabhängig von der Sitzung/den Cookies des Clients) |
| 📱 **2FA per SMS** | 6-stelliger Code | Kryptografisch generiert, 10 Minuten gültig, 60 Sekunden Abkühlzeit zwischen den Versendungen |
| 💻 **Vertrauenswürdige Geräte** | SHA-256, HttpOnly | Secure + SameSite=Strict, Datei außerhalb von `public_html`, TTL 7 Tage |
| ⏱️ **Sitzung** | Auto-Logout | Sitzungscookie mit expliziten HttpOnly- + Secure- + SameSite=Strict-Flags; 30-Minuten-Timeout, Erneuerung der Sitzungs-ID nach jeder Verifizierung |
| 🖥️ **Oberflächenschutz** | DevTools-Erkennung | Erkennt Entwicklertools, entfernt das DOM physisch, protokolliert den Vorfall mit IP, REF# und Dauer |
| 📥 **Geschützte Downloads** | `download.php` + Whitelist | Herunterladbare Dateien liegen außerhalb von `public_html`; eine aktive Sitzung ist erforderlich, keine direkte URL, immer serverseitig protokolliert |

---

## Installation

### Voraussetzungen

- PHP 8.0+
- Ein Webserver (Apache / Nginx)
- Ein Konto bei [SMSPlanet](https://smsplanet.pl) (zum Versenden von 2FA-Codes)
- Zugriff auf ein Verzeichnis außerhalb von `public_html`

---

### Schritt 1 — Konfiguration (offline)

Öffnen Sie `dashboard.html` lokal in Ihrem Browser. Es enthält zwei Registerkarten:

**Konfiguration** — generiert die Datei `secret-key.php`:
1. Das SMSPlanet-API-Token, den SMS-Absendernamen und die Domain für das automatische Ausfüllen des Codes (Android/iOS) — nur die Domain, ohne `@` und ohne `https://`
2. Für jede beauftragte Person: Login, Passwort, Vorname, Nachname, Telefonnummer und ob sie in der Inhaberliste im Panel sichtbar sein soll
3. Herunterladbare Dateien (Passwortdatenbank, 2FA-Datenbank, Programm-Installer) — Schlüssel, Button-Beschriftung, Dateiname
4. Die Anleitungsschritte für das Panel (Formatierung: `**fett**` / `*kursiv*`)
5. Optional: eine E-Mail-Benachrichtigung bei jeder Anmeldung (Empfängeradresse, Absender, Link zum Panel)
6. Klicken Sie auf „Konfiguration generieren" und laden Sie die generierte Datei `secret-key.php` herunter

**Verschlüsselung** — teilt das Master-Passwort in Shamir-Anteile auf:
1. Geben Sie das Master-Passwort zur Passwortdatenbank ein
2. Legen Sie die Gesamtzahl der Anteile und das erforderliche Minimum fest
3. Klicken Sie auf „Anteile generieren"
4. Laden Sie die Datei `secret-key-shares.txt` herunter

> [!TIP]
> Beide Werkzeuge arbeiten **vollständig offline** — es verlassen keine Daten den Browser. Das Formular generiert `secret-key.php` bei jedem Durchlauf von Grund auf neu — es lädt oder bearbeitet keine bestehende Datei.

---

### Schritt 2 — Hochladen auf den Server

```bash
/home/user/
├── public_html/
│   ├── app/           ← Inhalt des Ordners /app/
│   └── decrypt/       ← Inhalt des Ordners /decrypt/
└── private/           ← AUSSERHALB von public_html
    ├── secret-key.php  ← die generierte Konfigurationsdatei
    ├── lang.php        ← Oberflächentexte (eine Sprache, siehe Schritt 1)
    ├── rate-limit.php  ← eine Systemdatei aus dem Repository (Rate Limiting)
    └── moja-baza-hasel.kdbx  ← Ihre Dateien (ausgeliefert über download.php)
```

> [!WARNING]
> `lang.php` muss **zusammen mit** `secret-key.php` auf den Server hochgeladen werden. `auth.php` lädt sie über `require_once` — eine fehlende Datei bringt das gesamte System zum Absturz (ein fataler Fehler auf jeder Seite), nicht nur die Übersetzungen.

---

### Schritt 3 — Pfad zur Konfiguration

Aktualisieren Sie in der Datei `auth.php` den Pfad zur Konfigurationsdatei:

```php
require_once '/home/user/private/secret-key.php';
```

---

### Schritt 4 — Domain im SMS-Inhalt (WebOTP)

Der Inhalt des versendeten SMS-Codes endet mit der Zeile `@domain #code` — dies ist das von der [WebOTP-API](https://developer.mozilla.org/en-US/docs/Web/API/WebOTP_API) geforderte Format, dank dem der Browser auf dem Telefon das Codefeld selbstständig ausfüllt, ohne manuelles Abtippen aus der SMS.

Sie legen die Domain in **Schritt 1** fest, im Dashboard-Formular (Feld „Domain für SMS-Autofill") — Sie geben nur die Domain ein, ohne `@` und ohne `https://` (das Dashboard fügt das `@` automatisch hinzu). Sie gelangt in die Konfiguration als:

```php
define('SMS_AUTOFILL_DOMAIN', '@ihre-domain.de');
```

> [!WARNING]
> Die Domain muss **exakt** mit der übereinstimmen, unter der Sie das System hosten — andernfalls ignoriert WebOTP die SMS und das Autofill funktioniert nicht. Der Code kommt trotzdem an und funktioniert bei manueller Eingabe, nur ohne diesen Komfort.

---

### Schritt 5 — Verteilung der Karten

Bereiten Sie für jede beauftragte Person einen Träger vor mit:
- Login und Passwort (aus der Registerkarte Konfiguration)
- einem Shamir-Anteil (aus der Datei `secret-key-shares.txt`)
- optional: einem QR-Code mit demselben Anteil
- der Adresse Ihrer Systeminstanz

---

## Dateistruktur

```
secret-key/
│
├── 📁 app/                        # Öffentlich — Anmeldesystem
│   ├── 📁 decrypt/                # Geschützt — Benutzerpanel
│   │   ├── .htaccess
│   │   ├── card-secret-key.webp
│   │   ├── devtools-log.php
│   │   ├── download.php
│   │   ├── favicon.ico
│   │   ├── index.php
│   │   ├── key.svg
│   │   └── log.php
│   ├── .htaccess
│   ├── auth.php
│   ├── favicon.ico
│   ├── key.svg
│   ├── login.php
│   ├── logout.php
│   ├── resend.php
│   └── verify.php
│
├── 📁 dashboard/                  # Konfigurationswerkzeuge (offline)
│   ├── card-back.js
│   ├── card-front.js
│   ├── dashboard.html
│   ├── favicon.ico
│   ├── generate-card.html
│   ├── generate-hash.html
│   └── generate-shamir.html
│
├── 📁 img/                        # Grafikressourcen
│
└── 📁 private/                    # Außerhalb von public_html — Konfigurationsdateien
    ├── .htaccess
    ├── demo-baza-hasel.txt         # Beispiel für eine herunterladbare Datei (durch eigene ersetzen)
    ├── lang.php                    # Oberflächentexte (eine Sprache)
    ├── rate-limit.php
    └── secret-key.php
```

---

## FAQ

<details>
<summary><strong>Was passiert, wenn ich meine Secret-Key-Karte verliere?</strong></summary>

Die Karte allein ist nutzlos ohne Zugriff auf das dem Konto zugeordnete Telefon — die Anmeldung erfordert eine SMS-Verifizierung. Das Risiko ist begrenzt, dennoch sollte der Besitzer informiert werden und erwägen, eine neue Konfiguration mit einem neuen Satz von Anteilen zu generieren.

</details>

<details>
<summary><strong>Kann ich das Passwort mit nur einer Karte allein auslesen?</strong></summary>

Nein. Das ist mathematisch unmöglich. Ein einzelner Anteil offenbart keinerlei Information über das Geheimnis — dies ist eine Eigenschaft des Algorithmus namens information-theoretic security. Erst das Sammeln der erforderlichen Anzahl an Anteilen ermöglicht die Rekonstruktion des Master-Passworts.

</details>

<details>
<summary><strong>Was, wenn eine der beauftragten Personen stirbt oder nicht verfügbar ist?</strong></summary>

Das System ist mit Redundanz konzipiert — es genügt, die minimal erforderliche Anzahl an Anteilen zu sammeln (z. B. 3 von 5). Die Nichtverfügbarkeit einer oder zweier Personen blockiert das Notfallverfahren nicht, solange sich die übrigen versammeln können.

</details>

<details>
<summary><strong>Erreicht das Passwort während der Entschlüsselung den Server?</strong></summary>

Nein. Die Rekonstruktion des Passworts aus den Shamir-Anteilen erfolgt **vollständig auf der Browserseite** (JavaScript). Der Server dient nur zur Authentifizierung des Benutzers — das Geheimnis selbst verlässt ihn niemals.

</details>

<details>
<summary><strong>Kann ich das System mit einem anderen Passwort-Manager als KeePassXC verwenden?</strong></summary>

Ja. Secret Key speichert und rekonstruiert **jedes beliebige Master-Passwort** — unabhängig vom verwendeten Manager. Kompatibel mit jedem Programm, das ein Master-Passwort unterstützt: KeePassXC, Bitwarden, 1Password und anderen.

</details>

<details>
<summary><strong>Wie wähle ich den Schwellenwert — wie viele Anteile sind erforderlich?</strong></summary>

Je höher der Schwellenwert, desto größer die Sicherheit — aber auch desto schwieriger die Versammlung in einer Krisensituation. Der empfohlene Kompromiss für den Standardgebrauch ist **3 von 5** — er toleriert die Nichtverfügbarkeit zweier Personen bei gleichzeitig gutem Schutzniveau.

</details>

<details>
<summary><strong>Wie lange sind die Anmeldedaten einer Karte gültig?</strong></summary>

Unbegrenzt — solange der Besitzer keine neue Konfiguration generiert und die Datei `secret-key.php` auf dem Server ersetzt. Nach diesem Vorgang funktionieren die alten Karten nicht mehr, und es müssen neue an alle beauftragten Personen verteilt werden.

</details>

---

<div align="center">

Copyright © 2026 · [karpierz.me](https://karpierz.me)

</div>
