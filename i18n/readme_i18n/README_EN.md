<div align="center">

<img src="../../img/secret-key-logo.svg" width="380" alt="Secret Key Logo">

</div>

---

<p align="center">
  <a href="../../README.md">🇵🇱 Polski</a> •
  <kbd>🇬🇧 English</kbd> •
  <a href="README_ES.md">🇪🇸 Español</a> •
  <a href="README_DE.md">🇩🇪 Deutsch</a> •
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

### A cryptographic emergency-access system for your password database

<br>

[![PHP](https://img.shields.io/badge/PHP-8%2B-7c3aed?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Zero SQL](https://img.shields.io/badge/Database-Zero_SQL-4ade80?style=flat-square)](#stack)
[![Self-hosted](https://img.shields.io/badge/Hosting-Self--hosted-c084fc?style=flat-square)](#installation)
[![2FA](https://img.shields.io/badge/2FA-SMS_·_SMSPlanet-38bdf8?style=flat-square)](#security)
[![Shamir](https://img.shields.io/badge/Cryptography-Shamir_SSS-f472b6?style=flat-square)](#shamirs-algorithm)
[![License](https://img.shields.io/badge/License-MIT-818cf8?style=flat-square)](../../LICENSE)

<br>

**What happens to your accounts after you die?**

Secret Key cryptographically splits the master password to your password database among trusted people.  
No one knows it on their own — only together, at an agreed moment, can they reconstruct it.

<br>

![Secret Key Demo](../../img/SecretKeyGif.gif)

<br>

[**→ See the demo**](https://app.secretkey.website) &nbsp;·&nbsp; [**Project website**](https://secretkey.website) &nbsp;·&nbsp; [**Documentation**](https://secretkey.website/docs)

</div>

---

## Table of contents

- 01 · 🔑 [Idea](#idea)
- 02 · ⚙️ [How it works](#how-it-works)
- 03 · 🎬 [System overview](#system-overview)
- 04 · 💳 [Secret Key card](#secret-key-card)
- 05 · 🔐 [Shamir's algorithm](#shamirs-algorithm)
- 06 · 🏗️ [System architecture](#system-architecture)
- 07 · 🛡️ [Security](#security)
- 08 · 🚀 [Installation](#installation)
- 09 · 📁 [File structure](#file-structure)
- 10 · ❓ [FAQ](#faq)

---

## Idea

Each of us stores dozens of passwords — for banks, email, social media, subscriptions. **What happens to them after we die?** The family gets cut off from accounts, unable to cancel subscriptions, close accounts, or recover money.

Secret Key solves this problem by creating a secure emergency plan with two guarantees:

| &nbsp; | Problem | Solution |
|---|---|---|
| 🔐 | Unauthorized access **during** the owner's lifetime | Every access attempt requires a password + an SMS code |
| 💀 | Loss of account access **after death** | Designated people reconstruct the password using Shamir's algorithm |

The system is **fully self-hosted** — data never leaves your server. No central database, no cloud, no external dependencies apart from sending SMS.

<a name="stack"></a>

```
Backend       PHP 8+, flat files (JSON + PHP config), zero SQL
Authorization bcrypt cost=10, 2FA SMS via SMSPlanet API
Cryptography  Shamir Secret Sharing (secrets.js)
Frontend      Plain HTML + JS, zero external dependencies (offline)
```

---

## How it works

In a crisis situation, the designated people carry out four steps:

```
┌──────────────────────────────────────────────────────────────────────┐
│                                                                      │
│  01  GATHER          Min. 3 of 5 designated people                   │
│      ─────────       Each has a card with a key fragment             │
│                                                                      │
│  02  LOG IN          Login credentials from the Secret Key card      │
│      ──────────      + an SMS code to the assigned phone number      │
│                                                                      │
│  03  SHARES          Each person enters their fragment or            │
│      ─────────       scans the QR code from the card                 │
│                                                                      │
│  04  ACCESS          Password reconstructed locally in the browser — │
│      ────────        never reaches the server                        │
│                                                                      │
└──────────────────────────────────────────────────────────────────────┘
```

**Without the phone assigned to an account — logging in is impossible**, even knowing the data from the card.  
**Without the required number of shares — reconstructing the password is mathematically impossible**, even with access to the server.

---

## System overview

<p align="center">
  <a href="https://www.youtube.com/watch?v=omx7mpQD5-M" target="_blank">
    <img src="../../img/video-player.webp" alt="Watch a video presenting the Secret Key system" width="780">
  </a>
</p>

> [!TIP]
> 🔊 This video is available in multiple languages — click the ⚙️ icon in the YouTube player → **Subtitles/Audio (Audio track)**, to pick the dubbing language or turn on captions.

---

## Secret Key card

Each designated person receives a personalized carrier with four elements:

| Element | Description |
|---|---|
| 🔑 **Login and password** | Unique access credentials — each person has their own account with an assigned phone number |
| 🔢 **Shamir share** | A key fragment in hex format — one of five, useless without the rest |
| ⊞ **QR code** | The same share encoded as a QR code — scanning eliminates manual-transcription errors |
| 🌐 **System address** | The URL of your own Secret Key instance — leads straight to the login panel |

**The carrier format is up to you** — a printed sheet, a PDF, a plastic card. What matters is that it contains the four elements above.

---

## Shamir's algorithm

Secret Key uses the [`secrets.js`](https://github.com/amper5and/secrets.js) library to split and reconstruct the secret.

> [!NOTE]
> The library used is **identical to the one used by [iancoleman.io/shamir](https://iancoleman.io/shamir/)** — the share format is fully compatible, which allows independent verification outside the system.

### Splitting and reconstructing the secret

```
Master password: "MyKeePassPassword2024!"
         │
         ▼  Split into 5 shares (threshold: 3)
         │
    ┌────┴──────────────────────────────────┐
    │                                       │
    │  S1: 801a3f9c2e4b7d1...  →  Person A  │  Each share
    │  S2: 802c8f1a5e9b3d7...  →  Person B  │  is useless
    │  S3: 803e2a7f4c1b9d5...  →  Person C  │  without the
    │  S4: 804b6d3e8f2a1c9...  →  Person D  │  required rest
    │  S5: 805d9f7b2e4c3a1...  →  Person E  │
    │                                       │
    └────┬──────────────────────────────────┘
         │
         ▼  Reconstruction — any 3 of 5 are enough
         │
    S1 + S2 + S3  →  "MyKeePassPassword2024!"  ✓
    S2 + S4 + S5  →  "MyKeePassPassword2024!"  ✓
    S1            →  no information about the secret  ✗
```

### Cryptographic properties

The secret is encoded as the free term of a polynomial over the field GF(2⁸). Each share is a point on that polynomial — knowing the required number of points, it can be uniquely reconstructed via Lagrange interpolation:

```
f(x) = a₀ + a₁x + a₂x² + ... + aₖ₋₁xᵏ⁻¹  (mod p)
```

> [!IMPORTANT]
> Holding **fewer than the required** number of shares gives **zero** information about the secret (information-theoretic security). The added 1024-bit padding prevents attacks on small secrets.

### Implementation parameters

| Parameter | Value |
|---|---|
| Library | secrets.js (amper5and) — compatible with iancoleman.io |
| Share format | `8` + 2-hex x-coord + data |
| minPad | 1024 bits |
| Encoding | UTF-8 (str2hex) |
| Reconstruction | JavaScript on the browser side — the password never reaches the server |

### Choosing the split parameters

| Total shares | Required minimum | Scenario |
|---|---|---|
| 3 | 2 | Small family |
| 5 | 3 | Standard *(recommended)* |
| 7 | 4 | Larger family / company |

---

## System architecture

The user passes through successive verification layers before gaining access to protected resources:

```
User         →  enters login + password
                         │
PHP system   →  verifies the password (bcrypt), checks rate limiting and CSRF
                         │
SMS / 2FA    →  sends a one-time code to the assigned phone number
                         │
User         →  enters the SMS code
                         │
PHP system   →  verifies the code, creates a session, optionally remembers the device
                         │
Browser      →  accepts the Shamir shares, reconstructs the secret locally in JS
```

### System layers

**Authorization layer** (`/app/`)
- `login.php` — the login form
- `auth.php` — session logic, bcrypt, 2FA, brute-force, CSRF, plus the helpers `t()` (UI texts), `md_lite()` (markdown-lite), `empty_state_box()`
- `verify.php` — SMS code verification
- `resend.php` — SMS resend
- `logout.php` — logout

**Access layer** (`/decrypt/`)
- `index.php` — the decryption panel with Shamir reconstruction in JS
- `download.php` — gated file downloads (requires a session, a whitelist built from the config, server-side logging)
- `log.php` — event logging
- `devtools-log.php` — logging DevTools-inspection incidents (with per-IP rate limiting)

**Data layer** (`/private/` — outside `public_html`)
- `secret-key.php` — a single config file: people (`$people`), downloadable files (`$downloads`), instructions (`$instructions`), email notification (`$email_notify`), SMS domain
- `lang.php` — interface texts (a single language, no switcher — see [Installation](#installation))
- `rate-limit.php` — persistent rate limiting (counters independent of the session)
- `rate_limits.json` — login-attempt counters per IP/account *(created automatically)*
- `trusted_devices.json` — trusted-device tokens *(created automatically)*
- `secret-key.log` — event logs
- `moja-baza-hasel.kdbx` *(and other downloadable files)* — served only through `download.php`, never directly over HTTP

> [!WARNING]
> All sensitive configuration files are stored **outside the public directory** of the server — a misconfigured web server does not risk exposing them.

---

## Security

The system combines **eight independent layers of protection** — compromising one does not grant access to the system.

| Layer | Mechanism | Details |
|---|---|---|
| 🔒 **Passwords** | bcrypt | cost=10, `$2y$` format, timing-attack-resistant verification |
| 🛡️ **CSRF** | 64-hex token | Cryptographically generated, verified on every state-changing endpoint (login, 2FA verification, SMS resend, event log) |
| 🚫 **Brute-force** | Rate limiting | 3 attempts/IP + 3 attempts/account in a 15-min window; 3 wrong SMS codes/hour. Persistent server-side counters (a file, independent of the client's session/cookies) |
| 📱 **SMS 2FA** | 6-digit code | Cryptographically generated, valid for 10 min, 60s cooldown between sends |
| 💻 **Trusted devices** | SHA-256, HttpOnly | Secure + SameSite=Strict, file outside `public_html`, 7-day TTL |
| ⏱️ **Session** | Auto-logout | Session cookie with explicit HttpOnly + Secure + SameSite=Strict flags; 30-min timeout, session ID regenerated after every verification |
| 🖥️ **Interface protection** | DevTools detection | Detects developer tools, physically removes the DOM, logs the incident with IP, REF#, and duration |
| 📥 **Gated downloads** | `download.php` + whitelist | Downloadable files live outside `public_html`; an active session is required, no direct URL, always logged server-side |

---

## Installation

### Requirements

- PHP 8.0+
- A web server (Apache / Nginx)
- An account with [SMSPlanet](https://smsplanet.pl) (for sending 2FA codes)
- Access to a directory outside `public_html`

---

### Step 1 — Configuration (offline)

Open `dashboard.html` locally in your browser. It has two tabs:

**Configuration** — generates the `secret-key.php` file:
1. The SMSPlanet API token, the SMS sender name, and the domain for autofilling the code (Android/iOS) — just the domain, without `@` and without `https://`
2. For each designated person: login, password, first name, last name, phone number, and whether they should be visible on the holder list in the panel
3. Downloadable files (password database, 2FA database, program installer) — key, button label, filename
4. The instruction steps for the panel (formatting: `**bold**` / `*italic*`)
5. Optional: an email notification for every login (recipient address, sender, panel link)
6. Click "Generate configuration" and download the generated `secret-key.php` file

**Encryption** — splits the master password into Shamir shares:
1. Enter the master password to the password database
2. Set the total number of shares and the required minimum
3. Click "Generate shares"
4. Download the `secret-key-shares.txt` file

> [!TIP]
> Both tools work **entirely offline** — no data leaves the browser. The form generates `secret-key.php` from scratch on every run — it doesn't load or edit an existing file.

---

### Step 2 — Uploading to the server

```bash
/home/user/
├── public_html/
│   ├── app/           ← contents of the /app/ folder
│   └── decrypt/       ← contents of the /decrypt/ folder
└── private/           ← OUTSIDE public_html
    ├── secret-key.php  ← the generated configuration file
    ├── lang.php        ← interface texts (a single language, see Step 1)
    ├── rate-limit.php  ← a system file from the repository (rate limiting)
    └── moja-baza-hasel.kdbx  ← your files (served via download.php)
```

> [!WARNING]
> `lang.php` must be uploaded to the server **together with** `secret-key.php`. `auth.php` loads it via `require_once` — a missing file crashes the whole system (a fatal error on every page), not just the translations.

---

### Step 3 — Path to the configuration

In the `auth.php` file, update the path to the configuration file:

```php
require_once '/home/user/private/secret-key.php';
```

---

### Step 4 — Domain in the SMS content (WebOTP)

The content of the sent SMS code ends with the line `@domain #code` — this is the format required by the [WebOTP API](https://developer.mozilla.org/en-US/docs/Web/API/WebOTP_API), thanks to which the browser on the phone fills in the code field on its own, without manually retyping it from the SMS.

You set the domain in **Step 1**, in the dashboard form (the "SMS autofill domain" field) — you enter just the domain, without `@` and without `https://` (the dashboard adds the `@` automatically). It goes into the config as:

```php
define('SMS_AUTOFILL_DOMAIN', '@your-domain.com');
```

> [!WARNING]
> The domain must **exactly** match the one you host the system under — otherwise WebOTP will ignore the SMS and autofill won't work. The code will still arrive and work when entered manually, just without that convenience.

---

### Step 5 — Distributing the cards

For each designated person, prepare a carrier with:
- a login and password (from the Configuration tab)
- a Shamir share (from the `secret-key-shares.txt` file)
- optionally: a QR code with the same share
- the address of your system instance

---

## File structure

```
secret-key/
│
├── 📁 app/                        # Public — login system
│   ├── 📁 decrypt/                # Protected — user panel
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
├── 📁 dashboard/                  # Configuration tools (offline)
│   ├── card-back.js
│   ├── card-front.js
│   ├── dashboard.html
│   ├── favicon.ico
│   ├── generate-card.html
│   ├── generate-hash.html
│   └── generate-shamir.html
│
├── 📁 img/                        # Graphic assets
│
└── 📁 private/                    # Outside public_html — configuration files
    ├── .htaccess
    ├── demo-baza-hasel.txt         # Example downloadable file (replace with your own)
    ├── lang.php                    # Interface texts (a single language)
    ├── rate-limit.php
    └── secret-key.php
```

---

## FAQ

<details>
<summary><strong>What happens if I lose my Secret Key card?</strong></summary>

The card by itself is useless without access to the phone assigned to that account — logging in requires SMS verification. The risk is limited, but the owner should be informed and consider generating a new configuration with a new set of shares.

</details>

<details>
<summary><strong>Can I read the password on my own with just one card?</strong></summary>

No. It's mathematically impossible. A single share reveals no information about the secret — this is a property of the algorithm called information-theoretic security. Only gathering the required number of shares allows the master password to be reconstructed.

</details>

<details>
<summary><strong>What if one of the designated people dies or becomes unavailable?</strong></summary>

The system is designed with redundancy — it's enough to gather the minimum required number of shares (e.g. 3 of 5). One or two people being unavailable doesn't block the emergency procedure, as long as the rest can get together.

</details>

<details>
<summary><strong>Does the password reach the server during decryption?</strong></summary>

No. Reconstructing the password from the Shamir shares happens **entirely on the browser side** (JavaScript). The server is only used to authenticate the user — the secret itself never leaves it.

</details>

<details>
<summary><strong>Can I use the system with a password manager other than KeePassXC?</strong></summary>

Yes. Secret Key stores and reconstructs **any master password** — regardless of the manager used. Compatible with any program that supports a master password: KeePassXC, Bitwarden, 1Password, and others.

</details>

<details>
<summary><strong>How do I choose the threshold — how many shares are required?</strong></summary>

The higher the threshold, the greater the security — but also the harder it is to gather everyone in a crisis situation. The recommended compromise for standard use is **3 of 5** — it tolerates two people being unavailable while keeping a good level of protection.

</details>

<details>
<summary><strong>How long are the login credentials from a card valid?</strong></summary>

Indefinitely — as long as the owner doesn't generate a new configuration and replace the `secret-key.php` file on the server. After that operation, the old cards stop working, and new ones need to be distributed to all the designated people.

</details>

---

<div align="center">

Copyright © 2026 · [karpierz.me](https://karpierz.me)

</div>
