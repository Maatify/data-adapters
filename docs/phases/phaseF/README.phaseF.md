# Phase F — Factories & Infrastructure Error Boundary

## Version
v2.0.0

## Status
COMPLETED

---

## 🎯 Phase Goal

Introduce an **explicit factory layer** for creating adapters while enforcing a
**clear infrastructure error boundary**, without introducing:

- Environment access
- Runtime auto-detection
- Hidden defaults
- Adapter-side logic

Factories exist **only as a convenience layer** and are not part of the core adapter contract.

---

## 🧱 What Was Added

### 1) Adapter Factories

Factories were introduced for each supported infrastructure:

#### MySQL
- `MySQLAdapterFactory`
    - `fromPDO(PDO $pdo)`
    - `fromDBAL(Connection $connection)`
    - `fromPDOFactory(callable():PDO)`

#### Redis
- `RedisAdapterFactory`
    - `fromRedis(Redis $redis)`
    - `fromPredis(Client $client)`
    - `fromRedisFactory(callable():Redis)`

#### MongoDB
- `MongoAdapterFactory`
    - `fromDatabase(Database $database)`
    - `fromDatabaseFactory(callable():Database)`

All factories:
- Are fully explicit
- Perform no auto-detection
- Do not read env or config files
- Do not unify driver APIs

---

### 2) Infrastructure Error Boundary

A typed exception was introduced:

- `AdapterCreationException`

Responsibilities:
- Wrap vendor exceptions thrown during factory execution
- Act as a **clear boundary** between application code and infrastructure creation
- Prevent leakage of vendor-specific exceptions into higher layers

Adapters themselves remain exception-transparent and logic-free.

---

## 🧠 Design Decisions

### Why factories accept `callable():Driver`

- Enables deferred construction
- Keeps factories deterministic
- Avoids passing configuration or env into the adapter layer
- Fully compatible with PHPStan level max via `@phpstan-param`

### Why no `instanceof` checks

- Static analysis guarantees the callable contract
- Runtime validation would be redundant and misleading
- Contract violations are considered application-level bugs

---

## 🚫 Explicitly Out of Scope

This phase does NOT include:

- `fromEnv()` helpers
- Configuration DTO handling
- Runtime driver validation
- Connection health checks
- Retry, fallback, or pooling logic

---

## ✅ Completion Criteria

- All factories are explicit and deterministic
- PHPStan level max passes with zero errors
- No architectural drift from DI-first principles
- No changes to adapter contracts

---

## 🔒 Phase Status

**CLOSED**

Next phase: **Phase G — Testing Strategy & Contract Enforcement**
