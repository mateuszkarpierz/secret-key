# 🔐 Política de seguridad

<p align="center">
  <a href="../SECURITY.md">🇵🇱 Polski</a> •
  <a href="SECURITY_EN.md">🇬🇧 English</a> •
  <kbd>🇪🇸 Español</kbd> •
  <a href="SECURITY_DE.md">🇩🇪 Deutsch</a> •
  <a href="SECURITY_PT_BR.md">🇧🇷 Português (Brasil)</a> •
  <a href="SECURITY_FR.md">🇫🇷 Français</a> •
  <a href="SECURITY_ZH.md">🇨🇳 简体中文</a> •
  <a href="SECURITY_AR.md">🇸🇦 العربية</a> •
  <a href="SECURITY_HI.md">🇮🇳 हिन्दी</a> •
  <a href="SECURITY_JA.md">🇯🇵 日本語</a> •
  <a href="SECURITY_RU.md">🇷🇺 Русский</a> •
  <a href="SECURITY_UK.md">🇺🇦 Українська</a>
</p>

---

Los errores normales, las erratas en la documentación o las sugerencias de mejora repórtalos normalmente como un [Issue en GitHub](https://github.com/mateuszkarpierz/secret-key/issues) — es la vía estándar, no hay nada secreto en ello. Esta página trata únicamente sobre **vulnerabilidades de seguridad** (ver el [alcance de los reportes](#-alcance-de-los-reportes) más abajo) — esas, por favor, **no** las reportes públicamente.

---

## 📬 Cómo reportar vulnerabilidades de seguridad

> [!CAUTION]
> Reporta las vulnerabilidades de seguridad únicamente en privado, a **dev@secretkey.website** — nunca como un Issue público en GitHub. Revelar públicamente un exploit antes de publicar un parche pone en riesgo a todos los que tienen el sistema desplegado.

### Cómo reportar

Envía una descripción detallada a: **dev@secretkey.website**

### Qué incluir en el reporte

| | Elemento |
|---|---|
| 📝 | Descripción de la vulnerabilidad y su impacto potencial |
| 🔁 | Pasos para reproducirla (prueba de concepto) |
| 🏷️ | Versión del sistema afectada |
| 💡 | Propuesta de solución *(opcional)* |

### Qué esperar

| Tiempo | Respuesta |
|---|---|
| **48h** | Confirmación de recepción del reporte |
| **7 días** | Información sobre el progreso |
| Tras la corrección | Agradecimiento público *(si así lo deseas)* |

---

## 🏷️ Versiones compatibles

Siempre se da soporte a la **última versión publicada**. Se aceptan reportes sobre versiones anteriores, pero primero se pide actualizar al último release — algunas vulnerabilidades podrían ya estar corregidas.

Puedes encontrar la versión actual en la [página de Releases](https://github.com/mateuszkarpierz/secret-key/releases).

---

## 🎯 Alcance de los reportes

### ✅ Dentro del alcance

- Elusión de la autenticación (bcrypt, 2FA)
- Vulnerabilidades CSRF a pesar de las protecciones aplicadas
- Posibilidad de reconstruir el secreto de Shamir sin el número requerido de fragmentos
- Exposición de datos del directorio `/private/`
- Descarga de archivos sensibles (p. ej. la base de contraseñas) evitando el inicio de sesión, p. ej. mediante una URL directa
- Vulnerabilidad a ataques de fuerza bruta a pesar del rate limiting
- XSS, inyección SQL *(aunque el sistema no usa SQL)*

### ❌ Fuera del alcance

- Ataques que requieran acceso físico al servidor
- Ataques de ingeniería social contra los titulares de tarjetas Secret Key
- Problemas de configuración del servidor web por parte del usuario
- Reportes generados automáticamente por escáneres sin PoC

---

## 🛡️ Buenas prácticas de despliegue

> [!WARNING]
> Secret Key es un sistema **autoalojado (self-hosted)** — la seguridad de tu instalación depende en gran medida de la configuración de tu propio servidor.

| | Práctica |
|---|---|
| 📁 | Mantén el directorio `/private/` **fuera** de `public_html` |
| 🔒 | Usa HTTPS (SSL/TLS) en el servidor |
| 🔄 | Actualiza PHP regularmente a la última versión 8.x |
| 🚫 | Nunca expongas el archivo `secret-key.php` públicamente |
| 📥 | No enlaces directamente a archivos sensibles en el directorio público — sírvelos a través de `download.php` (requiere sesión, registra las descargas) |
| 🔑 | Usa contraseñas fuertes y únicas para cada cuenta |

---

<div align="center">

Copyright © 2026 · [karpierz.me](https://karpierz.me)

</div>
