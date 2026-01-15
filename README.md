# Sentry Mini-Projekt 🚀

Ein Demo PHP-Projekt mit **Sentry Integration** für Error Tracking und Performance Monitoring.

## 📋 Anforderungen

- PHP 8.0+
- Composer
- Docker (optional)

## 🚀 Quick Start (Lokal)

### 1. Repository klonen / Projekt vorbereiten
```bash
cd sentry-mini
```

### 2. Konfiguration setup
```bash
# .env Datei aus .env.example erstellen und anpassen
cp .env.example .env
```

**`.env` Datei anpassen mit:**
```env
SENTRY_DSN=https://dein-sentry-dsn@...
SENTRY_ENVIRONMENT=local-windows
SENTRY_RELEASE=mini-project@1.0.0
SENTRY_TRACES_SAMPLE_RATE=1.0
SENTRY_PROFILES_SAMPLE_RATE=1.0
```

### 3. Dependencies installieren
```bash
composer install
```

### 4. Server starten
```bash
php -S localhost:8080 -t public
```

### 5. Dashboard öffnet automatisch
- 🎨 **http://localhost:8080/** ← Dashboard startet automatisch!

---

## 📋 Environment Variables

Siehe [.env.example](.env.example) für alle verfügbaren Optionen:

| Variable | Default | Beschreibung |
|----------|---------|-------------|
| `SENTRY_DSN` | - | Sentry Error Reporting URL (erforderlich) |
| `SENTRY_ENVIRONMENT` | `development` | Environment Tag (development, staging, production) |
| `SENTRY_RELEASE` | `mini-project@1.0.0` | Release Version |
| `SENTRY_TRACES_SAMPLE_RATE` | `1.0` | Trace Sampling (0.0-1.0) |
| `SENTRY_PROFILES_SAMPLE_RATE` | `1.0` | Profile Sampling (0.0-1.0) |
| `SENTRY_MAX_BREADCRUMBS` | `50` | Max Breadcrumbs (1-100) |

---

## 🐳 Docker (One-Liner)

### Docker mit .env
```bash
docker build -t sentry-mini . && docker run --rm -it -p 8080:8080 --env-file .env sentry-mini
```

**Flags erklärt:**
- `--rm` – Container wird nach Exit automatisch gelöscht
- `-it` – Interactive + TTY (für Logs und Ctrl+C)
- `-p 8080:8080` – Port Mapping

Dann öffne: http://localhost:8080/

### Dashboard Features

Das **Web Dashboard** bietet:
- 🎯 5 verschiedene Demo-Modi in schöner UI
- ⚡ AJAX-Requests – Seite crasht nicht!
- 📊 Live-Ergebnisse mit Timestamps
- 🎨 Beautiful Responsive Design
- ✓ Status-Indikatoren

### API - Direkte Requests (ohne Dashboard)
```bash
# Normal (Success)
curl http://localhost:8080/?mode=normal

# Exception
curl http://localhost:8080/?mode=error

# Fatal Error / Crash
curl http://localhost:8080/?mode=crash

# User Warning
curl http://localhost:8080/?mode=warning

# Deprecated Warning
curl http://localhost:8080/?mode=deprecated
```

---

## 🎯 Demo-Features

## 📁 Projektstruktur

```
sentry-mini/
│
├── public/
│   └── index.php              # Entry Point
│
├── src/
│   └── Services/
│       └── DemoService.php    # Business Logic
│
├── bootstrap.php               # Sentry Init
├── composer.json
├── Dockerfile                  # Docker-Image
├── .gitignore
└── README.md
```

---

## ⚙️ Konfiguration

Die Sentry-Konfiguration befindet sich in [bootstrap.php](bootstrap.php):

```php
\Sentry\init([
    'dsn' => 'https://...',
    'traces_sample_rate' => 1.0,  // 100% Tracing
    'environment' => 'local-windows',
    'release' => 'mini-project@1.0.0',
]);
```

### Environment-Variablen (optional)

```bash
SENTRY_DSN=https://...
SENTRY_ENV=production
SENTRY_RELEASE=1.0.0
```

---

## 🎯 Demo-Features

### Tags & Context
- **System Tags:** OS, PHP SAPI, Server, Hostname
- **Runtime Context:** PHP-Version, Memory Limit, Execution Time
- **Request Context:** Method, Host, User-Agent, IP-Address
- **System Context:** Load Average, Disk Space, Memory Usage
- **User Context:** Demo User mit IP

### Tracing & Spans
- 📊 **Database Span** – SELECT query mit 1250 rows (300ms)
- 💾 **Cache Span** – Redis cache check (50ms)
- 🌐 **API Span** – External API call mit Status 200 (500ms)
- ⚙️ **Processing Span** – Data transformation (200ms)

### Breadcrumbs
- App Lifecycle Events
- HTTP Requests
- Database Queries
- API Calls
- Exception Handling

### Error Modes
- `?mode=normal` – Success Response
- `?mode=error` – Wirft Exception
- `?mode=crash` – Fatal Error
- `?mode=warning` – User Warning
- `?mode=deprecated` – Deprecated Warning

### Profiling
- Performance Profiling aktiviert (traces_sample_rate: 1.0)
- Profiles Sample Rate: 100%
- Vollständige Stack Traces

---

## 📊 Sentry Features in Aktion

### Success
```json
{
  "status": "ok",
  "time": "2026-01-15T10:30:45+00:00"
}
```

### Error
```json
{
  "error": "Test-Exception aus Mini-Projekt"
}
```

---

## 🔗 Links

- [Sentry PHP SDK](https://docs.sentry.io/platforms/php/)
- [Performance Tracing](https://docs.sentry.io/product/performance/)

---

## 📝 Lizenz

MIT
