# 🔐 安全政策

<p align="center">
  <a href="../../SECURITY.md">🇵🇱 Polski</a> •
  <a href="SECURITY_EN.md">🇬🇧 English</a> •
  <a href="SECURITY_ES.md">🇪🇸 Español</a> •
  <a href="SECURITY_DE.md">🇩🇪 Deutsch</a> •
  <a href="SECURITY_PT_BR.md">🇧🇷 Português (Brasil)</a> •
  <a href="SECURITY_FR.md">🇫🇷 Français</a> •
  <kbd>🇨🇳 简体中文</kbd> •
  <a href="SECURITY_AR.md">🇸🇦 العربية</a> •
  <a href="SECURITY_HI.md">🇮🇳 हिन्दी</a> •
  <a href="SECURITY_JA.md">🇯🇵 日本語</a> •
  <a href="SECURITY_RU.md">🇷🇺 Русский</a> •
  <a href="SECURITY_UK.md">🇺🇦 Українська</a>
</p>

---

普通的错误、文档中的拼写错误或改进建议，请正常通过 [GitHub Issue](https://github.com/mateuszkarpierz/secret-key/issues) 提交——这是标准途径，没有任何需要保密的地方。本页面仅涉及**安全漏洞**（参见下方的[报告范围](#-报告范围)）——此类问题请**不要**公开报告。

---

## 📬 报告安全漏洞

> [!CAUTION]
> 安全漏洞仅通过私下方式报告至 **dev@secretkey.website**——切勿以公开的 GitHub Issue 形式提交。在补丁发布之前公开披露漏洞利用方式，会使所有已部署该系统的用户面临风险。

### 如何报告

请将详细描述发送至：**dev@secretkey.website**

### 报告中应包含的内容

| | 内容 |
|---|---|
| 📝 | 漏洞描述及其潜在影响 |
| 🔁 | 复现步骤（概念验证） |
| 🏷️ | 受影响的系统版本 |
| 💡 | 修复建议*（可选）* |

### 预期的响应

| 时间 | 回应 |
|---|---|
| **48 小时** | 确认已收到报告 |
| **7 天** | 告知处理进展 |
| 修复完成后 | 公开致谢*（如果你愿意的话）* |

---

## 🏷️ 支持的版本

始终支持**最新发布的版本**。也接受针对旧版本的报告，但会首先建议更新到最新版本——部分漏洞可能已经被修复。

你可以在 [Releases 页面](https://github.com/mateuszkarpierz/secret-key/releases)找到当前版本。

---

## 🎯 报告范围

### ✅ 属于范围内

- 绕过身份验证（bcrypt、双因素认证）
- 尽管已有防护措施仍存在的 CSRF 漏洞
- 无需所需数量的密钥份额即可重新拼合出 Shamir 秘密的可能性
- `/private/` 目录中的数据泄露
- 绕过登录下载敏感文件（例如密码数据库），如通过直接 URL
- 尽管存在速率限制仍容易受到暴力破解攻击
- XSS、SQL 注入*（尽管该系统并未使用 SQL）*

### ❌ 不属于范围内

- 需要物理接触服务器的攻击
- 针对 Secret Key 卡片持有者的社会工程攻击
- 用户一侧的 Web 服务器配置问题
- 扫描工具自动生成且没有概念验证（PoC）的报告

---

## 🛡️ 部署最佳实践

> [!WARNING]
> Secret Key 是一个**自托管（self-hosted）**系统——你的安装的安全性在很大程度上取决于你自己服务器的配置。

| | 实践 |
|---|---|
| 📁 | 将 `/private/` 目录保存在 `public_html` **之外** |
| 🔒 | 在服务器上使用 HTTPS（SSL/TLS） |
| 🔄 | 定期将 PHP 更新到最新的 8.x 版本 |
| 🚫 | 切勿公开暴露 `secret-key.php` 文件 |
| 📥 | 不要直接链接到公共目录中的敏感文件——通过 `download.php` 提供（需要会话、记录下载日志） |
| 🔑 | 为每个账户使用强且唯一的密码 |

---

<div align="center">

版权所有 © 2026 · [karpierz.me](https://karpierz.me)

</div>
