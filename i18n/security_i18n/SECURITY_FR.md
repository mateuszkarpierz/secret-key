# 🔐 Politique de sécurité

<p align="center">
  <a href="../../SECURITY.md">🇵🇱 Polski</a> •
  <a href="SECURITY_EN.md">🇬🇧 English</a> •
  <a href="SECURITY_ES.md">🇪🇸 Español</a> •
  <a href="SECURITY_DE.md">🇩🇪 Deutsch</a> •
  <a href="SECURITY_PT_BR.md">🇧🇷 Português (Brasil)</a> •
  <kbd>🇫🇷 Français</kbd> •
  <a href="SECURITY_ZH.md">🇨🇳 简体中文</a> •
  <a href="SECURITY_AR.md">🇸🇦 العربية</a> •
  <a href="SECURITY_HI.md">🇮🇳 हिन्दी</a> •
  <a href="SECURITY_JA.md">🇯🇵 日本語</a> •
  <a href="SECURITY_RU.md">🇷🇺 Русский</a> •
  <a href="SECURITY_UK.md">🇺🇦 Українська</a>
</p>

---

Les bugs classiques, les fautes de frappe dans la documentation ou les suggestions d'amélioration se signalent normalement via une [Issue sur GitHub](https://github.com/mateuszkarpierz/secret-key/issues) — c'est la voie standard, il n'y a rien de secret là-dedans. Cette page traite uniquement des **vulnérabilités de sécurité** (voir le [périmètre des signalements](#-périmètre-des-signalements) ci-dessous) — celles-ci, merci de ne **pas** les signaler publiquement.

---

## 📬 Signaler une vulnérabilité de sécurité

> [!CAUTION]
> Signalez les vulnérabilités de sécurité uniquement en privé, à **dev@secretkey.website** — jamais via une Issue publique sur GitHub. Divulguer publiquement un exploit avant la publication d'un correctif met en danger toute personne ayant déployé le système.

### Comment signaler

Envoyez une description détaillée à : **dev@secretkey.website**

### Ce qu'il faut inclure dans le signalement

| | Élément |
|---|---|
| 📝 | Description de la vulnérabilité et de son impact potentiel |
| 🔁 | Étapes de reproduction (preuve de concept) |
| 🏷️ | Version du système concernée |
| 💡 | Proposition de correction *(facultatif)* |

### À quoi s'attendre

| Délai | Réponse |
|---|---|
| **48h** | Accusé de réception du signalement |
| **7 jours** | Point d'avancement |
| Après la correction | Remerciement public *(si vous le souhaitez)* |

---

## 🏷️ Versions prises en charge

La **dernière version publiée** est toujours prise en charge. Les signalements concernant des versions plus anciennes sont acceptés, mais il est d'abord demandé de mettre à jour vers la dernière version — certaines vulnérabilités ont peut-être déjà été corrigées.

Vous trouverez la version actuelle sur la [page Releases](https://github.com/mateuszkarpierz/secret-key/releases).

---

## 🎯 Périmètre des signalements

### ✅ Dans le périmètre

- Contournement de l'authentification (bcrypt, 2FA)
- Vulnérabilités CSRF malgré les protections en place
- Possibilité de reconstituer le secret de Shamir sans le nombre requis de fragments
- Exposition de données du répertoire `/private/`
- Téléchargement de fichiers sensibles (par ex. la base de mots de passe) en contournant la connexion, par ex. via une URL directe
- Vulnérabilité aux attaques par force brute malgré le rate limiting
- XSS, injection SQL *(bien que le système n'utilise pas SQL)*

### ❌ Hors périmètre

- Attaques nécessitant un accès physique au serveur
- Attaques d'ingénierie sociale contre les détenteurs de cartes Secret Key
- Problèmes de configuration du serveur web du côté de l'utilisateur
- Rapports générés automatiquement par des scanners sans PoC

---

## 🛡️ Bonnes pratiques de déploiement

> [!WARNING]
> Secret Key est un système **autohébergé** — la sécurité de votre installation dépend en grande partie de la configuration de votre propre serveur.

| | Pratique |
|---|---|
| 📁 | Gardez le répertoire `/private/` **en dehors** de `public_html` |
| 🔒 | Utilisez HTTPS (SSL/TLS) sur le serveur |
| 🔄 | Mettez régulièrement PHP à jour vers la dernière version 8.x |
| 🚫 | Ne rendez jamais le fichier `secret-key.php` public |
| 📥 | Ne créez pas de lien direct vers des fichiers sensibles dans le répertoire public — servez-les via `download.php` (nécessite une session, journalise les téléchargements) |
| 🔑 | Utilisez des mots de passe forts et uniques pour chaque compte |

---

<div align="center">

Copyright © 2026 · [karpierz.me](https://karpierz.me)

</div>
