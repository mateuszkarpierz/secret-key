<div align="center">

<img src="../../img/secret-key-logo.svg" width="380" alt="Secret Key Logo">

</div>

---

<p align="center">
  <a href="../../README.md">🇵🇱 Polski</a> •
  <a href="README_EN.md">🇬🇧 English</a> •
  <a href="README_ES.md">🇪🇸 Español</a> •
  <a href="README_DE.md">🇩🇪 Deutsch</a> •
  <a href="README_PT_BR.md">🇧🇷 Português (Brasil)</a> •
  <kbd>🇫🇷 Français</kbd> •
  <a href="README_ZH.md">🇨🇳 简体中文</a> •
  <a href="README_AR.md">🇸🇦 العربية</a> •
  <a href="README_HI.md">🇮🇳 हिन्दी</a> •
  <a href="README_JA.md">🇯🇵 日本語</a> •
  <a href="README_RU.md">🇷🇺 Русский</a> •
  <a href="README_UK.md">🇺🇦 Українська</a>
</p>

---

<div align="center">

### Un système cryptographique d'accès d'urgence à votre base de mots de passe

<br>

[![PHP](https://img.shields.io/badge/PHP-8%2B-7c3aed?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Zero SQL](https://img.shields.io/badge/Base_de_donn%C3%A9es-Zero_SQL-4ade80?style=flat-square)](#stack)
[![Self-hosted](https://img.shields.io/badge/H%C3%A9bergement-Self--hosted-c084fc?style=flat-square)](#installation)
[![2FA](https://img.shields.io/badge/2FA-SMS_·_SMSPlanet-38bdf8?style=flat-square)](#s%C3%A9curit%C3%A9)
[![Shamir](https://img.shields.io/badge/Cryptographie-Shamir_SSS-f472b6?style=flat-square)](#algorithme-de-shamir)
[![License](https://img.shields.io/badge/Licence-MIT-818cf8?style=flat-square)](../../LICENSE)

<br>

**Qu'arrive-t-il à vos comptes après votre décès ?**

Secret Key répartit cryptographiquement le mot de passe principal de votre base de mots de passe entre des personnes de confiance.  
Personne ne le connaît seul — ce n'est qu'ensemble, à un moment convenu, qu'elles peuvent le reconstituer.

<br>

![Secret Key Demo](../../img/SecretKeyGif.gif)

<br>

[**→ Voir la démo**](https://app.secretkey.website) &nbsp;·&nbsp; [**Site du projet**](https://secretkey.website) &nbsp;·&nbsp; [**Documentation**](https://secretkey.website/docs)

</div>

---

## Sommaire

- 01 · 🔑 [Idée](#idée)
- 02 · ⚙️ [Comment ça marche](#comment-ça-marche)
- 03 · 🎬 [Présentation du système](#présentation-du-système)
- 04 · 💳 [Carte Secret Key](#carte-secret-key)
- 05 · 🔐 [Algorithme de Shamir](#algorithme-de-shamir)
- 06 · 🏗️ [Architecture du système](#architecture-du-système)
- 07 · 🛡️ [Sécurité](#sécurité)
- 08 · 🚀 [Installation](#installation)
- 09 · 📁 [Structure des fichiers](#structure-des-fichiers)
- 10 · ❓ [FAQ](#faq)

---

## Idée

Chacun de nous stocke des dizaines de mots de passe — pour les banques, les e-mails, les réseaux sociaux, les abonnements. **Qu'advient-il d'eux après notre décès ?** La famille se retrouve coupée des comptes, incapable de résilier des abonnements, de fermer des comptes, de récupérer de l'argent.

Secret Key résout ce problème en créant un plan d'urgence sécurisé avec deux garanties :

| &nbsp; | Problème | Solution |
|---|---|---|
| 🔐 | Accès non autorisé **du vivant** du propriétaire | Chaque tentative d'accès nécessite un mot de passe + un code SMS |
| 💀 | Perte d'accès aux comptes **après le décès** | Les personnes désignées reconstituent le mot de passe avec l'algorithme de Shamir |

Le système est **entièrement autohébergé** — les données ne quittent jamais votre serveur. Aucune base de données centrale, aucun cloud, aucune dépendance externe hormis l'envoi des SMS.

<a name="stack"></a>

```
Backend         PHP 8+, fichiers plats (JSON + config PHP), zéro SQL
Autorisation    bcrypt cost=10, 2FA par SMS via l'API SMSPlanet
Cryptographie   Shamir Secret Sharing (secrets.js)
Frontend        HTML + JS pur, zéro dépendance externe (hors ligne)
```

---

## Comment ça marche

En situation de crise, les personnes désignées effectuent quatre étapes :

```
┌──────────────────────────────────────────────────────────────────────┐
│                                                                      │
│  01  RASSEMBLEMENT   Min. 3 des 5 personnes désignées                │
│      ─────────       Chacune possède une carte avec un fragment      │
│                                                                      │
│  02  CONNEXION       Identifiants de la carte Secret Key             │
│      ──────────      + un code SMS envoyé au numéro assigné          │
│                                                                      │
│  03  FRAGMENTS       Chaque personne saisit son fragment ou          │
│      ─────────       scanne le code QR de la carte                   │
│                                                                      │
│  04  ACCÈS           Mot de passe reconstitué localement dans le     │
│      ────────        navigateur — n'atteint jamais le serveur        │
│                                                                      │
└──────────────────────────────────────────────────────────────────────┘
```

**Sans le téléphone associé au compte — la connexion est impossible**, même en connaissant les données de la carte.  
**Sans le nombre requis de fragments — la reconstitution du mot de passe est mathématiquement impossible**, même avec un accès au serveur.

---

## Présentation du système

<p align="center">
  <a href="https://www.youtube.com/watch?v=omx7mpQD5-M" target="_blank">
    <img src="../../img/video-player.webp" alt="Regarder une vidéo de présentation du système Secret Key" width="780">
  </a>
</p>

> [!TIP]
> 🔊 La vidéo est disponible en plusieurs langues — cliquez sur ⚙️ dans le lecteur YouTube → **Sous-titres/Audio (piste audio)**, pour choisir la langue du doublage ou activer les sous-titres.

---

## Carte Secret Key

Chaque personne désignée reçoit un support personnalisé avec quatre éléments :

| Élément | Description |
|---|---|
| 🔑 **Identifiant et mot de passe** | Identifiants d'accès uniques — chaque personne a son propre compte avec un numéro de téléphone assigné |
| 🔢 **Fragment Shamir** | Un fragment de clé au format hexadécimal — un sur cinq, inutile sans les autres |
| ⊞ **Code QR** | Le même fragment encodé en QR — le scan élimine les erreurs de recopie manuelle |
| 🌐 **Adresse du système** | L'URL de votre propre instance Secret Key — mène directement au panneau de connexion |

**Le format du support est libre** — une feuille imprimée, un PDF, une carte plastique. L'important est qu'il contienne les quatre éléments ci-dessus.

---

## Algorithme de Shamir

Secret Key utilise la bibliothèque [`secrets.js`](https://github.com/grempe/secrets.js) (fork de [amper5and/secrets.js](https://github.com/amper5and/secrets.js), intégrée via [iancoleman.io/shamir](https://iancoleman.io/shamir/)) pour diviser et reconstituer le secret.

> [!NOTE]
> La bibliothèque utilisée est **identique à celle employée par [iancoleman.io/shamir](https://iancoleman.io/shamir/)** — le format des fragments est entièrement compatible, ce qui permet une vérification indépendante en dehors du système.

### Division et reconstitution du secret

```
Mot de passe principal : "MyKeePassPassword2024!"
         │
         ▼  Division en 5 fragments (seuil : 3)
         │
    ┌────┴──────────────────────────────────┐
    │                                       │
    │  S1: 801a3f9c2e4b7d1...  →  Personne A│  Chaque fragment
    │  S2: 802c8f1a5e9b3d7...  →  Personne B│  est inutile
    │  S3: 803e2a7f4c1b9d5...  →  Personne C│  sans le nombre
    │  S4: 804b6d3e8f2a1c9...  →  Personne D│  requis des autres
    │  S5: 805d9f7b2e4c3a1...  →  Personne E│
    │                                       │
    └────┬──────────────────────────────────┘
         │
         ▼  Reconstitution — 3 quelconques sur 5 suffisent
         │
    S1 + S2 + S3  →  "MyKeePassPassword2024!"  ✓
    S2 + S4 + S5  →  "MyKeePassPassword2024!"  ✓
    S1            →  aucune information sur le secret  ✗
```

### Propriétés cryptographiques

Le secret est encodé comme le terme constant d'un polynôme sur le corps GF(2⁸). Chaque fragment est un point de ce polynôme — en connaissant le nombre requis de points, il peut être reconstitué de manière unique par interpolation de Lagrange :

```
f(x) = a₀ + a₁x + a₂x² + ... + aₖ₋₁xᵏ⁻¹  (mod p)
```

> [!IMPORTANT]
> Posséder **moins que le nombre requis** de fragments ne donne **aucune** information sur le secret (information-theoretic security). Le padding supplémentaire de 1024 bits empêche les attaques sur les petits secrets.

### Paramètres d'implémentation

| Paramètre | Valeur |
|---|---|
| Bibliothèque | secrets.js (fork grempe, via iancoleman.io/shamir) |
| Format des fragments | `8` + coordonnée x sur 2 hex + données |
| minPad | 1024 bits |
| Encodage | UTF-8 (str2hex) |
| Reconstitution | JavaScript côté navigateur — le mot de passe n'atteint jamais le serveur |

### Choix des paramètres de division

| Nombre total de fragments | Minimum requis | Scénario |
|---|---|---|
| 3 | 2 | Petite famille |
| 5 | 3 | Standard *(recommandé)* |
| 7 | 4 | Famille élargie / entreprise |

---

## Architecture du système

L'utilisateur traverse successivement plusieurs couches de vérification avant d'accéder aux ressources protégées :

```
Utilisateur  →  saisit l'identifiant + le mot de passe
                         │
Système PHP  →  vérifie le mot de passe (bcrypt), contrôle le rate limiting et le CSRF
                         │
SMS / 2FA    →  envoie un code à usage unique au numéro assigné
                         │
Utilisateur  →  saisit le code SMS
                         │
Système PHP  →  vérifie le code, crée une session, mémorise éventuellement l'appareil
                         │
Navigateur   →  reçoit les fragments Shamir, reconstitue le secret localement en JS
```

### Couches du système

**Couche d'autorisation** (`/app/`)
- `login.php` — le formulaire de connexion
- `auth.php` — logique de session, bcrypt, 2FA, protection brute-force, CSRF, ainsi que les fonctions utilitaires `t()` (textes d'interface), `md_lite()` (markdown-lite), `empty_state_box()`
- `verify.php` — vérification du code SMS
- `resend.php` — renvoi du SMS
- `logout.php` — déconnexion

**Couche d'accès** (`/decrypt/`)
- `index.php` — le panneau de déchiffrement avec reconstitution Shamir en JS ; vérifie le résultat avec deux méthodes indépendantes (cohérence mathématique entre sous-ensembles de parts + analyse du format du texte) plutôt que de se fier à la simple absence d'exception
- `download.php` — téléchargements de fichiers contrôlés (session requise, liste blanche construite à partir de la configuration, journalisation côté serveur) + validation stricte du timelock indépendante du panneau
- `log.php` — journalisation des événements
- `devtools-log.php` — journalisation des tentatives d'inspection DevTools (avec limitation de débit par IP)
- `timelock.php` — logique du timelock après collusion des tiers de confiance (voir [Sécurité](#sécurité))
- `arm-timelock.php` — active le blocage de 48h après une reconstitution de mot de passe confirmée, envoie l'e-mail d'alerte
- `panic.php` — traite le lien à usage unique « Panic Button » de l'e-mail
- `tl-status.php` — interrogation en direct de l'état du blocage (pas besoin de recharger le panneau)

**Couche de données** (`/private/` — en dehors de `public_html`)
- `secret-key.php` — un fichier de configuration unique : personnes (`$people`), fichiers téléchargeables (`$downloads`), instructions (`$instructions`), notification e-mail (`$email_notify`), domaine SMS
- `lang.php` — textes d'interface (une seule langue, pas de sélecteur — voir [Installation](#installation))
- `rate-limit.php` — limitation de débit persistante (compteurs indépendants de la session)
- `rate_limits.json` — compteurs de tentatives de connexion par IP/compte *(créé automatiquement)*
- `trusted_devices.json` — jetons des appareils de confiance *(créé automatiquement)*
- `timelock.json` — état du blocage de 48h après collusion des tiers de confiance *(créé automatiquement, voir [Sécurité](#sécurité))*
- `secret-key.log` — journaux d'événements
- `moja-baza-hasel.kdbx` *(et autres fichiers téléchargeables)* — servis uniquement via `download.php`, jamais directement via HTTP

> [!WARNING]
> Tous les fichiers de configuration sensibles sont stockés **en dehors du répertoire public** du serveur — une configuration incorrecte du serveur web ne risque pas de les exposer.

---

## Sécurité

Le système combine **neuf couches de protection indépendantes** — la compromission de l'une ne donne pas accès au système.

| Couche | Mécanisme | Détails |
|---|---|---|
| 🔒 **Mots de passe** | bcrypt | cost=10, format `$2y$`, vérification résistante aux attaques temporelles |
| 🛡️ **CSRF** | Jeton 64 hex | Généré cryptographiquement, vérifié sur chaque point de terminaison modifiant l'état (connexion, vérification 2FA, renvoi de SMS, déconnexion, journal d'événements) |
| 🚫 **Brute-force** | Rate limiting | 3 tentatives/IP + 3 tentatives/compte sur une fenêtre de 15 min ; 3 codes SMS erronés/heure. Compteurs persistants côté serveur (un fichier, indépendant de la session/des cookies du client) |
| 📱 **2FA par SMS** | Code à 6 chiffres | Généré cryptographiquement, valable 10 min, délai de 60 s entre les envois |
| 💻 **Appareils de confiance** | SHA-256, HttpOnly | Secure + SameSite=Strict, fichier en dehors de `public_html`, TTL 7 jours |
| ⏱️ **Session** | Déconnexion automatique | Cookie de session avec drapeaux explicites HttpOnly + Secure + SameSite=Strict ; délai de 30 min, régénération de l'identifiant de session après chaque vérification |
| 🖥️ **Protection de l'interface** | Détection DevTools | Détecte les outils de développement, supprime physiquement le DOM, enregistre l'incident avec IP, REF# et durée |
| 📥 **Téléchargements contrôlés** | `download.php` + liste blanche | Les fichiers téléchargeables se trouvent en dehors de `public_html` ; une session active est requise, aucune URL directe, toujours journalisé côté serveur |

> [!TIP]
| 🚨 **Protection contre la collusion des tiers de confiance** | Timelock 48h + Panic Button + vérification côté serveur | La première reconstitution réussie du mot de passe (optionnellement vérifiée côté serveur via `SECRET_HASH`) bloque les téléchargements de fichiers pendant 48h et envoie un e-mail d'alerte avec un lien à usage unique pour bloquer l'accès immédiatement et définitivement — voir ci-dessous |

> **Protection contre la collusion des tiers de confiance de votre vivant.** L'algorithme de Shamir permet mathématiquement aux personnes désignées de reconstituer le mot de passe principal si elles s'entendent pour le faire — c'est une propriété inévitable de tout schéma de partage de secret à seuil, pas seulement de celui-ci. Le système réagit à cela sur trois niveaux : (1) dans le navigateur, il **vérifie la cohérence** des parts combinées (cohérence mathématique entre sous-ensembles + analyse du format du résultat) — cela filtre les fautes de frappe et le bruit aléatoire, mais ne **confirme pas** à lui seul que le secret combiné est le vrai mot de passe (une personne connectée pourrait coller des parts totalement cohérentes, mais inventées, avec un « mot de passe » complètement différent) ; (2) si vous définissez le `SECRET_HASH` optionnel, mais fortement recommandé, dans la configuration (un hash bcrypt du vrai mot de passe, calculé automatiquement par le tableau de bord), le serveur **vérifie réellement** le secret combiné via `password_verify()` avant d'armer quoi que ce soit ; (3) ce n'est qu'après cette vérification qu'il **bloque les téléchargements de fichiers pendant 48 heures**, en vous envoyant un e-mail avec un lien à usage unique « Panic Button » — un clic coupe définitivement l'accès avant que quiconque puisse télécharger quoi que ce soit. Connaître seul le mot de passe ne sert à rien sans le fichier physique de la base de données. Pour une protection maximale, il est en outre recommandé d'utiliser une clé de sécurité physique (par ex. YubiKey/FIDO2) comme second facteur de la base de mots de passe elle-même.

---

## Installation

### Prérequis

- PHP 8.0+
- Un serveur web (Apache / Nginx)
- Un compte [SMSPlanet](https://smsplanet.pl) (pour l'envoi des codes 2FA)
- Un accès à un répertoire en dehors de `public_html`

---

### Étape 1 — Configuration (hors ligne)

Ouvrez `dashboard.html` localement dans votre navigateur. Il comporte trois onglets :

**Configuration** — génère le fichier `secret-key.php` :
1. Le jeton API SMSPlanet, le nom de l'expéditeur SMS et le domaine pour le remplissage automatique du code (Android/iOS) — le domaine seul, sans `@` ni `https://`
2. Le seuil (K) de codes requis dans le panneau de récupération — doit correspondre au seuil défini dans l'onglet Chiffrement — et, optionnellement mais recommandé, le vrai mot de passe principal (le même que dans l'onglet Chiffrement), à partir duquel le tableau de bord calcule un hash pour la vérification côté serveur (voir [Protection contre la collusion](#sécurité))
3. Pour chaque personne désignée : identifiant, mot de passe, prénom, nom, numéro de téléphone et si elle doit être visible sur la liste des détenteurs dans le panneau
4. Fichiers téléchargeables (base de mots de passe, base 2FA, installateur du programme) — clé, libellé du bouton, nom de fichier
5. Les étapes d'instructions pour le panneau (mise en forme : `**gras**` / `*italique*`)
6. Optionnel : une notification e-mail à chaque connexion (adresse du destinataire, expéditeur, lien vers le panneau)
7. Cliquez sur « Générer la configuration » et téléchargez le fichier `secret-key.php` généré

**Chiffrement** — divise le mot de passe principal en fragments Shamir :
1. Saisissez le mot de passe principal de la base de mots de passe
2. Définissez le nombre total de fragments et le minimum requis
3. Cliquez sur « Générer les fragments »
4. Téléchargez le fichier `secret-key-shares.txt`

**Générateur de cartes** — crée des cartes Secret Key personnalisées, prêtes à imprimer (format ISO ID-1, 85,6×54mm) :
1. Paramètres globaux : l'URL de l'instance et le nom complet du propriétaire
2. Paramètres de partage (nombre total de fragments et minimum requis) — cohérents avec les onglets Configuration et Chiffrement
3. Un message modifiable au dos de la carte, avec des jetons pour insérer automatiquement le nombre de fragments, le seuil et l'URL de l'instance
4. Pour chaque personne : identifiant, mot de passe et fragment Shamir (hex) — données des deux onglets précédents
5. Optionnel : fond perdu de 2 mm et traits de coupe pour une impression en imprimerie
6. Cliquez sur « Générer les cartes » — aperçu du recto et du verso de chaque carte, prête à télécharger au format PDF

> [!TIP]
> Les trois outils fonctionnent **entièrement hors ligne** — aucune donnée ne quitte le navigateur. Le formulaire de configuration génère `secret-key.php` à partir de zéro à chaque exécution — il ne charge ni ne modifie un fichier existant.

---

### Étape 2 — Téléversement sur le serveur

```bash
/home/user/
├── public_html/
│   ├── app/           ← contenu du dossier /app/
│   └── decrypt/       ← contenu du dossier /decrypt/
└── private/           ← EN DEHORS de public_html
    ├── secret-key.php  ← le fichier de configuration généré
    ├── lang.php        ← textes d'interface (une langue, voir l'étape 1)
    ├── rate-limit.php  ← un fichier système du dépôt (rate limiting)
    └── moja-baza-hasel.kdbx  ← vos fichiers (servis via download.php)
```

> [!WARNING]
> `lang.php` doit être téléversé sur le serveur **en même temps que** `secret-key.php`. `auth.php` le charge via `require_once` — un fichier manquant fait planter tout le système (erreur fatale sur chaque page), pas seulement les traductions.

---

### Étape 3 — Chemin vers la configuration

Dans le fichier `auth.php`, mettez à jour le chemin vers le fichier de configuration :

```php
require_once '/home/user/private/secret-key.php';
```

---

### Étape 4 — Domaine dans le contenu du SMS (WebOTP)

Le contenu du code SMS envoyé se termine par la ligne `@domaine #code` — c'est le format requis par la [WebOTP API](https://developer.mozilla.org/en-US/docs/Web/API/WebOTP_API), grâce à laquelle le navigateur du téléphone remplit lui-même le champ du code, sans recopie manuelle depuis le SMS.

Vous définissez le domaine à l'**étape 1**, dans le formulaire du dashboard (champ « Domaine pour l'autofill SMS ») — vous saisissez uniquement le domaine, sans `@` ni `https://` (le dashboard ajoute le `@` automatiquement). Il est intégré à la configuration ainsi :

```php
define('SMS_AUTOFILL_DOMAIN', '@votre-domaine.fr');
```

> [!WARNING]
> Le domaine doit **correspondre exactement** à celui sous lequel vous hébergez le système — sinon WebOTP ignorera le SMS et l'autofill ne fonctionnera pas. Le code arrivera et fonctionnera quand même en saisie manuelle, simplement sans ce confort.

---

### Étape 5 — Distribution des cartes

Pour chaque personne désignée, préparez un support avec :
- un identifiant et un mot de passe (depuis l'onglet Configuration)
- un fragment Shamir (depuis le fichier `secret-key-shares.txt`)
- optionnellement : un code QR avec le même fragment
- l'adresse de votre instance système

---

## Structure des fichiers

```
secret-key/
│
├── 📁 app/                        # Public — système de connexion
│   ├── 📁 decrypt/                # Protégé — panneau utilisateur
│   │   ├── .htaccess
│   │   ├── arm-timelock.php
│   │   ├── card-secret-key.webp
│   │   ├── devtools-log.php
│   │   ├── download.php
│   │   ├── favicon.ico
│   │   ├── index.php
│   │   ├── key.svg
│   │   ├── log.php
│   │   ├── panic.php
│   │   ├── timelock.php
│   │   └── tl-status.php
│   ├── .htaccess
│   ├── auth.php
│   ├── favicon.ico
│   ├── key.svg
│   ├── login.php
│   ├── logout.php
│   ├── resend.php
│   └── verify.php
│
├── 📁 dashboard/                  # Outils de configuration (hors ligne)
│   ├── card-back.js
│   ├── card-front.js
│   ├── dashboard.html
│   ├── favicon.ico
│   ├── generate-card.html
│   ├── generate-hash.html
│   └── generate-shamir.html
│
├── 📁 img/                        # Ressources graphiques
│
└── 📁 private/                    # En dehors de public_html — fichiers de configuration
    ├── .htaccess
    ├── demo-baza-hasel.txt         # Exemple de fichier téléchargeable (à remplacer par le vôtre)
    ├── lang.php                    # Textes d'interface (une langue)
    ├── rate-limit.php
    └── secret-key.php
```

---

## FAQ

<details>
<summary><strong>Que se passe-t-il si je perds ma carte Secret Key ?</strong></summary>

La carte seule est inutile sans accès au téléphone associé à ce compte — la connexion nécessite une vérification par SMS. Le risque est limité, mais le propriétaire devrait être informé et envisager de générer une nouvelle configuration avec un nouveau jeu de fragments.

</details>

<details>
<summary><strong>Puis-je lire le mot de passe seul avec une seule carte ?</strong></summary>

Non. C'est mathématiquement impossible. Un seul fragment ne révèle aucune information sur le secret — c'est une propriété de l'algorithme appelée information-theoretic security. Seul le rassemblement du nombre requis de fragments permet de reconstituer le mot de passe principal.

</details>

<details>
<summary><strong>Que se passe-t-il si l'une des personnes désignées décède ou devient indisponible ?</strong></summary>

Le système est conçu avec de la redondance — il suffit de réunir le nombre minimum requis de fragments (par ex. 3 sur 5). L'indisponibilité d'une ou deux personnes ne bloque pas la procédure d'urgence, tant que les autres peuvent se rassembler.

</details>

<details>
<summary><strong>Le mot de passe atteint-il le serveur pendant le déchiffrement ?</strong></summary>

Non. La reconstitution du mot de passe à partir des fragments Shamir se fait **entièrement côté navigateur** (JavaScript). Le serveur ne sert qu'à authentifier l'utilisateur — le secret lui-même ne le quitte jamais.

</details>

<details>
<summary><strong>Que se passe-t-il si les tiers de confiance s'entendent pour récupérer le mot de passe de mon vivant, à mon insu ?</strong></summary>

Le mot de passe seul ne leur suffit pas. Les fichiers de la base de données se trouvent en dehors du répertoire public du serveur, et l'accès y est contrôlé par `download.php`. Dès la première reconstitution réussie du mot de passe dans le panneau — optionnellement, mais fortement recommandé, également vérifiée côté serveur via `SECRET_HASH`, afin que la simple cohérence des parts combinées (sans connaître le vrai mot de passe) ne suffise pas à forcer le blocage — le système bloque automatiquement les téléchargements de fichiers pendant 48 heures et vous envoie un e-mail d'alerte avec un lien à usage unique « Panic Button » — un clic coupe définitivement l'accès, vous laissant le temps de changer le mot de passe principal en toute tranquillité. Si vous protégez en plus la base de mots de passe par une clé de sécurité physique (par ex. YubiKey), la seule connaissance du mot de passe ne suffira pas à l'ouvrir, même une fois les fichiers débloqués.

</details>

<details>
<summary><strong>Puis-je utiliser le système avec un gestionnaire de mots de passe autre que KeePassXC ?</strong></summary>

Oui. Secret Key stocke et reconstitue **n'importe quel mot de passe principal** — quel que soit le gestionnaire utilisé. Compatible avec tout programme prenant en charge un mot de passe principal : KeePassXC, Bitwarden, 1Password et d'autres.

</details>

<details>
<summary><strong>Comment choisir le seuil — combien de fragments sont requis ?</strong></summary>

Plus le seuil est élevé, plus la sécurité est grande — mais aussi plus il est difficile de rassembler tout le monde en situation de crise. Le compromis recommandé pour un usage standard est **3 sur 5** — il tolère l'indisponibilité de deux personnes tout en conservant un bon niveau de protection.

</details>

<details>
<summary><strong>Combien de temps les identifiants de connexion d'une carte restent-ils valables ?</strong></summary>

Indéfiniment — tant que le propriétaire ne génère pas une nouvelle configuration et ne remplace pas le fichier `secret-key.php` sur le serveur. Après cette opération, les anciennes cartes cessent de fonctionner et il faut en distribuer de nouvelles à toutes les personnes désignées.

</details>

---

<div align="center">

Si ce projet vous a été utile, laissez une ⭐ — cela aide d'autres personnes qui pourraient en avoir besoin à le trouver.

</div>

---

<div align="center">

Copyright © 2026 · [karpierz.me](https://karpierz.me)

</div>
