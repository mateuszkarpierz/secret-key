<div align="center">

<img src="../img/secret-key-logo.svg" width="380" alt="Secret Key Logo">

</div>

---

<p align="center">
  <a href="../README.md">🇵🇱 Polski</a> •
  <a href="README_EN.md">🇬🇧 English</a> •
  <a href="README_ES.md">🇪🇸 Español</a> •
  <a href="README_DE.md">🇩🇪 Deutsch</a> •
  <a href="README_PT_BR.md">🇧🇷 Português (Brasil)</a> •
  <a href="README_FR.md">🇫🇷 Français</a> •
  <a href="README_ZH.md">🇨🇳 简体中文</a> •
  <a href="README_AR.md">🇸🇦 العربية</a> •
  <a href="README_HI.md">🇮🇳 हिन्दी</a> •
  <kbd>🇯🇵 日本語</kbd> •
  <a href="README_RU.md">🇷🇺 Русский</a> •
  <a href="README_UK.md">🇺🇦 Українська</a>
</p>

---

<div align="center">

### パスワードデータベースのための暗号化された緊急アクセスシステム

<br>

[![PHP](https://img.shields.io/badge/PHP-8%2B-7c3aed?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Zero SQL](https://img.shields.io/badge/%E3%83%87%E3%83%BC%E3%82%BF%E3%83%99%E3%83%BC%E3%82%B9-Zero_SQL-4ade80?style=flat-square)](#stack)
[![Self-hosted](https://img.shields.io/badge/%E3%83%9B%E3%82%B9%E3%83%86%E3%82%A3%E3%83%B3%E3%82%B0-Self--hosted-c084fc?style=flat-square)](#インストール)
[![2FA](https://img.shields.io/badge/2FA-SMS_·_SMSPlanet-38bdf8?style=flat-square)](#セキュリティ)
[![Shamir](https://img.shields.io/badge/%E6%9A%97%E5%8F%B7-Shamir_SSS-f472b6?style=flat-square)](#shamirのアルゴリズム)
[![License](https://img.shields.io/badge/%E3%83%A9%E3%82%A4%E3%82%BB%E3%83%B3%E3%82%B9-MIT-818cf8?style=flat-square)](../LICENSE)

<br>

**あなたが亡くなった後、あなたのアカウントはどうなるのでしょうか？**

Secret Key は、パスワードデータベースのマスターパスワードを信頼できる人々の間で暗号学的に分割します。  
誰も単独ではそれを知ることができません — 合意された時に、みんなで一緒になったときだけ、再構築できます。

<br>

![Secret Key Demo](../img/SecretKeyGif.gif)

<br>

[**→ デモを見る**](https://app.secretkey.website) &nbsp;·&nbsp; [**プロジェクトサイト**](https://secretkey.website) &nbsp;·&nbsp; [**ドキュメント**](https://secretkey.website/docs)

</div>

---

## 目次

- 01 · 🔑 [アイデア](#アイデア)
- 02 · ⚙️ [動作の仕組み](#動作の仕組み)
- 03 · 🎬 [システム概要](#システム概要)
- 04 · 💳 [Secret Key カード](#secret-keyカード)
- 05 · 🔐 [Shamirのアルゴリズム](#shamirのアルゴリズム)
- 06 · 🏗️ [システムアーキテクチャ](#システムアーキテクチャ)
- 07 · 🛡️ [セキュリティ](#セキュリティ)
- 08 · 🚀 [インストール](#インストール)
- 09 · 📁 [ファイル構成](#ファイル構成)
- 10 · ❓ [よくある質問](#faq)

---

## アイデア

私たちは誰でも、銀行、メール、SNS、サブスクリプションなど、何十ものパスワードを保管しています。**私たちが亡くなった後、それらはどうなるのでしょうか？**家族はアカウントから完全に切り離され、サブスクリプションの解約もアカウントの閉鎖もお金の回収もできなくなります。

Secret Key は、2つの保証を備えた安全な緊急計画を作成することで、この問題を解決します。

| &nbsp; | 問題 | 解決策 |
|---|---|---|
| 🔐 | 所有者の**生存中**の不正アクセス | アクセスの試みごとにパスワード + SMSコードが必要 |
| 💀 | **死後**のアカウントアクセスの喪失 | 指定された人々がShamirのアルゴリズムでパスワードを再構築 |

このシステムは**完全にセルフホスト**されており、データは決してあなたのサーバーから離れません。中央データベースなし、クラウドなし、SMS送信以外の外部依存なし。

<a name="stack"></a>

```
バックエンド     PHP 8+、フラットファイル（JSON + PHP設定）、SQLゼロ
認証             bcrypt cost=10、SMSPlanet APIによるSMS二要素認証
暗号技術         Shamir Secret Sharing（secrets.js）
フロントエンド   純粋なHTML + JS、外部依存ゼロ（オフライン動作）
```

---

## 動作の仕組み

危機的状況では、指定された人々が次の4つのステップを実行します。

```
┌──────────────────────────────────────────────────────────────────────┐
│                                                                      │
│  01  集合             指定された5人中最低3人                         │
│      ─────────       各自が鍵の断片が入ったカードを所持               │
│                                                                      │
│  02  ログイン         Secret Keyカードのログイン情報                  │
│      ──────────      + 割り当てられた電話番号へのSMSコード             │
│                                                                      │
│  03  断片             各自が自分の断片を入力するか、                   │
│      ─────────       カードのQRコードをスキャン                       │
│                                                                      │
│  04  アクセス         パスワードはブラウザ内でローカルに再構築 —       │
│      ────────        サーバーには決して届かない                       │
│                                                                      │
└──────────────────────────────────────────────────────────────────────┘
```

**アカウントに割り当てられた電話がなければ、ログインは不可能です** — カードの情報を知っていても同様です。  
**必要な数の断片がなければ、パスワードの再構築は数学的に不可能です** — サーバーへのアクセス権があっても同様です。

---

## システム概要

<p align="center">
  <a href="https://www.youtube.com/watch?v=omx7mpQD5-M" target="_blank">
    <img src="../img/video-player.webp" alt="Secret Key システムを紹介する動画を見る" width="780">
  </a>
</p>

> [!TIP]
> 🔊 この動画は複数の言語でご覧いただけます — YouTubeプレーヤーの ⚙️ をクリック → **字幕/音声（Audio track）** から、吹き替え言語を選択するか字幕をオンにしてください。

---

## Secret Keyカード

指定された各人は、4つの要素を含む個別化された媒体を受け取ります。

| 要素 | 説明 |
|---|---|
| 🔑 **ログインとパスワード** | 一意のアクセス情報 — 各人が割り当てられた電話番号を持つ独自のアカウントを持つ |
| 🔢 **Shamirの断片** | 16進形式の鍵の断片 — 5つ中の1つで、他がなければ役に立たない |
| ⊞ **QRコード** | 同じ断片をQRコードとして符号化 — スキャンにより手書き転記の誤りを排除 |
| 🌐 **システムアドレス** | あなた自身のSecret KeyインスタンスのURL — ログインパネルに直接つながる |

**媒体の形式は自由です** — 印刷した紙、PDF、プラスチックカードなど。重要なのは上記4つの要素を含んでいることです。

---

## Shamirのアルゴリズム

Secret Keyは、秘密の分割と再構築のために [`secrets.js`](https://github.com/amper5and/secrets.js) ライブラリを使用しています。

> [!NOTE]
> 使用されているライブラリは [iancoleman.io/shamir](https://iancoleman.io/shamir/) で使われているものと**同一**であり、断片の形式は完全に互換性があるため、システム外での独立した検証が可能です。

### 秘密の分割と再構築

```
マスターパスワード: "MyKeePassPassword2024!"
         │
         ▼  5つの断片に分割（閾値: 3）
         │
    ┌────┴──────────────────────────────────┐
    │                                       │
    │  S1: 801a3f9c2e4b7d1...  →  人物A     │  各断片は
    │  S2: 802c8f1a5e9b3d7...  →  人物B     │  必要な数の
    │  S3: 803e2a7f4c1b9d5...  →  人物C     │  残りの断片
    │  S4: 804b6d3e8f2a1c9...  →  人物D     │  がなければ
    │  S5: 805d9f7b2e4c3a1...  →  人物E     │  無意味
    │                                       │
    └────┬──────────────────────────────────┘
         │
         ▼  再構築 — 5つ中任意の3つで十分
         │
    S1 + S2 + S3  →  "MyKeePassPassword2024!"  ✓
    S2 + S4 + S5  →  "MyKeePassPassword2024!"  ✓
    S1            →  秘密に関する情報は一切ない  ✗
```

### 暗号学的特性

秘密は体GF(2⁸)上の多項式の定数項として符号化されます。各断片はその多項式上の一点であり、必要な数の点を知っていれば、ラグランジュ補間により一意に再構築できます。

```
f(x) = a₀ + a₁x + a₂x² + ... + aₖ₋₁xᵏ⁻¹  (mod p)
```

> [!IMPORTANT]
> 必要な数**未満**の断片を持っていても、秘密に関する情報は**一切**得られません（information-theoretic security）。追加された1024ビットのパディングにより、小さな秘密への攻撃を防ぎます。

### 実装パラメータ

| パラメータ | 値 |
|---|---|
| ライブラリ | secrets.js（amper5and） — iancoleman.ioと互換 |
| 断片の形式 | `8` + 2桁16進のx座標 + データ |
| minPad | 1024ビット |
| エンコーディング | UTF-8（str2hex） |
| 再構築 | ブラウザ側のJavaScript — パスワードはサーバーに決して届かない |

### 分割パラメータの選択

| 断片の総数 | 必要な最小数 | シナリオ |
|---|---|---|
| 3 | 2 | 小規模な家族 |
| 5 | 3 | 標準 *（推奨）* |
| 7 | 4 | 大家族 / 企業 |

---

## システムアーキテクチャ

保護されたリソースへのアクセスを得る前に、ユーザーは順に複数の検証層を通過します。

```
ユーザー     →  ログイン + パスワードを入力
                         │
PHPシステム  →  パスワードを検証（bcrypt）、レート制限とCSRFを確認
                         │
SMS / 2FA    →  割り当てられた番号にワンタイムコードを送信
                         │
ユーザー     →  SMSコードを入力
                         │
PHPシステム  →  コードを検証、セッションを作成、必要に応じてデバイスを記憶
                         │
ブラウザ     →  Shamirの断片を受け取り、JS内でローカルに秘密を再構築
```

### システムの層

**認証層**（`/app/`）
- `login.php` — ログインフォーム
- `auth.php` — セッションロジック、bcrypt、2FA、ブルートフォース対策、CSRF、および補助関数 `t()`（UIテキスト）、`md_lite()`（簡易markdown）、`empty_state_box()`
- `verify.php` — SMSコードの検証
- `resend.php` — SMSの再送
- `logout.php` — ログアウト

**アクセス層**（`/decrypt/`）
- `index.php` — JS内でのShamir再構築を伴う復号パネル
- `download.php` — 制御されたファイルダウンロード（セッションが必要、設定から構築されたホワイトリスト、サーバー側のログ記録）
- `log.php` — イベントのログ記録
- `devtools-log.php` — DevTools検査の試みをログ記録（IPごとのレート制限あり）

**データ層**（`/private/` — `public_html`の外側）
- `secret-key.php` — 単一の設定ファイル：人物（`$people`）、ダウンロード可能なファイル（`$downloads`）、手順（`$instructions`）、メール通知（`$email_notify`）、SMS用ドメイン
- `lang.php` — インターフェーステキスト（単一言語のみ、切り替えなし — [インストール](#インストール)を参照）
- `rate-limit.php` — 永続的なレート制限（セッションに依存しないカウンター）
- `rate_limits.json` — IP/アカウントごとのログイン試行カウンター*（自動作成）*
- `trusted_devices.json` — 信頼済みデバイスのトークン*（自動作成）*
- `secret-key.log` — イベントログ
- `moja-baza-hasel.kdbx` *（およびその他のダウンロード可能なファイル）* — `download.php` を通してのみ提供され、HTTPで直接提供されることはない

> [!WARNING]
> すべての機密設定ファイルはサーバーの**公開ディレクトリの外側**に保管されており、Webサーバーの誤設定によってそれらが公開されるリスクはありません。

---

## セキュリティ

このシステムは**8つの独立した保護層**を組み合わせており、1つが破られても、システムへのアクセスは得られません。

| 層 | 仕組み | 詳細 |
|---|---|---|
| 🔒 **パスワード** | bcrypt | cost=10、`$2y$`形式、タイミング攻撃に耐性のある検証 |
| 🛡️ **CSRF** | 64桁16進のトークン | 暗号学的に生成され、状態を変更するすべてのエンドポイント（ログイン、2FA検証、SMS再送、イベントログ）で検証 |
| 🚫 **ブルートフォース** | レート制限 | 15分間の枠でIPごとに3回、アカウントごとに3回の試行；SMSコード誤りは1時間に3回まで。サーバー側の永続的なカウンター（クライアントのセッション／クッキーに依存しないファイル） |
| 📱 **SMSによる2FA** | 6桁のコード | 暗号学的に生成、10分間有効、送信間に60秒のクールダウン |
| 💻 **信頼済みデバイス** | SHA-256、HttpOnly | Secure + SameSite=Strict、`public_html`の外側のファイル、TTL 7日 |
| ⏱️ **セッション** | 自動ログアウト | 明示的なHttpOnly + Secure + SameSite=Strictフラグを持つセッションクッキー；30分のタイムアウト、検証ごとにセッションIDを再生成 |
| 🖥️ **インターフェース保護** | DevTools検知 | 開発者ツールを検知し、DOMを物理的に削除し、IP、REF#、継続時間を記録してインシデントをログ |
| 📥 **制御されたダウンロード** | `download.php` + ホワイトリスト | ダウンロード可能なファイルは`public_html`の外側にあり、アクティブなセッションが必要で、直接URLはなく、常にサーバー側でログ記録 |

---

## インストール

### 要件

- PHP 8.0以上
- Webサーバー（Apache / Nginx）
- [SMSPlanet](https://smsplanet.pl) のアカウント（2FAコード送信用）
- `public_html`の外側のディレクトリへのアクセス

---

### ステップ1 — 設定（オフライン）

`dashboard.html` をブラウザでローカルに開きます。2つのタブがあります。

**設定** — `secret-key.php` ファイルを生成します：
1. SMSPlanetのAPIトークン、SMS送信者名、コードの自動入力用ドメイン（Android/iOS）— ドメインのみで、`@`や`https://`は不要
2. 指定する各人について：ログイン、パスワード、名、姓、電話番号、パネルの所持者リストに表示するかどうか
3. ダウンロード可能なファイル（パスワードデータベース、2FAデータベース、プログラムのインストーラー）— キー、ボタンのラベル、ファイル名
4. パネル用の手順（フォーマット：`**太字**` / `*イタリック*`）
5. オプション：ログインごとのメール通知（受信者アドレス、送信者、パネルへのリンク）
6. 「設定を生成」をクリックし、生成された `secret-key.php` ファイルをダウンロード

**暗号化** — マスターパスワードをShamirの断片に分割します：
1. パスワードデータベースのマスターパスワードを入力
2. 断片の総数と必要な最小数を設定
3. 「断片を生成」をクリック
4. `secret-key-shares.txt` ファイルをダウンロード

> [!TIP]
> 両方のツールは**完全にオフライン**で動作し、データはブラウザから決して離れません。フォームは実行ごとに `secret-key.php` をゼロから生成します — 既存のファイルの読み込みや編集は行いません。

---

### ステップ2 — サーバーへのアップロード

```bash
/home/user/
├── public_html/
│   ├── app/           ← /app/ フォルダの内容
│   └── decrypt/       ← /decrypt/ フォルダの内容
└── private/           ← public_htmlの外側
    ├── secret-key.php  ← 生成された設定ファイル
    ├── lang.php        ← インターフェーステキスト（1言語、ステップ1を参照）
    ├── rate-limit.php  ← リポジトリのシステムファイル（レート制限）
    └── moja-baza-hasel.kdbx  ← あなたのファイル（download.php経由で提供）
```

> [!WARNING]
> `lang.php` は `secret-key.php` **と一緒に**サーバーにアップロードする必要があります。`auth.php` は `require_once` を通じてこれを読み込みます — ファイルが欠けていると、翻訳だけでなくシステム全体がクラッシュします（すべてのページで致命的エラー）。

---

### ステップ3 — 設定へのパス

`auth.php` ファイル内で、設定ファイルへのパスを更新します。

```php
require_once '/home/user/private/secret-key.php';
```

---

### ステップ4 — SMS内容内のドメイン（WebOTP）

送信されるSMSコードの内容は `@ドメイン #コード` という行で終わります — これは [WebOTP API](https://developer.mozilla.org/en-US/docs/Web/API/WebOTP_API) が要求する形式で、これにより電話のブラウザがSMSから手動で書き写すことなく、コード欄を自動で入力します。

ドメインは**ステップ1**で、ダッシュボードのフォーム（「SMS自動入力ドメイン」欄）で設定します — ドメインのみを入力し、`@`や`https://`は不要です（ダッシュボードが自動的に`@`を追加します）。設定には次のように反映されます。

```php
define('SMS_AUTOFILL_DOMAIN', '@your-domain.jp');
```

> [!WARNING]
> ドメインは、実際にシステムをホストしているドメインと**正確に**一致する必要があります — 一致しない場合、WebOTPはSMSを無視し、自動入力は機能しません。コードは手動入力でも届き機能しますが、この便利さは失われます。

---

### ステップ5 — カードの配布

指定した各人のために、次を含む媒体を用意します。
- ログインとパスワード（「設定」タブから）
- Shamirの断片（`secret-key-shares.txt` ファイルから）
- 任意：同じ断片を含むQRコード
- あなたのシステムインスタンスのアドレス

---

## ファイル構成

```
secret-key/
│
├── 📁 app/                        # 公開 — ログインシステム
│   ├── 📁 decrypt/                # 保護 — ユーザーパネル
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
├── 📁 dashboard/                  # 設定ツール（オフライン）
│   ├── card-back.js
│   ├── card-front.js
│   ├── dashboard.html
│   ├── favicon.ico
│   ├── generate-card.html
│   ├── generate-hash.html
│   └── generate-shamir.html
│
├── 📁 img/                        # 画像リソース
│
└── 📁 private/                    # public_htmlの外側 — 設定ファイル
    ├── .htaccess
    ├── demo-baza-hasel.txt         # サンプルのダウンロード可能ファイル（自分のものに置き換えてください）
    ├── lang.php                    # インターフェーステキスト（1言語）
    ├── rate-limit.php
    └── secret-key.php
```

---

## よくある質問

<details>
<summary><strong>Secret Keyカードを失くしたらどうなりますか？</strong></summary>

カード単独では、そのアカウントに割り当てられた電話へのアクセスがなければ無意味です — ログインにはSMS認証が必要です。リスクは限定的ですが、所有者に通知し、新しい断片のセットで新しい設定を生成することを検討すべきです。

</details>

<details>
<summary><strong>カード1枚だけで自分でパスワードを読み取れますか？</strong></summary>

いいえ。それは数学的に不可能です。単一の断片は秘密に関する情報を一切明らかにしません — これは information-theoretic security と呼ばれるアルゴリズムの特性です。必要な数の断片を集めることによってのみ、マスターパスワードを再構築できます。

</details>

<details>
<summary><strong>指定された人の一人が亡くなったり、連絡が取れなくなったりした場合はどうなりますか？</strong></summary>

このシステムは冗長性を持って設計されています — 必要最小限の数の断片（例：5つ中3つ）を集めるだけで十分です。残りの人々が集まれる限り、1人か2人の連絡不能はこの緊急手続きを妨げません。

</details>

<details>
<summary><strong>復号中にパスワードがサーバーに届くことはありますか？</strong></summary>

いいえ。Shamirの断片からのパスワードの再構築は**完全にブラウザ側**（JavaScript）で行われます。サーバーはユーザーの認証にのみ使用され、秘密自体は決してサーバーを離れません。

</details>

<details>
<summary><strong>KeePassXC以外のパスワードマネージャーでシステムを使用できますか？</strong></summary>

はい。Secret Keyは、使用するマネージャーに関わらず**任意のマスターパスワード**を保存・再構築します。マスターパスワードをサポートするあらゆるプログラムと互換性があります：KeePassXC、Bitwarden、1Passwordなど。

</details>

<details>
<summary><strong>閾値の選び方は？いくつの断片が必要ですか？</strong></summary>

閾値が高いほどセキュリティは高まりますが、危機的状況で全員を集めるのはより困難になります。標準的な使用における推奨される折り合いは**5つ中3つ**です — 2人の連絡不能を許容しつつ、良好な保護レベルを維持します。

</details>

<details>
<summary><strong>カードのログイン情報はどのくらい有効ですか？</strong></summary>

無期限です — 所有者が新しい設定を生成し、サーバー上の `secret-key.php` ファイルを置き換えない限り。この操作の後、古いカードは機能しなくなり、指定されたすべての人に新しいカードを配布する必要があります。

</details>

---

<div align="center">

Copyright © 2026 · [karpierz.me](https://karpierz.me)

</div>
