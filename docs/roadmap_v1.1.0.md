![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---
# 📦 maatify/data-adapters  
## **Roadmap — Version 1.1.0**

**Owner:** Maatify.dev  
**Base Version:** 1.0.0  
**Maintainer:** Mohamed Abdulalim (megyptm)  
**Goal:** Extend the unified connectivity layer with DSN support, multi-profile database resolution, and dynamic registry configuration.

---

# 🚀 Overview

Version **1.1.0** introduces a cleaner and more flexible configuration system across all adapters.  
Major additions include:

- Full DSN support (MySQL, Redis, Mongo)
- Multi-profile MySQL connections
- Multi-profile Mongo connections
- Optional dynamic JSON registry
- Complete documentation + >90% test coverage

---

# 🧩 **Phase 10 — DSN Support (NEW)**  
### _Status: Planned — 0%_

### 🎯 Goal  
Enable first-class DSN support for all adapters, allowing projects to use single-line connection strings instead of multiple `.env` variables.

### 🔧 Tasks
- Add `EnvironmentConfig::getDsnConfig(type, profile)`
- Support:
  - `MYSQL_MAIN_DSN`, `MYSQL_LOGS_DSN`, `MYSQL_ANALYTICS_DSN`
  - `MONGO_MAIN_DSN`, `REDIS_MAIN_DSN`
- Merge DSN + username/password/options cleanly
- Update `DatabaseResolver` to prioritize DSN
- Add adapter-level DSN parsing for MySQL/Mongo/Redis
- Implement `TestDsnResolutionTest`
- Create documentation: `README.phase10.md`

### 📝 Notes  
DSN support becomes the new preferred configuration method but remains fully backward-compatible.

---

# 🧩 **Phase 11 — Multi-Profile MySQL Connections**  
### _Status: Planned — 0%_

### 🎯 Goal  
Introduce profile-based database resolution such as:

```

mysql.main
mysql.logs
mysql.analytics

```

### 🔧 Tasks
- Add `EnvironmentConfig::getMySQLConfig(profile)`
- Support prefixed variables:
  - `MYSQL_MAIN_*`, `MYSQL_LOGS_*`, `MYSQL_ANALYTICS_*`
  - `MYSQL_MAIN_DSN`, etc.
- Update resolver to support `mysql.{profile}`
- Cache adapters per profile
- Add `MysqlProfileResolverTest`
- Write `README.phase11.md`

### 🔗 Dependencies  
`phase10`

---

# 🧩 **Phase 12 — Multi-Profile MongoDB Support (NEW)**  
### _Status: Planned — 0%_

### 🎯 Goal  
Add support for multiple MongoDB connections:

```

mongo.main
mongo.logs
mongo.activity

```

### 🔧 Tasks
- Add `EnvironmentConfig::getMongoConfig(profile)`
- Support:
  - `MONGO_MAIN_*`, `MONGO_LOGS_*`, `MONGO_ACTIVITY_*`
  - `MONGO_MAIN_DSN`, etc.
- Update resolver to support `mongo.{profile}`
- Cache Mongo adapters per profile
- Add `MongoProfileResolverTest`
- Document in `README.phase12.md`

### 🔗 Dependencies  
`phase10`

---

# 🧩 **Phase 13 — Dynamic JSON Registry (Optional)**  
### _Status: Planned — 0%_

### 🎯 Goal  
Enable configuration loading from:

```

/config/databases.json

```

with runtime override priorities.

### 🔧 Tasks
- Add registry loader to `EnvironmentConfig`
- Define JSON schema
- Merge priority:
  - JSON → DSN → ENV
- Add runtime hot-reload flag
- Add `RegistryConfigTest`
- Document in `README.phase13.md`

### 🔗 Dependencies  
`phase10`, `phase11`, `phase12`

---

# 🧩 **Phase 14 — Documentation & Release 1.1.0**  
### _Status: Pending — 0%_

### 🎯 Goal  
Finalize the release with unified docs, changelog, and Packagist publish.

### 🔧 Tasks
- Merge DSN, profiles, and registry docs into `docs/README.full.md`
- Update `README.md` with new features
- Add CHANGELOG entry for v1.1.0
- Finalize tests (>90% coverage)
- Tag and publish `v1.1.0` on Packagist

### 🔗 Dependencies  
`phase10`, `phase11`, `phase12`, `phase13`

---

# 🟦 Summary

| Phase | Title                          | Status    |
|-------|--------------------------------|-----------|
| 10    | DSN Support                    | Planned   |
| 11    | Multi-Profile MySQL            | Planned   |
| 12    | Multi-Profile Mongo            | Planned   |
| 13    | Dynamic JSON Registry          | Planned   |
| 14    | Documentation & Release 1.1.0  | Pending   |

---

# 🧱 Ready for Execution  
This roadmap is now stable and ready to be used for automated execution via the Maatify Project Executor.

---

**© 2025 Maatify.dev**  
Engineered by **Mohamed Abdulalim ([@megyptm](https://github.com/megyptm))** — https://www.maatify.dev

📘 Full documentation & source code:  
https://github.com/Maatify/data-adapters

---