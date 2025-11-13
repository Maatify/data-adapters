
---

## 🧩 Environment Variables

| Variable                | Description                                                                              |
|:------------------------|:-----------------------------------------------------------------------------------------|
| `REDIS_HOST`            | Redis server hostname or IP address — used for caching, queueing, and distributed locks. |
| `REDIS_PORT`            | Redis connection port (default: 6379).                                                   |
| `REDIS_PASSWORD`        | Optional Redis password (leave empty if no authentication required).                     |
| `MONGO_HOST`            | MongoDB server hostname or IP address for activity logs and historical data.             |
| `MONGO_PORT`            | MongoDB connection port (default: 27017).                                                |
| `MONGO_USER`            | MongoDB username (if authentication is enabled).                                         |
| `MONGO_PASS`            | MongoDB password (if authentication is enabled).                                         |
| `MONGO_DB`              | Target MongoDB database name.                                                            |
| `MYSQL_HOST`            | MySQL server hostname or IP address for transactional and analytical data.               |
| `MYSQL_PORT`            | MySQL connection port (default: 3306).                                                   |
| `MYSQL_USER`            | MySQL username.                                                                          |
| `MYSQL_PASS`            | MySQL password (leave blank for local development).                                      |
| `MYSQL_DB`              | Target MySQL database name.                                                              |
| `MYSQL_DRIVER`          | Connection driver type (e.g., `dbal`, `pdo`).                                            |
| `APP_ENV`               | Application environment (`local`, `staging`, `production`).                              |
| `LOG_PATH`              | Global log storage path.                                                                 |
| `ADAPTER_LOG_PATH`      | Adapter-specific log path (per-driver logs).                                             |
| `METRICS_ENABLED`       | Enables or disables the metrics collector.                                               |
| `METRICS_EXPORT_FORMAT` | Format for metrics export (`prometheus`, `json`, or `none`).                             |
| `METRICS_SAMPLING_RATE` | Fraction of requests sampled for metrics (range: 0.0–1.0).                               |

---

#### 🧠 Example `.env`

```env
# ----------------------------------------------------------
# 🔴 REDIS ADAPTER CONFIGURATION
# ----------------------------------------------------------
# Redis connection parameters for caching, queueing, and distributed locks.
REDIS_HOST=127.0.0.1#          # Redis server hostname or IP address
REDIS_PORT=6379#               # Redis server port (default: 6379)
REDIS_PASSWORD=#               # Optional password (leave empty if no auth required)


# ----------------------------------------------------------
# 🟢 MONGODB ADAPTER CONFIGURATION
# ----------------------------------------------------------
# MongoDB connection details for activity logs and historical data.
MONGO_HOST=127.0.0.1#          # MongoDB server hostname or IP address
MONGO_PORT=27017#              # MongoDB server port (default: 27017)
MONGO_USER=#                   # MongoDB username (if authentication enabled)
MONGO_PASS=#                   # MongoDB password (if authentication enabled)
MONGO_DB=maatify_dev#          # Target MongoDB database name


# ----------------------------------------------------------
# 🔵 MYSQL ADAPTER CONFIGURATION
# ----------------------------------------------------------
# MySQL credentials for transactional data, analytics, and fallbacks.
MYSQL_HOST=127.0.0.1#          # MySQL server hostname or IP address
MYSQL_PORT=3306#               # MySQL server port (default: 3306)
MYSQL_USER=root#               # MySQL username
MYSQL_PASS=#                   # MySQL password (keep blank for local dev)
MYSQL_DB=maatify_dev#          # Target MySQL database name
MYSQL_DRIVER=dbal#             # Connection driver type (e.g., dbal, pdo)


# ----------------------------------------------------------
# ⚙️ GENERAL APPLICATION SETTINGS
# ----------------------------------------------------------
APP_ENV=local#                  			#Application environment (local, staging, production)
LOG_PATH=storage/logs#          			#Global log storage path
ADAPTER_LOG_PATH=storage/adapter_logs#    	#Adapter-specific logs (per-driver logs)


# ----------------------------------------------------------
# 📊 METRICS & OBSERVABILITY
# ----------------------------------------------------------
# Controls telemetry data collection and export format for adapter performance.
METRICS_ENABLED=true#           	#Enable/disable adapter metrics collector
METRICS_EXPORT_FORMAT=prometheus#   #Supported: prometheus, json, none
METRICS_SAMPLING_RATE=1.0#     		#Fraction of requests sampled for metrics (0.0–1.0)
```

---

**© 2025 Maatify.dev**  
Engineered by **Mohamed Abdulalim ([@megyptm](https://github.com/megyptm))** — https://www.maatify.dev

📘 Full documentation & source code:  
https://github.com/Maatify/data-adapters

---
