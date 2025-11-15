# 🧾 CHANGELOG — maatify/data-adapters

All notable changes to this project will be documented in this file.

---

**Project:** maatify/data-adapters  
**Version:** 1.0.0  
**Maintainer:** Mohamed Abdulalim ([@megyptm](https://github.com/megyptm))  
**Organization:** [Maatify.dev](https://www.maatify.dev)  
**License:** MIT  
**Release Date:** 2025-11-12  

---

Here are the **perfectly formatted**, Maatify-standard **CHANGELOG entry** and **release note** for the `.env.example` update.

---

# 🧾 **CHANGELOG Entry**

Add this under your latest version (e.g., `1.1.1` or `1.2.0` — depending on what you decide):

---

## **[1.1.0] — 2025-11-14**

### **Updated**

* Refreshed `.env.example` with full DSN-first configuration schema (Phase 11).
* Added missing **multi-profile MySQL** variables (`MYSQL_MAIN_HOST`, `MYSQL_MAIN_PORT`, etc.) to ensure legacy mode passes CI tests.
* Normalized MySQL DSN strings with explicit `port` and `charset=utf8mb4` for consistency.
* Clarified Redis & Mongo DSN format examples and aligned fallback variables.
* Improved formatting, comments, and organization across all environment sections.
* Ensured `.env.example` fully matches internal resolver behavior and PHPUnit expectations.

---

## **[1.1.0] — 2025-11-14**

### 🚀 Phase 12 — Multi-Profile MongoDB Support

### Added

* `MongoConfigBuilder` for DSN parsing and multi-profile MongoDB configuration.
* Support for **unlimited MongoDB profiles**:
  `mongo.main`, `mongo.logs`, `mongo.activity`, `mongo.events`, …etc.
* DSN-first parsing for:

    * `mongodb://host:port/database`
    * `mongodb+srv://cluster/database`
* New test suite: `MongoProfileResolverTest`
  (profile independence, DSN parsing, builder merge logic, resolver integration).
* Added resolver-level caching for Mongo profiles to match MySQL behavior.
* Documentation: `README.phase12.md`.

### Changed

* `MongoAdapter` now overrides `resolveConfig()` identical to MySQL:

    * Merge priority: **DSN → builder → legacy env → BaseAdapter fallback**
* Updated connection builder to safely merge user/pass even when missing from DSN.

### Notes

* Fully backward compatible with legacy `MONGO_HOST`, `MONGO_PORT`, `MONGO_DB`.
* No changes required in EnvironmentConfig.
* Architecture now fully aligned between MySQL and Mongo adapters.

---

## [1.1.0] — 2025-11-14
### Phase 11 — Multi-Profile MySQL Resolution
### Added
- `MySqlConfigBuilder` for centralized MySQL config.
- Support for dynamic profiles (`mysql.reporting`, `mysql.billing`, etc.).
- Full DSN priority handling (PDO DSN + Doctrine URL DSN).
- New test suite: `MysqlProfileResolverTest`.

### Changed
- `MySQLAdapter` now overrides `resolveConfig()` to merge builder + BaseAdapter.
- `MySQLDbalAdapter` now uses builder for consistent profile resolution.

### Notes
- Backward compatible with all previous env formats.
- Redis and Mongo remain unchanged.

---

## [1.1.0] — 2025-11-13
### 🚀 Phase 10 — Multi-Profile MySQL Connections

#### Added
- ✨ Support for **multiple MySQL profiles** using dotted notation  
  (`mysql.main`, `mysql.logs`, `mysql.analytics`, ...).
- New method: `EnvironmentConfig::getMySQLConfig($profile)`  
  to load environment variables based on prefix (e.g., `MYSQL_LOGS_HOST`).
- Automatic fallback to legacy `MYSQL_*` variables when no profile prefix exists.
- DatabaseResolver upgraded to parse `mysql.<profile>` and inject profile-specific config.
- Independent adapter instances per profile with internal caching.

#### Documentation
- Added page: `docs/mysql-profiles.md` (profile structure, examples, diagrams).
- Updated README with new usage examples and environment notes.

#### Tests
- Added:
    - `MySQLProfileResolverTest`
    - `EnvironmentFallbackTest`
    - `ProfileCachingTest`
    - `MultiProfileConnectionTest`

#### Coverage
- 📈 Overall test coverage: **87%+**

> 🧭 Next: Phase 11 — Dynamic Database Registry  
> Introduces a JSON/YAML-based registry for defining multiple database connections at runtime,  
> with priority rules (runtime JSON → .env → defaults) and optional hot-reload support.

---

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

## 🧱 Version 1.0.0 — Stable Release

### 🗓 Summary
First stable release of **maatify/data-adapters** — the unified data connectivity & diagnostics layer for the Maatify ecosystem.  
Includes support for Redis (phpredis + Predis fallback), MongoDB, and MySQL (PDO/DBAL) with built-in health, fallback, and telemetry systems.

---

### 📚 Phase Overview

| Phase   | Title                                 | Status | Key Highlights                                                          |
|:--------|:--------------------------------------|:-------|:------------------------------------------------------------------------|
| **1**   | Environment Setup                     | ✅      | Composer init, Docker, CI, PHPUnit bootstrap                            |
| **2**   | Core Interfaces & Base Structure      | ✅      | AdapterInterface, BaseAdapter, DatabaseResolver, EnvironmentConfig      |
| **3**   | Adapter Implementations               | ✅      | Redis, Predis, Mongo, MySQL (PDO + DBAL) drivers                        |
| **3.5** | Adapter Smoke Tests Extension         | ✅      | Added Predis, Mongo, MySQL smoke tests (no connections)                 |
| **4**   | Health & Diagnostics Layer            | ✅      | DiagnosticService, healthCheck(), AdapterFailoverLog                    |
| **4.1** | Hybrid AdapterFailoverLog Enhancement | ✅      | Dynamic log path with .env support & auto-creation                      |
| **4.2** | Adapter Logger Abstraction via DI     | ✅      | AdapterLoggerInterface + FileAdapterLogger (Dependency Injection)       |
| **5**   | Integration & Unified Testing         | ✅      | Ecosystem integration tests (RateLimiter, SecurityGuard, MongoActivity) |
| **7**   | Observability & Metrics               | ✅      | AdapterMetricsCollector, Prometheus export, PSR Logger context          |
| **8**   | Documentation & Release               | ✅      | README, CHANGELOG, LICENSE, Packagist ready                             |
| **9**   | Removal of Legacy Fallback Layer      | ✅      | Removed fallback system, cleaned BaseAdapter, removed fallback tests    |
| **10**  | Multi-Profile MySQL Connections       | ✅      | mysql.logs, mysql.main, prefixed env, profile resolver                  |

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
- **v1.2.0:** Dynamic Database Registry (runtime JSON/YAML + hot reload)
- **v1.2.0:** Real-time Telemetry API endpoints  
- **v1.3.0:** Distributed Health Cluster Monitor  
- **v2.0.0:** Async adapter engine with Swoole support  

---

> 🧩 *maatify/data-adapters — Unified Data Connectivity & Diagnostics Layer*  
> © 2025 Maatify.dev • Authored by Mohamed Abdulalim (@megyptm)

---

**© 2025 Maatify.dev**  
Engineered by **Mohamed Abdulalim ([@megyptm](https://github.com/megyptm))** — https://www.maatify.dev

📘 Full documentation & source code:  
https://github.com/Maatify/data-adapters

---

<p align="center">
  <sub><span style="color:#777">Built with ❤️ by <a href="https://www.maatify.dev">Maatify.dev</a> — Unified Ecosystem for Modern PHP Libraries</span></sub>
</p>
