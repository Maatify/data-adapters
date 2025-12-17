# Phase E — Split Adapters (Total Isolation)

**Status:** Implemented  
**Scope:** Concrete adapter implementations only  
**Tests:** Deferred (explicitly not part of this phase)

---

## 🎯 Purpose

This phase introduces the **first real production source code** in
`maatify/data-adapters`.

The goal is to provide **explicit, minimal, DI-only adapters** that wrap
real infrastructure drivers **without adding any behavior**.

Each adapter acts strictly as:
- an ownership wrapper
- a DI boundary
- a typed bridge to the real driver

---

## ✅ What Was Implemented

The following concrete adapters were introduced:

### MySQL
- `MySQLPDOAdapter` → wraps `PDO`
- `MySQLDBALAdapter` → wraps `Doctrine\DBAL\Connection`

### Redis
- `RedisAdapter` → wraps `ext-redis (Redis)`
- `RedisPredisAdapter` → wraps `Predis\Client`

### MongoDB
- `MongoDatabaseAdapter` → wraps `MongoDB\Database`

All adapters:

- implement `AdapterInterface<TDriver>`
- accept **exactly one driver instance** via constructor
- expose **only** `getDriver(): object`
- contain **no logic**
- contain **no configuration**
- perform **no connection, detection, or validation**

---

## 🚫 What Is Explicitly NOT Included

This phase deliberately does **not** include:

- Factories
- DTOs
- Environment access
- Auto-detection
- BaseAdapter inheritance
- Retry, fallback, pooling, or health logic
- Tests (will be added in a later phase)

---

## 🧱 Architectural Decisions (Locked)

- **Split adapters per driver** to avoid optional dependency traps
- **No BaseAdapter** to preserve total isolation
- **No union types** across optional packages
- **Explicit naming**:
    - default driver → simple name (`RedisAdapter`)
    - alternative driver → explicit name (`RedisPredisAdapter`)

These decisions are final for v2.0.0.

---

## ➡️ What Comes Next

- Tests will be introduced in a dedicated testing phase
- DTOs and factories will be added in later phases
- This phase focuses **only** on source correctness and architectural clarity

---

**Phase E is considered complete once the adapters exist and compile correctly.**
