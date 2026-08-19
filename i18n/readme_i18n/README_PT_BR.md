<div align="center">

<img src="../../img/secret-key-logo.svg" width="380" alt="Secret Key Logo">

</div>

---

<p align="center">
  <a href="../../README.md">🇵🇱 Polski</a> •
  <a href="README_EN.md">🇬🇧 English</a> •
  <a href="README_ES.md">🇪🇸 Español</a> •
  <a href="README_DE.md">🇩🇪 Deutsch</a> •
  <kbd>🇧🇷 Português (Brasil)</kbd> •
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

### Um sistema criptográfico de acesso de emergência para seu banco de senhas

<br>

[![PHP](https://img.shields.io/badge/PHP-8%2B-7c3aed?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Zero SQL](https://img.shields.io/badge/Banco_de_dados-Zero_SQL-4ade80?style=flat-square)](#stack)
[![Self-hosted](https://img.shields.io/badge/Hospedagem-Self--hosted-c084fc?style=flat-square)](#instalação)
[![2FA](https://img.shields.io/badge/2FA-SMS_·_SMSPlanet-38bdf8?style=flat-square)](#segurança)
[![Shamir](https://img.shields.io/badge/Criptografia-Shamir_SSS-f472b6?style=flat-square)](#algoritmo-de-shamir)
[![License](https://img.shields.io/badge/Licença-MIT-818cf8?style=flat-square)](../../LICENSE)

<br>

**O que acontece com suas contas após a sua morte?**

O Secret Key divide criptograficamente a senha mestra do seu banco de senhas entre pessoas de confiança.  
Ninguém a conhece por conta própria — apenas juntas, em um momento acordado, podem reconstruí-la.

<br>

![Secret Key Demo](../../img/SecretKeyGif.gif)

<br>

[**→ Ver a demo**](https://app.secretkey.website) &nbsp;·&nbsp; [**Site do projeto**](https://secretkey.website) &nbsp;·&nbsp; [**Documentação**](https://secretkey.website/docs)

</div>

---

## Sumário

- 01 · 🔑 [Ideia](#ideia)
- 02 · ⚙️ [Como funciona](#como-funciona)
- 03 · 🎬 [Apresentação do sistema](#apresentação-do-sistema)
- 04 · 💳 [Cartão Secret Key](#cartão-secret-key)
- 05 · 🔐 [Algoritmo de Shamir](#algoritmo-de-shamir)
- 06 · 🏗️ [Arquitetura do sistema](#arquitetura-do-sistema)
- 07 · 🛡️ [Segurança](#segurança)
- 08 · 🚀 [Instalação](#instalação)
- 09 · 📁 [Estrutura de arquivos](#estrutura-de-arquivos)
- 10 · ❓ [Perguntas frequentes](#faq)

---

## Ideia

Cada um de nós guarda dezenas de senhas — de bancos, e-mail, redes sociais, assinaturas. **O que acontece com elas depois que morremos?** A família fica sem acesso às contas, incapaz de cancelar assinaturas, encerrar contas ou recuperar dinheiro.

O Secret Key resolve esse problema criando um plano de emergência seguro com duas garantias:

| &nbsp; | Problema | Solução |
|---|---|---|
| 🔐 | Acesso não autorizado **durante a vida** do proprietário | Toda tentativa de acesso exige uma senha + um código SMS |
| 💀 | Perda de acesso às contas **após a morte** | Pessoas designadas reconstroem a senha usando o algoritmo de Shamir |

O sistema é **totalmente auto-hospedado** — os dados nunca saem do seu servidor. Nenhum banco de dados central, nenhuma nuvem, nenhuma dependência externa além do envio de SMS.

<a name="stack"></a>

```
Backend         PHP 8+, arquivos planos (JSON + config PHP), zero SQL
Autorização     bcrypt cost=10, 2FA por SMS via API da SMSPlanet
Criptografia    Shamir Secret Sharing (secrets.js)
Frontend        HTML + JS puro, zero dependências externas (offline)
```

---

## Como funciona

Em uma situação de crise, as pessoas designadas executam quatro etapas:

```
┌──────────────────────────────────────────────────────────────────────┐
│                                                                      │
│  01  REUNIÃO         Mín. 3 de 5 pessoas designadas                  │
│      ─────────       Cada uma tem um cartão com um fragmento         │
│                                                                      │
│  02  LOGIN           Credenciais do cartão Secret Key                │
│      ──────────      + um código SMS para o número atribuído         │
│                                                                      │
│  03  FRAGMENTOS      Cada pessoa insere seu fragmento ou              │
│      ─────────       escaneia o código QR do cartão                  │
│                                                                      │
│  04  ACESSO          Senha reconstruída localmente no navegador —     │
│      ────────        nunca chega ao servidor                         │
│                                                                      │
└──────────────────────────────────────────────────────────────────────┘
```

**Sem o telefone atribuído à conta, o login é impossível**, mesmo conhecendo os dados do cartão.  
**Sem o número necessário de fragmentos, reconstruir a senha é matematicamente impossível**, mesmo com acesso ao servidor.

---

## Apresentação do sistema

<p align="center">
  <a href="https://www.youtube.com/watch?v=omx7mpQD5-M" target="_blank">
    <img src="../../img/video-player.webp" alt="Assista a um vídeo de apresentação do sistema Secret Key" width="780">
  </a>
</p>

> [!TIP]
> 🔊 O vídeo está disponível em vários idiomas — clique em ⚙️ no player do YouTube → **Legendas/Áudio (faixa de áudio)**, para escolher o idioma da dublagem ou ativar as legendas.

---

## Cartão Secret Key

Cada pessoa designada recebe um suporte personalizado com quatro elementos:

| Elemento | Descrição |
|---|---|
| 🔑 **Login e senha** | Credenciais de acesso exclusivas — cada pessoa tem sua própria conta com um número de telefone atribuído |
| 🔢 **Fragmento Shamir** | Um fragmento de chave em formato hexadecimal — um de cinco, inútil sem os demais |
| ⊞ **Código QR** | O mesmo fragmento codificado como QR — o escaneamento elimina erros de transcrição manual |
| 🌐 **Endereço do sistema** | A URL da sua própria instância do Secret Key — leva direto ao painel de login |

**O formato do suporte é livre** — uma folha impressa, um PDF, um cartão de plástico. O importante é que ele contenha os quatro elementos acima.

---

## Algoritmo de Shamir

O Secret Key usa a biblioteca [`secrets.js`](https://github.com/grempe/secrets.js) (fork de [amper5and/secrets.js](https://github.com/amper5and/secrets.js), incorporado via [iancoleman.io/shamir](https://iancoleman.io/shamir/)) para dividir e reconstruir o segredo.

> [!NOTE]
> A biblioteca utilizada é **idêntica à usada por [iancoleman.io/shamir](https://iancoleman.io/shamir/)** — o formato dos fragmentos é totalmente compatível, o que permite verificação independente fora do sistema.

### Divisão e reconstrução do segredo

```
Senha mestra: "MyKeePassPassword2024!"
         │
         ▼  Divisão em 5 fragmentos (limite: 3)
         │
    ┌────┴──────────────────────────────────┐
    │                                       │
    │  S1: 801a3f9c2e4b7d1...  →  Pessoa A  │  Cada fragmento
    │  S2: 802c8f1a5e9b3d7...  →  Pessoa B  │  é inútil sem
    │  S3: 803e2a7f4c1b9d5...  →  Pessoa C  │  o número
    │  S4: 804b6d3e8f2a1c9...  →  Pessoa D  │  necessário dos
    │  S5: 805d9f7b2e4c3a1...  →  Pessoa E  │  demais
    │                                       │
    └────┬──────────────────────────────────┘
         │
         ▼  Reconstrução — quaisquer 3 de 5 bastam
         │
    S1 + S2 + S3  →  "MyKeePassPassword2024!"  ✓
    S2 + S4 + S5  →  "MyKeePassPassword2024!"  ✓
    S1            →  nenhuma informação sobre o segredo  ✗
```

### Propriedades criptográficas

O segredo é codificado como o termo constante de um polinômio sobre o corpo GF(2⁸). Cada fragmento é um ponto nesse polinômio — sabendo o número necessário de pontos, ele pode ser reconstruído de forma única por interpolação de Lagrange:

```
f(x) = a₀ + a₁x + a₂x² + ... + aₖ₋₁xᵏ⁻¹  (mod p)
```

> [!IMPORTANT]
> Possuir **menos que o número necessário** de fragmentos não fornece **nenhuma** informação sobre o segredo (information-theoretic security). O padding adicional de 1024 bits impede ataques contra segredos pequenos.

### Parâmetros de implementação

| Parâmetro | Valor |
|---|---|
| Biblioteca | secrets.js (fork grempe, via iancoleman.io/shamir) |
| Formato do fragmento | `8` + coordenada x em 2 hex + dados |
| minPad | 1024 bits |
| Codificação | UTF-8 (str2hex) |
| Reconstrução | JavaScript no lado do navegador — a senha nunca chega ao servidor |

### Escolha dos parâmetros de divisão

| Total de fragmentos | Mínimo necessário | Cenário |
|---|---|---|
| 3 | 2 | Família pequena |
| 5 | 3 | Padrão *(recomendado)* |
| 7 | 4 | Família maior / empresa |

---

## Arquitetura do sistema

O usuário passa por camadas sucessivas de verificação antes de obter acesso aos recursos protegidos:

```
Usuário       →  digita login + senha
                         │
Sistema PHP   →  verifica a senha (bcrypt), checa rate limiting e CSRF
                         │
SMS / 2FA     →  envia um código único para o número atribuído
                         │
Usuário       →  digita o código SMS
                         │
Sistema PHP   →  verifica o código, cria uma sessão, opcionalmente lembra o dispositivo
                         │
Navegador     →  recebe os fragmentos Shamir, reconstrói o segredo localmente em JS
```

### Camadas do sistema

**Camada de autorização** (`/app/`)
- `login.php` — o formulário de login
- `auth.php` — lógica de sessão, bcrypt, 2FA, proteção contra força bruta, CSRF, além das funções auxiliares `t()` (textos de UI), `md_lite()` (markdown-lite), `empty_state_box()`
- `verify.php` — verificação do código SMS
- `resend.php` — reenvio do SMS
- `logout.php` — logout

**Camada de acesso** (`/decrypt/`)
- `index.php` — o painel de descriptografia com reconstrução Shamir em JS
- `download.php` — downloads de arquivos controlados (requer sessão, lista branca construída a partir da configuração, log no lado do servidor)
- `log.php` — registro de eventos
- `devtools-log.php` — registro de tentativas de inspeção via DevTools (com rate limiting por IP)

**Camada de dados** (`/private/` — fora de `public_html`)
- `secret-key.php` — um único arquivo de configuração: pessoas (`$people`), arquivos para download (`$downloads`), instruções (`$instructions`), notificação por e-mail (`$email_notify`), domínio SMS
- `lang.php` — textos de interface (um único idioma, sem seletor — veja [Instalação](#instalação))
- `rate-limit.php` — rate limiting persistente (contadores independentes da sessão)
- `rate_limits.json` — contadores de tentativas de login por IP/conta *(criado automaticamente)*
- `trusted_devices.json` — tokens de dispositivos confiáveis *(criado automaticamente)*
- `secret-key.log` — logs de eventos
- `moja-baza-hasel.kdbx` *(e outros arquivos para download)* — servidos apenas via `download.php`, nunca diretamente via HTTP

> [!WARNING]
> Todos os arquivos de configuração sensíveis são armazenados **fora do diretório público** do servidor — uma configuração incorreta do servidor web não corre o risco de expô-los.

---

## Segurança

O sistema combina **oito camadas independentes de proteção** — comprometer uma delas não concede acesso ao sistema.

| Camada | Mecanismo | Detalhes |
|---|---|---|
| 🔒 **Senhas** | bcrypt | cost=10, formato `$2y$`, verificação resistente a ataques de temporização |
| 🛡️ **CSRF** | Token de 64 hex | Gerado criptograficamente, verificado em todo endpoint que altera o estado (login, verificação 2FA, reenvio de SMS, logout, log de eventos) |
| 🚫 **Força bruta** | Rate limiting | 3 tentativas/IP + 3 tentativas/conta em uma janela de 15 min; 3 códigos SMS incorretos/hora. Contadores persistentes no lado do servidor (um arquivo, independente da sessão/cookies do cliente) |
| 📱 **2FA via SMS** | Código de 6 dígitos | Gerado criptograficamente, válido por 10 min, 60s de espera entre envios |
| 💻 **Dispositivos confiáveis** | SHA-256, HttpOnly | Secure + SameSite=Strict, arquivo fora de `public_html`, TTL de 7 dias |
| ⏱️ **Sessão** | Logout automático | Cookie de sessão com flags explícitos HttpOnly + Secure + SameSite=Strict; timeout de 30 min, regeneração do ID de sessão após cada verificação |
| 🖥️ **Proteção da interface** | Detecção de DevTools | Detecta ferramentas de desenvolvedor, remove fisicamente o DOM, registra o incidente com IP, REF# e duração |
| 📥 **Downloads controlados** | `download.php` + lista branca | Arquivos para download ficam fora de `public_html`; sessão ativa exigida, sem URL direta, sempre registrado no lado do servidor |

---

## Instalação

### Requisitos

- PHP 8.0+
- Um servidor web (Apache / Nginx)
- Uma conta na [SMSPlanet](https://smsplanet.pl) (para envio de códigos 2FA)
- Acesso a um diretório fora de `public_html`

---

### Etapa 1 — Configuração (offline)

Abra o `dashboard.html` localmente no seu navegador. Ele tem duas abas:

**Configuração** — gera o arquivo `secret-key.php`:
1. O token da API da SMSPlanet, o nome do remetente do SMS e o domínio para autopreenchimento do código (Android/iOS) — apenas o domínio, sem `@` e sem `https://`
2. Para cada pessoa designada: login, senha, nome, sobrenome, número de telefone e se ela deve ficar visível na lista de titulares no painel
3. Arquivos para download (banco de senhas, banco 2FA, instalador do programa) — chave, rótulo do botão, nome do arquivo
4. As etapas de instrução para o painel (formatação: `**negrito**` / `*itálico*`)
5. Opcional: uma notificação por e-mail a cada login (endereço do destinatário, remetente, link do painel)
6. Clique em "Gerar configuração" e baixe o arquivo `secret-key.php` gerado

**Criptografia** — divide a senha mestra em fragmentos Shamir:
1. Digite a senha mestra do banco de senhas
2. Defina o número total de fragmentos e o mínimo necessário
3. Clique em "Gerar fragmentos"
4. Baixe o arquivo `secret-key-shares.txt`

> [!TIP]
> Ambas as ferramentas funcionam **totalmente offline** — nenhum dado sai do navegador. O formulário gera `secret-key.php` do zero a cada execução — ele não carrega nem edita um arquivo existente.

---

### Etapa 2 — Envio para o servidor

```bash
/home/user/
├── public_html/
│   ├── app/           ← conteúdo da pasta /app/
│   └── decrypt/       ← conteúdo da pasta /decrypt/
└── private/           ← FORA de public_html
    ├── secret-key.php  ← o arquivo de configuração gerado
    ├── lang.php        ← textos de interface (um idioma, veja a etapa 1)
    ├── rate-limit.php  ← um arquivo de sistema do repositório (rate limiting)
    └── moja-baza-hasel.kdbx  ← seus arquivos (servidos via download.php)
```

> [!WARNING]
> `lang.php` precisa ser enviado ao servidor **junto com** `secret-key.php`. O `auth.php` o carrega via `require_once` — a ausência do arquivo derruba todo o sistema (erro fatal em cada página), não apenas as traduções.

---

### Etapa 3 — Caminho para a configuração

No arquivo `auth.php`, atualize o caminho para o arquivo de configuração:

```php
require_once '/home/user/private/secret-key.php';
```

---

### Etapa 4 — Domínio no conteúdo do SMS (WebOTP)

O conteúdo do código SMS enviado termina com a linha `@domínio #código` — este é o formato exigido pela [API WebOTP](https://developer.mozilla.org/en-US/docs/Web/API/WebOTP_API), graças à qual o navegador do celular preenche o campo do código por conta própria, sem digitação manual a partir do SMS.

Você define o domínio na **Etapa 1**, no formulário do dashboard (campo "Domínio para autopreenchimento de SMS") — você insere apenas o domínio, sem `@` e sem `https://` (o dashboard adiciona o `@` automaticamente). Ele entra na configuração como:

```php
define('SMS_AUTOFILL_DOMAIN', '@seu-dominio.com.br');
```

> [!WARNING]
> O domínio precisa corresponder **exatamente** àquele sob o qual você hospeda o sistema — caso contrário, o WebOTP vai ignorar o SMS e o autopreenchimento não funcionará. O código ainda vai chegar e funcionar ao ser digitado manualmente, só sem essa comodidade.

---

### Etapa 5 — Distribuição dos cartões

Para cada pessoa designada, prepare um suporte com:
- login e senha (da aba Configuração)
- um fragmento Shamir (do arquivo `secret-key-shares.txt`)
- opcionalmente: um código QR com o mesmo fragmento
- o endereço da sua instância do sistema

---

## Estrutura de arquivos

```
secret-key/
│
├── 📁 app/                        # Público — sistema de login
│   ├── 📁 decrypt/                # Protegido — painel do usuário
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
├── 📁 dashboard/                  # Ferramentas de configuração (offline)
│   ├── card-back.js
│   ├── card-front.js
│   ├── dashboard.html
│   ├── favicon.ico
│   ├── generate-card.html
│   ├── generate-hash.html
│   └── generate-shamir.html
│
├── 📁 img/                        # Recursos gráficos
│
└── 📁 private/                    # Fora de public_html — arquivos de configuração
    ├── .htaccess
    ├── demo-baza-hasel.txt         # Arquivo de download de exemplo (substitua pelo seu)
    ├── lang.php                    # Textos de interface (um idioma)
    ├── rate-limit.php
    └── secret-key.php
```

---

## Perguntas frequentes

<details>
<summary><strong>O que acontece se eu perder meu cartão Secret Key?</strong></summary>

O cartão isoladamente é inútil sem acesso ao telefone atribuído àquela conta — o login exige verificação por SMS. O risco é limitado, mas o proprietário deve ser informado e considerar gerar uma nova configuração com um novo conjunto de fragmentos.

</details>

<details>
<summary><strong>Posso ler a senha por conta própria com apenas um cartão?</strong></summary>

Não. É matematicamente impossível. Um único fragmento não revela nenhuma informação sobre o segredo — essa é uma propriedade do algoritmo chamada information-theoretic security. Somente reunindo o número necessário de fragmentos é possível reconstruir a senha mestra.

</details>

<details>
<summary><strong>E se uma das pessoas designadas morrer ou ficar indisponível?</strong></summary>

O sistema foi projetado com redundância — basta reunir o número mínimo necessário de fragmentos (por exemplo, 3 de 5). A indisponibilidade de uma ou duas pessoas não bloqueia o procedimento de emergência, desde que as demais possam se reunir.

</details>

<details>
<summary><strong>A senha chega ao servidor durante a descriptografia?</strong></summary>

Não. A reconstrução da senha a partir dos fragmentos Shamir ocorre **totalmente no lado do navegador** (JavaScript). O servidor serve apenas para autenticar o usuário — o segredo em si nunca o deixa.

</details>

<details>
<summary><strong>Posso usar o sistema com um gerenciador de senhas diferente do KeePassXC?</strong></summary>

Sim. O Secret Key armazena e reconstrói **qualquer senha mestra** — independentemente do gerenciador usado. Compatível com qualquer programa que aceite uma senha mestra: KeePassXC, Bitwarden, 1Password e outros.

</details>

<details>
<summary><strong>Como escolher o limite — quantos fragmentos são necessários?</strong></summary>

Quanto maior o limite, maior a segurança — mas também mais difícil reunir todos em uma situação de crise. O compromisso recomendado para uso padrão é **3 de 5** — tolera a indisponibilidade de duas pessoas mantendo um bom nível de proteção.

</details>

<details>
<summary><strong>Por quanto tempo as credenciais de login de um cartão são válidas?</strong></summary>

Indefinidamente — desde que o proprietário não gere uma nova configuração e substitua o arquivo `secret-key.php` no servidor. Após essa operação, os cartões antigos deixam de funcionar e é necessário distribuir novos para todas as pessoas designadas.

</details>

---

<div align="center">

Copyright © 2026 · [karpierz.me](https://karpierz.me)

</div>
