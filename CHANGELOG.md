# Changelog

All notable changes to this project will be documented in this file.

This project adheres to [Semantic Versioning](https://semver.org/)
and follows the principles of [Keep a Changelog](https://keepachangelog.com/en/1.1.0/).

---

## [2.0.1] — 2025-12-18

### 🧹 Fixed
- Remove leftover `.env` files from the repository
- Clarify that the library does not rely on environment variables

---

## [2.0.0] — 2025-12-18

### ⚠️ BREAKING CHANGE

This release is a **full architectural reset** of `maatify/data-adapters`.

Previous concepts and abstractions from v1.x are intentionally removed.
Backward compatibility is **not** preserved.

---

### ✨ Added

- Explicit DI-first adapter implementations for:
  - MySQL (PDO)
  - MySQL (Doctrine DBAL)
  - Redis (ext-redis)
  - Redis (Predis)
  - MongoDB (`MongoDB\Database`)
- Minimal `AdapterInterface` exposing `getDriver()` only
- Optional adapter factories with strict construction boundaries
- Typed `AdapterCreationException` for factory error isolation
- Full documentation set defining scope, design decisions, lifecycle boundaries,
  static analysis requirements, misuse traps, and dependency policy
- Explicit usage examples for all supported drivers under `examples/`
- Security policy via `SECURITY.md`

---

### 🔥 Removed

- Repository abstractions
- Query builders
- Unified or normalized APIs
- Configuration loading
- Environment variable access
- Lifecycle management (connect/reconnect/health)
- Runtime detection or magic behavior
- Implicit dependencies

---

### 🧠 Changed

- The package now acts strictly as a **dependency-injection boundary**
- All behavior beyond driver ownership is explicitly delegated
- Static analysis (PHPStan/Psalm) is a **design requirement**
- Optional dependencies are declared via `suggest` only
- Minimal installation by default (no driver dependencies required)

---

### 🧪 Testing

- 100% unit test coverage for adapter behavior
- No real databases or services required for tests
- Deterministic test execution suitable for CI environments

---

### 📦 Dependencies

- PHP >= 8.4
- `maatify/common` ^2.0
- All driver-specific dependencies are optional and explicitly documented

---

### 🔒 Stability

- This release establishes the **stable public contract** for v2.x
- Any future breaking change will require a new major version

---

## [1.x] — Legacy

The 1.x series is considered **legacy** and is no longer maintained.

Users are strongly encouraged to upgrade to v2.x.

---
