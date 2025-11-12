# 🧾 CHANGELOG — maatify/data-adapters

**Project:** maatify/data-adapters  
**Version:** 1.0.0  
**Maintainer:** Mohamed Abdulalim ([@megyptm](https://github.com/megyptm))  
**Organization:** [Maatify.dev](https://www.maatify.dev)  
**License:** MIT  
**Release Date:** 2025-11-12  

---

## 🧱 Version 1.0.0 — Stable Release

### 🗓 Summary
First stable release of **maatify/data-adapters** — the unified data connectivity & diagnostics layer for the Maatify ecosystem.  
Includes support for Redis (phpredis + Predis fallback), MongoDB, and MySQL (PDO/DBAL) with built-in health, fallback, and telemetry systems.

---

### 📚 Phase Overview

| Phase     | Title                                            | Status | Key Highlights                                                          |
|:----------|:-------------------------------------------------|:-------|:------------------------------------------------------------------------|
| **1**     | Environment Setup                                | ✅      | Composer init, Docker, CI, PHPUnit bootstrap                            |
| **2**     | Core Interfaces & Base Structure                 | ✅      | AdapterInterface, BaseAdapter, DatabaseResolver, EnvironmentConfig      |
| **3**     | Adapter Implementations                          | ✅      | Redis, Predis, Mongo, MySQL (PDO + DBAL) drivers                        |
| **3.5**   | Adapter Smoke Tests Extension                    | ✅      | Added Predis, Mongo, MySQL smoke tests (no connections)                 |
| **4**     | Health & Diagnostics Layer                       | ✅      | DiagnosticService, healthCheck(), AdapterFailoverLog                    |
| **4.1**   | Hybrid AdapterFailoverLog Enhancement            | ✅      | Dynamic log path with .env support & auto-creation                      |
| **4.2**   | Adapter Logger Abstraction via DI                | ✅      | AdapterLoggerInterface + FileAdapterLogger (Dependency Injection)       |
| **5**     | Integration & Unified Testing                    | ✅      | Ecosystem integration tests (RateLimiter, SecurityGuard, MongoActivity) |
| **7**     | Observability & Metrics                          | ✅      | AdapterMetricsCollector, Prometheus export, PSR Logger context          |
| **8**     | Documentation & Release                          | ✅      | README, CHANGELOG, LICENSE, Packagist ready                             |

---

## 🧩 Detailed Phase Highlights

### **Phase 1 — Environment Setup**
- Initialized Composer project with `maatify/common`.
- Added PSR-4 autoload, Docker compose (Redis + Mongo + MySQL).
- Configured GitHub Actions for CI and PHPUnit.

---

### **Phase 2 — Core Interfaces & Base Structure**
- Introduced `AdapterInterface`, `BaseAdapter`, and exception hierarchy.  
- Implemented `EnvironmentConfig` loader and `DatabaseResolver`.  
- Added .env auto-detection for Redis/Mongo/MySQL.

---

### **Phase 3 — Adapter Implementations**
- Built Redis (phpredis + Predis fallback), MongoDB, and MySQL (PDO/DBAL) adapters.  
- Added `reconnect()` and graceful shutdown.  
- Extended DatabaseResolver for auto driver resolution.

---

### **Phase 3.5 — Adapter Smoke Tests Extension**
- Added Predis/Mongo/MySQL smoke tests (no live connections).  
- Validated autoload structure and PHPUnit suites.  
- CI runs safe tests without network dependencies.

---

### **Phase 4 — Health & Diagnostics Layer**
- Implemented `DiagnosticService` for adapter status JSON output.  
- Introduced `AdapterFailoverLog` for fallback recording.  
- Integrated Enum support (`AdapterTypeEnum`) in Diagnostics.

---

### **Phase 4.1 — Hybrid AdapterFailoverLog Enhancement**
- Added runtime-resolved log path with .env config (`ADAPTER_LOG_PATH`).  
- Enabled hybrid (static + instance) logging design.  
- Ensured auto-creation of log directories.

---

### **Phase 4.2 — Adapter Logger Abstraction via DI**
- Replaced static logging calls with DI-based `AdapterLoggerInterface`.  
- Added `FileAdapterLogger` (default implementation).  
- Updated DiagnosticService constructor for injectable logger.

---

### **Phase 5 — Integration & Unified Testing**
- Created mock integration tests for RateLimiter, SecurityGuard, MongoActivity.  
- Added real integration templates for live testing.  
- Unified PHPUnit bootstrap and env setup.  
- CI validated cross-adapter compatibility.

---

### **Phase 7 — Observability & Metrics**
- Introduced `AdapterMetricsCollector` for latency & success metrics.  
- Added `PrometheusMetricsFormatter` for monitoring dashboards.  
- Integrated PSR-Logger contexts and adapter tags.  
- Coverage ≈ 90 %, latency impact < 0.3 ms.

---

### **Phase 8 — Documentation & Release**
- Consolidated all phases into `docs/README.full.md`.  
- Added `CHANGELOG.md`, `LICENSE`, `SECURITY.md`, `VERSION`.  
- Updated `composer.json` metadata and Packagist release.  
- Tagged `v1.0.0` and validated build via GitHub Actions.

---
## [1.1.0] — 2025-11-12
### 🧩 Phase 9 — Deprecated Legacy Fallback Layer Removal

#### 🔥 Removed
- **Removed entire fallback subsystem** (`FallbackQueue`, `FallbackQueuePruner`, `RecoveryWorker`, `SqliteFallbackStorage`, `MysqlFallbackStorage`).
- **Removed `handleFailure()`**, `isFallbackEnabled()`, and `setFallbackManager()` from `BaseAdapter`.
- **Deleted all tests under** `tests/Fallback/` and updated `BaseAdapterTest` accordingly.
- **Removed .env variables:**  
  `FALLBACK_STORAGE_DRIVER`, `FALLBACK_STORAGE_PATH`, `FALLBACK_QUEUE_TTL`, `REDIS_RETRY_SECONDS`, `ADAPTER_FALLBACK_ENABLED`.

#### ⚙️ Updated
- `BaseAdapter` simplified to handle only connection lifecycle and configuration.
- `BaseAdapterTest` refactored to validate `requireEnv()` behavior and environment integrity.
- `README.md` and `README.full.md` cleaned from deprecated fallback flow diagrams.
- `EnvironmentConfig` untouched but now used consistently across all adapters.

#### ✅ Impact
- **Reduced complexity:** no background workers or fallback managers.
- **Stabilized behavior:** adapters now fail fast with proper exceptions.
- **Improved reliability:** simpler tests, no filesystem dependency.
- **Prepared foundation** for multi-profile MySQL (Phase 10) and dynamic registry (Phase 11).

---

> 🧭 Next: Phase 10 — Multi-Profile MySQL Connections  
> Enables multiple database profiles via `mysql.{profile}` syntax and prefixed environment variables.

---

## 🧪 Test & CI Summary
- **Coverage:** ≈ 90 % (over 300 assertions)  
- **PHPUnit:** ✅ All suites passed  
- **CI:** 🟢 Build green on main branch  
- **Integration:** Stable at > 10 k req/sec load

---

## 🧩 Compatibility
| Library                | Integration | Status                  |
|------------------------|-------------|-------------------------|
| maatify/common         | ✅           | Core utilities          |
| maatify/psr-logger     | ✅           | Logging layer           |
| maatify/rate-limiter   | 🟡          | Integration tests ready |
| maatify/security-guard | 🟡          | Integration tests ready |
| maatify/mongo-activity | ✅           | Confirmed connected     |

---

## 🪄 Future Roadmap
- **v1.1.0:** Multi-Profile MySQL Connections + Dynamic Database Registry
- **v1.2.0:** Real-time Telemetry API endpoints  
- **v1.3.0:** Distributed Health Cluster Monitor  
- **v2.0.0:** Async adapter engine with Swoole support  

---

> 🧩 *maatify/data-adapters — Unified Data Connectivity & Diagnostics Layer*  
> © 2025 Maatify.dev • Authored by Mohamed Abdulalim (@megyptm)
