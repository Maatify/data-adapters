
---

> 🔗 [بالعربي 🇸🇦 ](env-ar.md)

---
# ✅ **Environment Variables (Updated for Phase 10 – Final Version)**

> ⚠️ **Starting from Phase 10, DSN variables take full priority and replace host/port/user/pass when available.**
> Legacy variables remain **supported** but are now **deprecated**.

---

## 🧩 Primary Variables (DSN-First)

| Variable                      | Description                                                    |
|-------------------------------|----------------------------------------------------------------|
| `MYSQL_DSN` / `MYSQL_*_DSN`   | Full MySQL DSN string (primary configuration).                 |
| `MONGO_DSN` / `MONGO_*_DSN`   | Full MongoDB DSN string.                                       |
| `REDIS_DSN` / `REDIS_*_DSN`   | Full Redis DSN string.                                         |
| `MYSQL_USER` / `MYSQL_*_USER` | MySQL username (used only if DSN doesn't include credentials). |
| `MYSQL_PASS` / `MYSQL_*_PASS` | MySQL password.                                                |
| `MONGO_USER` / `MONGO_*_USER` | MongoDB username.                                              |
| `MONGO_PASS` / `MONGO_*_PASS` | MongoDB password.                                              |
| `REDIS_PASS` / `REDIS_*_PASS` | Redis password (consistent naming with Phase 10).              |
| `APP_ENV`                     | Application environment (`local`, `testing`, `production`).    |
| `LOG_PATH`                    | Main log directory.                                            |
| `ADAPTER_LOG_PATH`            | Adapter-level logs (per driver).                               |
| `METRICS_ENABLED`             | Enable Prometheus/JSON metrics exporter.                       |
| `METRICS_EXPORT_FORMAT`       | `prometheus`, `json`, or `none`.                               |
| `METRICS_SAMPLING_RATE`       | Fraction of sampled requests (0.0–1.0).                        |

---

## ⚠️ Deprecated Variables

> Still supported, but **DSN variables override them in Phase 10+**

| Deprecated Variables                   | Replaced by   |
|----------------------------------------|---------------|
| `MYSQL_HOST`, `MYSQL_PORT`, `MYSQL_DB` | → `MYSQL_DSN` |
| `MONGO_HOST`, `MONGO_PORT`, `MONGO_DB` | → `MONGO_DSN` |
| `REDIS_HOST`, `REDIS_PORT`             | → `REDIS_DSN` |

---

# 🧠 **Example `.env` (Fully Updated for Phase 10)**

```env
# ----------------------------------------------------------
# 🔵 MYSQL ADAPTER (DSN-First)
# ----------------------------------------------------------
MYSQL_MAIN_DSN="mysql:host=127.0.0.1;dbname=maatify_main;charset=utf8mb4"
MYSQL_MAIN_USER=root
MYSQL_MAIN_PASS=

# Optional fallback (deprecated)
MYSQL_HOST=127.0.0.1
MYSQL_PORT=3306
MYSQL_DB=maatify_main
MYSQL_USER=root
MYSQL_PASS=


# ----------------------------------------------------------
# 🟢 MONGODB ADAPTER (DSN-First)
# ----------------------------------------------------------
MONGO_LOGS_DSN="mongodb://127.0.0.1:27017/logs"
MONGO_LOGS_USER=
MONGO_LOGS_PASS=
MONGO_LOGS_DB=logs

# Optional fallback (deprecated)
MONGO_HOST=127.0.0.1
MONGO_PORT=27017
MONGO_DB=logs


# ----------------------------------------------------------
# 🔴 REDIS ADAPTER (DSN-First)
# ----------------------------------------------------------
REDIS_CACHE_DSN="redis://127.0.0.1:6379"
REDIS_CACHE_PASS=

# Optional fallback (deprecated)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379


# ----------------------------------------------------------
# ⚙️ GENERAL CONFIGURATION
# ----------------------------------------------------------
APP_ENV=local
LOG_PATH=storage/logs
ADAPTER_LOG_PATH=storage/adapter_logs


# ----------------------------------------------------------
# 📊 METRICS & OBSERVABILITY
# ----------------------------------------------------------
METRICS_ENABLED=true
METRICS_EXPORT_FORMAT=prometheus
METRICS_SAMPLING_RATE=1.0
```

---

**© 2025 Maatify.dev**  
Engineered by **Mohamed Abdulalim ([@megyptm](https://github.com/megyptm))** — https://www.maatify.dev

📘 Full documentation & source code:  
https://github.com/Maatify/data-adapters

---
