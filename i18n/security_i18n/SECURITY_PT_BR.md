# 🔐 Política de segurança

<p align="center">
  <a href="../../SECURITY.md">🇵🇱 Polski</a> •
  <a href="SECURITY_EN.md">🇬🇧 English</a> •
  <a href="SECURITY_ES.md">🇪🇸 Español</a> •
  <a href="SECURITY_DE.md">🇩🇪 Deutsch</a> •
  <kbd>🇧🇷 Português (Brasil)</kbd> •
  <a href="SECURITY_FR.md">🇫🇷 Français</a> •
  <a href="SECURITY_ZH.md">🇨🇳 简体中文</a> •
  <a href="SECURITY_AR.md">🇸🇦 العربية</a> •
  <a href="SECURITY_HI.md">🇮🇳 हिन्दी</a> •
  <a href="SECURITY_JA.md">🇯🇵 日本語</a> •
  <a href="SECURITY_RU.md">🇷🇺 Русский</a> •
  <a href="SECURITY_UK.md">🇺🇦 Українська</a>
</p>

---

Bugs comuns, erros de digitação na documentação ou sugestões de melhorias devem ser reportados normalmente como uma [Issue no GitHub](https://github.com/mateuszkarpierz/secret-key/issues) — essa é a via padrão, não há nada de secreto nisso. Esta página trata exclusivamente de **vulnerabilidades de segurança** (veja o [escopo dos relatos](#-escopo-dos-relatos) abaixo) — essas, por favor, **não** reporte publicamente.

---

## 📬 Como reportar vulnerabilidades de segurança

> [!CAUTION]
> Reporte vulnerabilidades de segurança somente em privado, para **dev@secretkey.website** — nunca como uma Issue pública no GitHub. Divulgar publicamente um exploit antes do lançamento de uma correção coloca em risco todos que têm o sistema implantado.

### Como reportar

Envie uma descrição detalhada para: **dev@secretkey.website**

### O que incluir no relato

| | Elemento |
|---|---|
| 📝 | Descrição da vulnerabilidade e seu impacto potencial |
| 🔁 | Passos para reproduzir (prova de conceito) |
| 🏷️ | Versão do sistema afetada |
| 💡 | Sugestão de correção *(opcional)* |

### O que esperar

| Tempo | Resposta |
|---|---|
| **48h** | Confirmação de recebimento do relato |
| **7 dias** | Atualização sobre o progresso |
| Após a correção | Agradecimento público *(se desejar)* |

---

## 🏷️ Versões suportadas

A **versão mais recente lançada** é sempre suportada. Relatos sobre versões mais antigas são aceitos, mas primeiro pede-se a atualização para o release mais recente — algumas vulnerabilidades já podem ter sido corrigidas.

Você encontra a versão atual na [página de Releases](https://github.com/mateuszkarpierz/secret-key/releases).

---

## 🎯 Escopo dos relatos

### ✅ Dentro do escopo

- Contorno de autenticação (bcrypt, 2FA)
- Vulnerabilidades CSRF apesar das proteções implementadas
- Possibilidade de reconstruir o segredo de Shamir sem o número necessário de fragmentos
- Exposição de dados do diretório `/private/`
- Download de arquivos sensíveis (por ex. o banco de senhas) contornando o login, por ex. via URL direta
- Suscetibilidade a ataques de força bruta apesar do rate limiting
- XSS, injeção de SQL *(embora o sistema não use SQL)*

### ❌ Fora do escopo

- Ataques que exigem acesso físico ao servidor
- Ataques de engenharia social contra portadores de cartões Secret Key
- Problemas de configuração do servidor web do lado do usuário
- Relatórios gerados automaticamente por scanners sem PoC

---

## 🛡️ Boas práticas de implantação

> [!WARNING]
> O Secret Key é um sistema **auto-hospedado (self-hosted)** — a segurança da sua instalação depende, em grande parte, da configuração do seu próprio servidor.

| | Prática |
|---|---|
| 📁 | Mantenha o diretório `/private/` **fora** de `public_html` |
| 🔒 | Use HTTPS (SSL/TLS) no servidor |
| 🔄 | Atualize o PHP regularmente para a versão 8.x mais recente |
| 🚫 | Nunca exponha o arquivo `secret-key.php` publicamente |
| 📥 | Não crie links diretos para arquivos sensíveis no diretório público — sirva-os via `download.php` (exige sessão, registra os downloads) |
| 🔑 | Use senhas fortes e únicas para cada conta |

---

<div align="center">

Copyright © 2026 · [karpierz.me](https://karpierz.me)

</div>
