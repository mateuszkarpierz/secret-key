<div align="center">

<img src="../img/secret-key-logo.svg" width="380" alt="Secret Key Logo">

</div>

---

<p align="center">
  <a href="../README.md">🇵🇱 Polski</a> •
  <a href="README_EN.md">🇬🇧 English</a> •
  <kbd>🇪🇸 Español</kbd> •
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

### Un sistema criptográfico de acceso de emergencia a tu base de contraseñas

<br>

[![PHP](https://img.shields.io/badge/PHP-8%2B-7c3aed?style=flat-square&logo=php&logoColor=white)](https://php.net)
[![Zero SQL](https://img.shields.io/badge/Base_de_datos-Zero_SQL-4ade80?style=flat-square)](#stack)
[![Self-hosted](https://img.shields.io/badge/Alojamiento-Self--hosted-c084fc?style=flat-square)](#instalación)
[![2FA](https://img.shields.io/badge/2FA-SMS_·_SMSPlanet-38bdf8?style=flat-square)](#seguridad)
[![Shamir](https://img.shields.io/badge/Criptograf%C3%ADa-Shamir_SSS-f472b6?style=flat-square)](#algoritmo-de-shamir)
[![License](https://img.shields.io/badge/Licencia-MIT-818cf8?style=flat-square)](../LICENSE)

<br>

**¿Qué pasa con tus cuentas después de tu muerte?**

Secret Key divide criptográficamente la contraseña maestra de tu base de contraseñas entre personas de confianza.  
Nadie la conoce por sí sola — solo juntas, en un momento acordado, pueden reconstruirla.

<br>

![Secret Key Demo](../img/SecretKeyGif.gif)

<br>

[**→ Ver la demo**](https://app.secretkey.website) &nbsp;·&nbsp; [**Sitio del proyecto**](https://secretkey.website) &nbsp;·&nbsp; [**Documentación**](https://secretkey.website/docs)

</div>

---

## Índice

- 01 · 🔑 [Idea](#idea)
- 02 · ⚙️ [Cómo funciona](#cómo-funciona)
- 03 · 🎬 [Presentación del sistema](#presentación-del-sistema)
- 04 · 💳 [Tarjeta Secret Key](#tarjeta-secret-key)
- 05 · 🔐 [Algoritmo de Shamir](#algoritmo-de-shamir)
- 06 · 🏗️ [Arquitectura del sistema](#arquitectura-del-sistema)
- 07 · 🛡️ [Seguridad](#seguridad)
- 08 · 🚀 [Instalación](#instalación)
- 09 · 📁 [Estructura de archivos](#estructura-de-archivos)
- 10 · ❓ [Preguntas frecuentes](#faq)

---

## Idea

Cada uno de nosotros guarda docenas de contraseñas — de bancos, correo, redes sociales, suscripciones. **¿Qué pasa con ellas tras nuestra muerte?** La familia queda excluida de las cuentas, sin poder cancelar suscripciones, cerrar cuentas ni recuperar dinero.

Secret Key resuelve este problema creando un plan de emergencia seguro con dos garantías:

| &nbsp; | Problema | Solución |
|---|---|---|
| 🔐 | Acceso no autorizado **en vida** del propietario | Cada intento de acceso requiere una contraseña + un código SMS |
| 💀 | Pérdida de acceso a las cuentas **tras la muerte** | Las personas designadas reconstruyen la contraseña con el algoritmo de Shamir |

El sistema es **totalmente autoalojado** — los datos nunca salen de tu servidor. Sin base de datos central, sin nube, sin dependencias externas salvo el envío de SMS.

<a name="stack"></a>

```
Backend         PHP 8+, archivos planos (JSON + config PHP), cero SQL
Autorización    bcrypt cost=10, 2FA por SMS vía la API de SMSPlanet
Criptografía    Shamir Secret Sharing (secrets.js)
Frontend        HTML + JS puro, cero dependencias externas (sin conexión)
```

---

## Cómo funciona

En una situación de crisis, las personas designadas realizan cuatro pasos:

```
┌──────────────────────────────────────────────────────────────────────┐
│                                                                      │
│  01  REUNIÓN         Mín. 3 de 5 personas designadas                 │
│      ─────────       Cada una tiene una tarjeta con un fragmento      │
│                                                                      │
│  02  INICIO SESIÓN   Credenciales de la tarjeta Secret Key            │
│      ──────────      + un código SMS al número de teléfono asignado  │
│                                                                      │
│  03  FRAGMENTOS      Cada persona introduce su fragmento o            │
│      ─────────       escanea el código QR de la tarjeta              │
│                                                                      │
│  04  ACCESO          Contraseña reconstruida localmente en el         │
│      ────────        navegador — nunca llega al servidor              │
│                                                                      │
└──────────────────────────────────────────────────────────────────────┘
```

**Sin el teléfono asignado a la cuenta, iniciar sesión es imposible**, incluso conociendo los datos de la tarjeta.  
**Sin el número requerido de fragmentos, reconstruir la contraseña es matemáticamente imposible**, incluso con acceso al servidor.

---

## Presentación del sistema

<p align="center">
  <a href="https://www.youtube.com/watch?v=omx7mpQD5-M" target="_blank">
    <img src="../img/video-player.webp" alt="Ver un vídeo de presentación del sistema Secret Key" width="780">
  </a>
</p>

> [!TIP]
> 🔊 El vídeo está disponible en varios idiomas — haz clic en ⚙️ en el reproductor de YouTube → **Subtítulos/Audio (pista de audio)**, para elegir el idioma del doblaje o activar los subtítulos.

---

## Tarjeta Secret Key

Cada persona designada recibe un soporte personalizado con cuatro elementos:

| Elemento | Descripción |
|---|---|
| 🔑 **Usuario y contraseña** | Credenciales de acceso únicas — cada persona tiene su propia cuenta con un número de teléfono asignado |
| 🔢 **Fragmento Shamir** | Un fragmento de clave en formato hexadecimal — uno de cinco, inútil sin los demás |
| ⊞ **Código QR** | El mismo fragmento codificado como QR — escanearlo elimina errores de transcripción manual |
| 🌐 **Dirección del sistema** | La URL de tu propia instancia de Secret Key — lleva directamente al panel de inicio de sesión |

**El formato del soporte es libre** — una hoja impresa, un PDF, una tarjeta de plástico. Lo importante es que contenga los cuatro elementos anteriores.

---

## Algoritmo de Shamir

Secret Key utiliza la biblioteca [`secrets.js`](https://github.com/amper5and/secrets.js) para dividir y reconstruir el secreto.

> [!NOTE]
> La biblioteca utilizada es **idéntica a la empleada por [iancoleman.io/shamir](https://iancoleman.io/shamir/)** — el formato de los fragmentos es totalmente compatible, lo que permite una verificación independiente fuera del sistema.

### División y reconstrucción del secreto

```
Contraseña maestra: "MyKeePassPassword2024!"
         │
         ▼  División en 5 fragmentos (umbral: 3)
         │
    ┌────┴──────────────────────────────────┐
    │                                       │
    │  S1: 801a3f9c2e4b7d1...  →  Persona A │  Cada fragmento
    │  S2: 802c8f1a5e9b3d7...  →  Persona B │  es inútil sin
    │  S3: 803e2a7f4c1b9d5...  →  Persona C │  el número
    │  S4: 804b6d3e8f2a1c9...  →  Persona D │  requerido de
    │  S5: 805d9f7b2e4c3a1...  →  Persona E │  los demás
    │                                       │
    └────┬──────────────────────────────────┘
         │
         ▼  Reconstrucción — bastan 3 cualesquiera de 5
         │
    S1 + S2 + S3  →  "MyKeePassPassword2024!"  ✓
    S2 + S4 + S5  →  "MyKeePassPassword2024!"  ✓
    S1            →  ninguna información sobre el secreto  ✗
```

### Propiedades criptográficas

El secreto se codifica como el término independiente de un polinomio sobre el cuerpo GF(2⁸). Cada fragmento es un punto de ese polinomio — conociendo el número requerido de puntos, puede reconstruirse de forma única mediante interpolación de Lagrange:

```
f(x) = a₀ + a₁x + a₂x² + ... + aₖ₋₁xᵏ⁻¹  (mod p)
```

> [!IMPORTANT]
> Tener **menos del número requerido** de fragmentos no proporciona **ninguna** información sobre el secreto (information-theoretic security). El padding adicional de 1024 bits impide ataques contra secretos pequeños.

### Parámetros de implementación

| Parámetro | Valor |
|---|---|
| Biblioteca | secrets.js (amper5and) — compatible con iancoleman.io |
| Formato de fragmento | `8` + coordenada x en 2 hex + datos |
| minPad | 1024 bits |
| Codificación | UTF-8 (str2hex) |
| Reconstrucción | JavaScript en el lado del navegador — la contraseña nunca llega al servidor |

### Elección de los parámetros de división

| Total de fragmentos | Mínimo requerido | Escenario |
|---|---|---|
| 3 | 2 | Familia pequeña |
| 5 | 3 | Estándar *(recomendado)* |
| 7 | 4 | Familia grande / empresa |

---

## Arquitectura del sistema

El usuario pasa por sucesivas capas de verificación antes de obtener acceso a los recursos protegidos:

```
Usuario       →  introduce usuario + contraseña
                         │
Sistema PHP   →  verifica la contraseña (bcrypt), comprueba el rate limiting y el CSRF
                         │
SMS / 2FA     →  envía un código de un solo uso al número asignado
                         │
Usuario       →  introduce el código SMS
                         │
Sistema PHP   →  verifica el código, crea una sesión, opcionalmente recuerda el dispositivo
                         │
Navegador     →  recibe los fragmentos Shamir, reconstruye el secreto localmente en JS
```

### Capas del sistema

**Capa de autorización** (`/app/`)
- `login.php` — el formulario de inicio de sesión
- `auth.php` — lógica de sesión, bcrypt, 2FA, protección contra fuerza bruta, CSRF, además de las funciones auxiliares `t()` (textos de interfaz), `md_lite()` (markdown-lite), `empty_state_box()`
- `verify.php` — verificación del código SMS
- `resend.php` — reenvío del SMS
- `logout.php` — cierre de sesión

**Capa de acceso** (`/decrypt/`)
- `index.php` — el panel de descifrado con reconstrucción Shamir en JS
- `download.php` — descargas de archivos controladas (requiere sesión, lista blanca construida a partir de la configuración, registro del lado del servidor)
- `log.php` — registro de eventos
- `devtools-log.php` — registro de intentos de inspección con DevTools (con limitación de tasa por IP)

**Capa de datos** (`/private/` — fuera de `public_html`)
- `secret-key.php` — un único archivo de configuración: personas (`$people`), archivos descargables (`$downloads`), instrucciones (`$instructions`), notificación por correo (`$email_notify`), dominio SMS
- `lang.php` — textos de interfaz (un solo idioma, sin selector — ver [Instalación](#instalación))
- `rate-limit.php` — limitación de tasa persistente (contadores independientes de la sesión)
- `rate_limits.json` — contadores de intentos de inicio de sesión por IP/cuenta *(se crea automáticamente)*
- `trusted_devices.json` — tokens de dispositivos de confianza *(se crea automáticamente)*
- `secret-key.log` — registros de eventos
- `moja-baza-hasel.kdbx` *(y otros archivos descargables)* — servidos únicamente a través de `download.php`, nunca directamente por HTTP

> [!WARNING]
> Todos los archivos de configuración sensibles se almacenan **fuera del directorio público** del servidor — una configuración incorrecta del servidor web no supone riesgo de exponerlos.

---

## Seguridad

El sistema combina **ocho capas de protección independientes** — comprometer una no otorga acceso al sistema.

| Capa | Mecanismo | Detalles |
|---|---|---|
| 🔒 **Contraseñas** | bcrypt | cost=10, formato `$2y$`, verificación resistente a ataques de temporización |
| 🛡️ **CSRF** | Token de 64 hex | Generado criptográficamente, verificado en cada endpoint que cambia el estado (inicio de sesión, verificación 2FA, reenvío de SMS, registro de eventos) |
| 🚫 **Fuerza bruta** | Rate limiting | 3 intentos/IP + 3 intentos/cuenta en una ventana de 15 min; 3 códigos SMS erróneos/hora. Contadores persistentes del lado del servidor (un archivo, independiente de la sesión/cookies del cliente) |
| 📱 **2FA por SMS** | Código de 6 dígitos | Generado criptográficamente, válido durante 10 min, 60 s de espera entre envíos |
| 💻 **Dispositivos de confianza** | SHA-256, HttpOnly | Secure + SameSite=Strict, archivo fuera de `public_html`, TTL de 7 días |
| ⏱️ **Sesión** | Cierre de sesión automático | Cookie de sesión con indicadores explícitos HttpOnly + Secure + SameSite=Strict; tiempo de espera de 30 min, regeneración del ID de sesión tras cada verificación |
| 🖥️ **Protección de la interfaz** | Detección de DevTools | Detecta las herramientas de desarrollo, elimina físicamente el DOM, registra el incidente con IP, REF# y duración |
| 📥 **Descargas controladas** | `download.php` + lista blanca | Los archivos descargables están fuera de `public_html`; se requiere una sesión activa, sin URL directa, siempre registrado del lado del servidor |

---

## Instalación

### Requisitos

- PHP 8.0+
- Un servidor web (Apache / Nginx)
- Una cuenta en [SMSPlanet](https://smsplanet.pl) (para el envío de códigos 2FA)
- Acceso a un directorio fuera de `public_html`

---

### Paso 1 — Configuración (sin conexión)

Abre `dashboard.html` localmente en tu navegador. Tiene dos pestañas:

**Configuración** — genera el archivo `secret-key.php`:
1. El token de la API de SMSPlanet, el nombre del remitente del SMS y el dominio para autocompletar el código (Android/iOS) — solo el dominio, sin `@` ni `https://`
2. Para cada persona designada: usuario, contraseña, nombre, apellido, número de teléfono y si debe ser visible en la lista de titulares en el panel
3. Archivos descargables (base de contraseñas, base 2FA, instalador del programa) — clave, etiqueta del botón, nombre de archivo
4. Los pasos de instrucciones para el panel (formato: `**negrita**` / `*cursiva*`)
5. Opcional: una notificación por correo en cada inicio de sesión (dirección del destinatario, remitente, enlace al panel)
6. Haz clic en «Generar configuración» y descarga el archivo `secret-key.php` generado

**Encriptación** — divide la contraseña maestra en fragmentos Shamir:
1. Introduce la contraseña maestra de la base de contraseñas
2. Establece el número total de fragmentos y el mínimo requerido
3. Haz clic en «Generar fragmentos»
4. Descarga el archivo `secret-key-shares.txt`

> [!TIP]
> Ambas herramientas funcionan **completamente sin conexión** — ningún dato sale del navegador. El formulario genera `secret-key.php` desde cero en cada ejecución — no carga ni edita un archivo existente.

---

### Paso 2 — Subida al servidor

```bash
/home/user/
├── public_html/
│   ├── app/           ← contenido de la carpeta /app/
│   └── decrypt/       ← contenido de la carpeta /decrypt/
└── private/           ← FUERA de public_html
    ├── secret-key.php  ← el archivo de configuración generado
    ├── lang.php        ← textos de interfaz (un idioma, ver el paso 1)
    ├── rate-limit.php  ← un archivo del sistema del repositorio (rate limiting)
    └── moja-baza-hasel.kdbx  ← tus archivos (servidos vía download.php)
```

> [!WARNING]
> `lang.php` debe subirse al servidor **junto con** `secret-key.php`. `auth.php` lo carga mediante `require_once` — la falta del archivo hace caer todo el sistema (error fatal en cada página), no solo las traducciones.

---

### Paso 3 — Ruta a la configuración

En el archivo `auth.php`, actualiza la ruta al archivo de configuración:

```php
require_once '/home/user/private/secret-key.php';
```

---

### Paso 4 — Dominio en el contenido del SMS (WebOTP)

El contenido del código SMS enviado termina con la línea `@dominio #código` — este es el formato requerido por la [API WebOTP](https://developer.mozilla.org/en-US/docs/Web/API/WebOTP_API), gracias a la cual el navegador del teléfono rellena por sí solo el campo del código, sin necesidad de copiarlo manualmente del SMS.

El dominio se configura en el **Paso 1**, en el formulario del dashboard (campo «Dominio para autocompletar SMS») — introduces solo el dominio, sin `@` ni `https://` (el dashboard añade la `@` automáticamente). Se incorpora a la configuración así:

```php
define('SMS_AUTOFILL_DOMAIN', '@tu-dominio.es');
```

> [!WARNING]
> El dominio debe coincidir **exactamente** con aquel bajo el cual alojas el sistema — de lo contrario WebOTP ignorará el SMS y el autocompletado no funcionará. El código igualmente llegará y funcionará al introducirlo manualmente, solo sin esa comodidad.

---

### Paso 5 — Distribución de las tarjetas

Para cada persona designada, prepara un soporte con:
- usuario y contraseña (desde la pestaña Configuración)
- un fragmento Shamir (desde el archivo `secret-key-shares.txt`)
- opcionalmente: un código QR con el mismo fragmento
- la dirección de tu instancia del sistema

---

## Estructura de archivos

```
secret-key/
│
├── 📁 app/                        # Público — sistema de inicio de sesión
│   ├── 📁 decrypt/                # Protegido — panel de usuario
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
├── 📁 dashboard/                  # Herramientas de configuración (sin conexión)
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
└── 📁 private/                    # Fuera de public_html — archivos de configuración
    ├── .htaccess
    ├── demo-baza-hasel.txt         # Archivo descargable de ejemplo (sustitúyelo por el tuyo)
    ├── lang.php                    # Textos de interfaz (un idioma)
    ├── rate-limit.php
    └── secret-key.php
```

---

## Preguntas frecuentes

<details>
<summary><strong>¿Qué pasa si pierdo mi tarjeta Secret Key?</strong></summary>

La tarjeta por sí sola es inútil sin acceso al teléfono asignado a esa cuenta — iniciar sesión requiere verificación por SMS. El riesgo es limitado, pero el propietario debería ser informado y considerar generar una nueva configuración con un nuevo conjunto de fragmentos.

</details>

<details>
<summary><strong>¿Puedo leer la contraseña yo solo con una sola tarjeta?</strong></summary>

No. Es matemáticamente imposible. Un único fragmento no revela ninguna información sobre el secreto — es una propiedad del algoritmo llamada information-theoretic security. Solo al reunir el número requerido de fragmentos se puede reconstruir la contraseña maestra.

</details>

<details>
<summary><strong>¿Qué pasa si una de las personas designadas muere o no está disponible?</strong></summary>

El sistema está diseñado con redundancia — basta con reunir el número mínimo requerido de fragmentos (p. ej. 3 de 5). La indisponibilidad de una o dos personas no bloquea el procedimiento de emergencia, siempre que las demás puedan reunirse.

</details>

<details>
<summary><strong>¿La contraseña llega al servidor durante el descifrado?</strong></summary>

No. La reconstrucción de la contraseña a partir de los fragmentos Shamir ocurre **completamente en el lado del navegador** (JavaScript). El servidor solo se usa para autenticar al usuario — el secreto en sí nunca lo abandona.

</details>

<details>
<summary><strong>¿Puedo usar el sistema con un gestor de contraseñas distinto de KeePassXC?</strong></summary>

Sí. Secret Key almacena y reconstruye **cualquier contraseña maestra** — independientemente del gestor utilizado. Compatible con cualquier programa que admita una contraseña maestra: KeePassXC, Bitwarden, 1Password y otros.

</details>

<details>
<summary><strong>¿Cómo elijo el umbral — cuántos fragmentos se requieren?</strong></summary>

Cuanto mayor sea el umbral, mayor será la seguridad — pero también más difícil reunir a todos en una situación de crisis. El compromiso recomendado para un uso estándar es **3 de 5** — tolera la indisponibilidad de dos personas manteniendo un buen nivel de protección.

</details>

<details>
<summary><strong>¿Cuánto tiempo son válidas las credenciales de inicio de sesión de una tarjeta?</strong></summary>

Indefinidamente — mientras el propietario no genere una nueva configuración ni reemplace el archivo `secret-key.php` en el servidor. Tras esa operación, las tarjetas antiguas dejan de funcionar y es necesario distribuir otras nuevas a todas las personas designadas.

</details>

---

<div align="center">

Copyright © 2026 · [karpierz.me](https://karpierz.me)

</div>
