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
  <a href="README_FR.md">🇫🇷 Français</a> •
  <kbd>🇨🇳 简体中文</kbd> •
  <a href="README_AR.md">🇸🇦 العربية</a> •
  <a href="README_HI.md">🇮🇳 हिन्दी</a> •
  <a href="README_JA.md">🇯🇵 日本語</a> •
  <a href="README_RU.md">🇷🇺 Русский</a> •
  <a href="README_UK.md">🇺🇦 Українська</a>
</p>

---

<div align="center">

### 用于密码数据库的加密紧急访问系统

<br>

[![PHP](https://img.shields.io/badge/PHP-8%2B-7c3aed?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Zero SQL](https://img.shields.io/badge/%E6%95%B0%E6%8D%AE%E5%BA%93-Zero_SQL-4ade80?style=flat-square)](#stack)
[![Self-hosted](https://img.shields.io/badge/%E9%83%A8%E7%BB%93%E6%9E%84-Self--hosted-c084fc?style=flat-square)](#安装)
[![2FA](https://img.shields.io/badge/2FA-SMS_·_SMSPlanet-38bdf8?style=flat-square)](#安全性)
[![Shamir](https://img.shields.io/badge/%E5%AF%86%E7%A0%81%E5%AD%A6-Shamir_SSS-f472b6?style=flat-square)](#shamir-算法)
[![License](https://img.shields.io/badge/%E8%AE%B8%E5%8F%AF%E8%AF%81-MIT-818cf8?style=flat-square)](../../LICENSE)

<br>

**你去世后，你的账户会发生什么？**

Secret Key 通过加密方式将密码数据库的主密码拆分给可信任的人。  
任何人都无法单独知道它——只有大家一起，在约定好的时刻，才能重新拼合出来。

<br>

![Secret Key Demo](../../img/SecretKeyGif.gif)

<br>

[**→ 查看演示**](https://app.secretkey.website) &nbsp;·&nbsp; [**项目官网**](https://secretkey.website) &nbsp;·&nbsp; [**文档**](https://secretkey.website/docs)

</div>

---

## 目录

- 01 · 🔑 [理念](#理念)
- 02 · ⚙️ [工作原理](#工作原理)
- 03 · 🎬 [系统介绍](#系统介绍)
- 04 · 💳 [Secret Key 卡片](#secret-key-卡片)
- 05 · 🔐 [Shamir 算法](#shamir-算法)
- 06 · 🏗️ [系统架构](#系统架构)
- 07 · 🛡️ [安全性](#安全性)
- 08 · 🚀 [安装](#安装)
- 09 · 📁 [文件结构](#文件结构)
- 10 · ❓ [常见问题](#faq)

---

## 理念

我们每个人都保存着几十个密码——银行、邮箱、社交媒体、订阅服务的密码。**我们去世后，这些密码会怎样？**家人会被完全挡在账户之外，无法取消订阅、注销账户，也无法追回资金。

Secret Key 用两项保证解决了这个问题，创建了一套安全的应急方案：

| &nbsp; | 问题 | 解决方案 |
|---|---|---|
| 🔐 | 所有者**在世期间**的未授权访问 | 每次访问尝试都需要密码 + 短信验证码 |
| 💀 | **去世后**失去账户访问权限 | 指定的人员使用 Shamir 算法重新拼合出密码 |

该系统**完全自托管**——数据永远不会离开你自己的服务器。没有中央数据库,没有云服务,除了发送短信外没有任何外部依赖。

<a name="stack"></a>

```
后端      PHP 8+，扁平文件（JSON + PHP 配置），零 SQL
授权      bcrypt cost=10，通过 SMSPlanet API 实现短信双因素认证
密码学    Shamir 秘密共享（secrets.js）
前端      纯 HTML + JS，零外部依赖（可离线运行）
```

---

## 工作原理

在紧急情况下，指定人员需完成四个步骤：

```
┌──────────────────────────────────────────────────────────────────────┐
│                                                                      │
│  01  集合          至少需要 5 位指定人员中的 3 位                     │
│      ─────────       每人持有一张带密钥片段的卡片                     │
│                                                                      │
│  02  登录          使用 Secret Key 卡片上的登录凭据                   │
│      ──────────      + 发送到指定手机号的短信验证码                   │
│                                                                      │
│  03  份额          每人输入自己的密钥份额，或                         │
│      ─────────       扫描卡片上的二维码                               │
│                                                                      │
│  04  访问          密码在浏览器本地重新拼合——                        │
│      ────────        永不发送到服务器                                 │
│                                                                      │
└──────────────────────────────────────────────────────────────────────┘
```

**没有账户绑定的手机——即使知道卡片上的信息，也无法登录。**  
**没有足够数量的密钥份额——即使能访问服务器，也无法通过数学方法重新拼合出密码。**

---

## 系统介绍

<p align="center">
  <a href="https://www.youtube.com/watch?v=omx7mpQD5-M" target="_blank">
    <img src="../../img/video-player.webp" alt="观看 Secret Key 系统介绍视频" width="780">
  </a>
</p>

> [!TIP]
> 🔊 该视频提供多种语言版本——点击 YouTube 播放器中的 ⚙️ → **字幕/音频（Audio track）**，即可选择配音语言或开启字幕。

---

## Secret Key 卡片

每位指定人员都会收到一份个性化的载体，包含四个要素：

| 要素 | 说明 |
|---|---|
| 🔑 **登录名和密码** | 独一无二的访问凭据——每人都有自己的账户，并绑定一个手机号 |
| 🔢 **Shamir 密钥份额** | 十六进制格式的密钥片段——五份中的一份，没有其余部分则毫无用处 |
| ⊞ **二维码** | 同一份份额编码为二维码——扫描可避免手动抄写出错 |
| 🌐 **系统地址** | 你自己的 Secret Key 实例的 URL——直接进入登录面板 |

**载体的形式不限**——打印的纸张、PDF 文件、塑料卡片皆可。重要的是它要包含以上四个要素。

---

## Shamir 算法

Secret Key 使用 [`secrets.js`](https://github.com/grempe/secrets.js) 库（[amper5and/secrets.js](https://github.com/amper5and/secrets.js) 的分支，通过 [iancoleman.io/shamir](https://iancoleman.io/shamir/) 引入）来拆分和重新拼合秘密。

> [!NOTE]
> 所使用的库与 [iancoleman.io/shamir](https://iancoleman.io/shamir/) 所使用的**完全相同**——密钥份额格式完全兼容,可在系统外部进行独立验证。

### 秘密的拆分与重新拼合

```
主密码："MyKeePassPassword2024!"
         │
         ▼  拆分为 5 份密钥份额（门限值：3）
         │
    ┌────┴──────────────────────────────────┐
    │                                       │
    │  S1: 801a3f9c2e4b7d1...  →  人员 A    │  每份密钥
    │  S2: 802c8f1a5e9b3d7...  →  人员 B    │  份额若没有
    │  S3: 803e2a7f4c1b9d5...  →  人员 C    │  所需数量的
    │  S4: 804b6d3e8f2a1c9...  →  人员 D    │  其余份额
    │  S5: 805d9f7b2e4c3a1...  →  人员 E    │  就毫无用处
    │                                       │
    └────┬──────────────────────────────────┘
         │
         ▼  重新拼合——5 份中任意 3 份即可
         │
    S1 + S2 + S3  →  "MyKeePassPassword2024!"  ✓
    S2 + S4 + S5  →  "MyKeePassPassword2024!"  ✓
    S1            →  无法获得关于秘密的任何信息  ✗
```

### 密码学特性

秘密被编码为有限域 GF(2⁸) 上一个多项式的常数项。每份密钥份额都是该多项式上的一个点——只要知道所需数量的点，就可以通过拉格朗日插值唯一地重新拼合出秘密：

```
f(x) = a₀ + a₁x + a₂x² + ... + aₖ₋₁xᵏ⁻¹  (mod p)
```

> [!IMPORTANT]
> 持有**少于所需数量**的密钥份额，无法获得关于秘密的**任何**信息（信息论安全性，information-theoretic security）。额外增加的 1024 位填充可防止针对小型秘密的攻击。

### 实现参数

| 参数 | 值 |
|---|---|
| 库 | secrets.js（grempe 分支，通过 iancoleman.io/shamir）|
| 份额格式 | `8` + 2 位十六进制 x 坐标 + 数据 |
| minPad | 1024 位 |
| 编码 | UTF-8（str2hex） |
| 重新拼合 | 在浏览器端通过 JavaScript 完成——密码永不发送到服务器 |

### 拆分参数的选择

| 密钥份额总数 | 所需最小数量 | 适用场景 |
|---|---|---|
| 3 | 2 | 小型家庭 |
| 5 | 3 | 标准配置 *（推荐）* |
| 7 | 4 | 大家庭 / 企业 |

---

## 系统架构

用户在获得受保护资源的访问权限之前，需要依次通过多层验证：

```
用户         →  输入登录名 + 密码
                         │
PHP 系统     →  验证密码（bcrypt），检查速率限制和 CSRF
                         │
短信 / 2FA   →  向绑定的手机号发送一次性验证码
                         │
用户         →  输入短信验证码
                         │
PHP 系统     →  验证验证码，创建会话，可选择记住设备
                         │
浏览器       →  接收 Shamir 密钥份额，在本地用 JS 重新拼合秘密
```

### 系统层级

**授权层**（`/app/`）
- `login.php` —— 登录表单
- `auth.php` —— 会话逻辑、bcrypt、双因素认证、防暴力破解、CSRF，以及辅助函数 `t()`（界面文本）、`md_lite()`（简易 markdown）、`empty_state_box()`
- `verify.php` —— 短信验证码校验
- `resend.php` —— 重新发送短信
- `logout.php` —— 注销登录

**访问层**（`/decrypt/`）
- `index.php` —— 带有 JS 端 Shamir 重新拼合功能的解密面板
- `download.php` —— 受控文件下载（需要有效会话、基于配置生成的白名单、服务器端日志记录）
- `log.php` —— 事件日志记录
- `devtools-log.php` —— 记录 DevTools 检查行为的尝试（带按 IP 的速率限制）

**数据层**（`/private/` —— 位于 `public_html` 之外）
- `secret-key.php` —— 单一配置文件：人员（`$people`）、可下载文件（`$downloads`）、操作说明（`$instructions`）、邮件通知（`$email_notify`）、短信域名
- `lang.php` —— 界面文本（仅支持单一语言，无语言切换器——参见[安装](#安装)）
- `rate-limit.php` —— 持久化速率限制（计数器独立于会话）
- `rate_limits.json` —— 按 IP/账户统计的登录尝试计数器*（自动创建）*
- `trusted_devices.json` —— 受信任设备令牌*（自动创建）*
- `secret-key.log` —— 事件日志
- `moja-baza-hasel.kdbx` *（以及其他可下载文件）* —— 仅通过 `download.php` 提供，绝不直接通过 HTTP 访问

> [!WARNING]
> 所有敏感配置文件都存储在服务器**公共目录之外**——即使 Web 服务器配置有误，也不会有暴露这些文件的风险。

---

## 安全性

该系统结合了**八层独立的防护机制**——攻破其中一层并不能获得系统的访问权限。

| 层级 | 机制 | 详情 |
|---|---|---|
| 🔒 **密码** | bcrypt | cost=10，`$2y$` 格式，可抵御时序攻击的验证方式 |
| 🛡️ **CSRF** | 64 位十六进制令牌 | 加密方式生成，在所有会改变状态的端点（登录、双因素验证、重新发送短信、登出、事件日志）都会进行校验 |
| 🚫 **防暴力破解** | 速率限制 | 15 分钟窗口内每 IP 3 次尝试 + 每账户 3 次尝试；每小时最多 3 次错误短信验证码。持久化的服务器端计数器（文件形式，独立于客户端的会话/Cookie） |
| 📱 **短信双因素认证** | 6 位数字验证码 | 加密方式生成，有效期 10 分钟，两次发送之间冷却 60 秒 |
| 💻 **受信任设备** | SHA-256、HttpOnly | Secure + SameSite=Strict，文件存放在 `public_html` 之外，有效期 7 天 |
| ⏱️ **会话** | 自动注销 | 会话 Cookie 明确设置 HttpOnly + Secure + SameSite=Strict 标志；30 分钟超时，每次验证后重新生成会话 ID |
| 🖥️ **界面保护** | DevTools 检测 | 检测开发者工具，物理移除 DOM，并记录包含 IP、REF# 和持续时间的事件日志 |
| 📥 **受控下载** | `download.php` + 白名单 | 可下载文件存放在 `public_html` 之外；需要有效会话，没有直接 URL，始终在服务器端记录日志 |

---

## 安装

### 系统要求

- PHP 8.0+
- 一台 Web 服务器（Apache / Nginx）
- 一个 [SMSPlanet](https://smsplanet.pl) 账户（用于发送双因素验证码）
- 可访问 `public_html` 之外的目录

---

### 第 1 步 —— 配置（离线）

在浏览器中本地打开 `dashboard.html`。它包含两个标签页：

**配置** —— 生成 `secret-key.php` 文件：
1. SMSPlanet API 令牌、短信发送者名称，以及用于自动填充验证码（Android/iOS）的域名——只填域名本身，不含 `@` 和 `https://`
2. 为每位指定人员填写：登录名、密码、名字、姓氏、手机号，以及是否在面板的持有人列表中显示
3. 可下载文件（密码数据库、双因素认证数据库、程序安装包）——键名、按钮文字、文件名
4. 面板中的操作说明步骤（支持格式：`**粗体**` / `*斜体*`）
5. 可选：每次登录时发送邮件通知（收件地址、发件人、面板链接）
6. 点击"生成配置"，下载生成的 `secret-key.php` 文件

**加密** —— 将主密码拆分为 Shamir 密钥份额：
1. 输入密码数据库的主密码
2. 设置密钥份额总数和所需最小数量
3. 点击"生成密钥份额"
4. 下载 `secret-key-shares.txt` 文件

> [!TIP]
> 这两个工具都**完全离线运行**——没有任何数据离开浏览器。每次运行时表单都会从零生成新的 `secret-key.php`——它不会加载或编辑已有文件。

---

### 第 2 步 —— 上传到服务器

```bash
/home/user/
├── public_html/
│   ├── app/           ← /app/ 文件夹的内容
│   └── decrypt/       ← /decrypt/ 文件夹的内容
└── private/           ← 位于 public_html 之外
    ├── secret-key.php  ← 生成的配置文件
    ├── lang.php        ← 界面文本（单一语言，参见第 1 步）
    ├── rate-limit.php  ← 来自仓库的系统文件（速率限制）
    └── moja-baza-hasel.kdbx  ← 你的文件（通过 download.php 提供）
```

> [!WARNING]
> `lang.php` 必须与 `secret-key.php` **一起**上传到服务器。`auth.php` 通过 `require_once` 加载它——如果文件缺失，整个系统都会崩溃（每一页都会出现致命错误），而不仅仅是翻译文本失效。

---

### 第 3 步 —— 配置文件路径

在 `auth.php` 文件中，更新配置文件的路径：

```php
require_once '/home/user/private/secret-key.php';
```

---

### 第 4 步 —— 短信内容中的域名（WebOTP）

发送的短信验证码内容以 `@域名 #验证码` 这一行结尾——这是 [WebOTP API](https://developer.mozilla.org/en-US/docs/Web/API/WebOTP_API) 所要求的格式，得益于此，手机浏览器可以自动填充验证码字段，无需从短信中手动抄写。

你在**第 1 步**中，在仪表盘表单里（"短信自动填充域名"字段）设置该域名——只需输入域名本身，不含 `@` 和 `https://`（仪表盘会自动加上 `@`）。它会写入配置中，形式如下：

```php
define('SMS_AUTOFILL_DOMAIN', '@your-domain.com');
```

> [!WARNING]
> 该域名必须与你实际部署系统所用的域名**完全一致**——否则 WebOTP 会忽略短信，自动填充也无法工作。验证码仍会正常送达，手动输入依然有效，只是失去了这项便利。

---

### 第 5 步 —— 分发卡片

为每位指定人员准备一份载体，包含：
- 登录名和密码（来自"配置"标签页）
- 一份 Shamir 密钥份额（来自 `secret-key-shares.txt` 文件）
- 可选：带有同一份额的二维码
- 你系统实例的地址

---

## 文件结构

```
secret-key/
│
├── 📁 app/                        # 公开——登录系统
│   ├── 📁 decrypt/                # 受保护——用户面板
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
├── 📁 dashboard/                  # 配置工具（离线）
│   ├── card-back.js
│   ├── card-front.js
│   ├── dashboard.html
│   ├── favicon.ico
│   ├── generate-card.html
│   ├── generate-hash.html
│   └── generate-shamir.html
│
├── 📁 img/                        # 图形资源
│
└── 📁 private/                    # 位于 public_html 之外——配置文件
    ├── .htaccess
    ├── demo-baza-hasel.txt         # 示例可下载文件（请替换为你自己的文件）
    ├── lang.php                    # 界面文本（单一语言）
    ├── rate-limit.php
    └── secret-key.php
```

---

## 常见问题

<details>
<summary><strong>如果我丢失了 Secret Key 卡片会怎样？</strong></summary>

单独一张卡片本身毫无用处，因为登录还需要该账户绑定手机上的短信验证。风险是有限的，但所有者应当被告知，并考虑生成一套新的配置和新的密钥份额。

</details>

<details>
<summary><strong>我能仅凭一张卡片自己读取密码吗？</strong></summary>

不能。这在数学上是不可能的。单独一份密钥份额不会泄露关于秘密的任何信息——这是该算法的一项特性，称为信息论安全性（information-theoretic security）。只有收集到所需数量的密钥份额，才能重新拼合出主密码。

</details>

<details>
<summary><strong>如果某位指定人员去世或无法联系到怎么办？</strong></summary>

该系统的设计具有冗余性——只需收集到所需的最小数量的密钥份额（例如 5 份中的 3 份）即可。只要其余人员能够聚齐，一两位人员无法联系并不会阻碍应急流程。

</details>

<details>
<summary><strong>解密过程中密码会发送到服务器吗？</strong></summary>

不会。从 Shamir 密钥份额重新拼合密码的过程**完全在浏览器端**（JavaScript）完成。服务器仅用于验证用户身份——秘密本身永远不会离开浏览器。

</details>

<details>
<summary><strong>我可以将此系统用于 KeePassXC 之外的其他密码管理器吗？</strong></summary>

可以。Secret Key 可以存储和重新拼合**任意的主密码**——与所使用的密码管理器无关。兼容任何支持主密码的程序：KeePassXC、Bitwarden、1Password 等。

</details>

<details>
<summary><strong>如何选择门限值——需要多少份密钥份额？</strong></summary>

门限值越高，安全性越高——但在紧急情况下把所有人聚集在一起也就越困难。对于标准使用场景，推荐的折中方案是**5 份中取 3 份**——即使两人无法联系到，也能保持良好的保护水平。

</details>

<details>
<summary><strong>卡片上的登录凭据有效期是多久？</strong></summary>

无限期有效——只要所有者没有生成新配置并替换服务器上的 `secret-key.php` 文件。一旦执行了这项操作，旧卡片就会失效,需要向所有指定人员重新分发新卡片。

</details>

---

<div align="center">

版权所有 © 2026 · [karpierz.me](https://karpierz.me)

</div>
