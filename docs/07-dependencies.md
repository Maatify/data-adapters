[![Maatify Data Adapters](https://img.shields.io/badge/Maatify-Data%20Adapters-blue?style=for-the-badge)](../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

# Dependency Policy & Matrix

This document defines the **dependency policy** for `maatify/data-adapters`.

Dependencies are part of the **public contract**.
Misconfigured dependencies lead to runtime failures and broken installations.
This policy exists to prevent that.

---

## 1. Purpose of This Policy

The goal of this dependency policy is to ensure:

- Minimal installation by default
- No hidden or implicit dependencies
- No accidental class loading failures
- Full control by the consuming application

Every dependency relationship in this package is **explicit and intentional**.

---

## 2. Minimal Install Philosophy

By default, installing `maatify/data-adapters`:

- Does NOT require any database extensions
- Does NOT require Redis
- Does NOT require MongoDB
- Does NOT require Doctrine DBAL

This is possible because:

- Adapters are split by driver
- No adapter is loaded unless explicitly referenced
- No runtime auto-detection exists

Minimal install means:
> **You only install what you actually use.**

---

## 3. Required Dependencies

The following dependencies are **always required**:

| Dependency        | Reason                                  |
|------------------|------------------------------------------|
| PHP ≥ 8.4        | Language features and typing guarantees |
| `maatify/common` | Provides the `AdapterInterface` contract |

No other dependency is mandatory.

---

## 4. Optional Dependencies (Explicit Only)

All driver-related dependencies are **optional**.

They are required **only if** the corresponding adapter is used.

| Adapter Class                 | Required Dependency          |
|-------------------------------|------------------------------|
| `MySQLPDOAdapter`             | `ext-pdo`                    |
| `MySQLDBALAdapter`            | `doctrine/dbal`              |
| `RedisAdapter` (ext-redis)    | `ext-redis`                  |
| `RedisPredisAdapter`          | `predis/predis`              |
| `MongoDatabaseAdapter`        | `mongodb/mongodb`            |

If an adapter class is referenced without its dependency installed,
PHP will fail immediately — **by design**.

---

## 5. Adapter → Dependency Matrix

This table defines the **exact dependency surface**:

| Adapter                         | PHP Extension | Composer Package |
|---------------------------------|---------------|------------------|
| MySQLPDOAdapter                 | ext-pdo       | —                |
| MySQLDBALAdapter                | —             | doctrine/dbal    |
| RedisAdapter                    | ext-redis     | —                |
| RedisPredisAdapter              | —             | predis/predis   |
| MongoDatabaseAdapter            | —             | mongodb/mongodb |

There are **no transitive dependencies** between adapters.

---

## 6. Why `suggest` Is Mandatory

Composer’s `suggest` section is used to:

- Document optional capabilities
- Prevent silent missing-dependency errors
- Make adapter requirements visible at install time

Each optional dependency is declared as:

```json
"suggest": {
  "ext-pdo": "Required for MySQLPDOAdapter",
  "doctrine/dbal": "Required for MySQLDBALAdapter",
  "ext-redis": "Required for RedisAdapter (ext-redis)",
  "predis/predis": "Required for RedisPredisAdapter",
  "mongodb/mongodb": "Required for MongoDatabaseAdapter"
}
```

This is **not optional documentation**.
It is part of the dependency contract.

---

## 7. Common Dependency Mistakes

### ❌ Installing Everything “Just in Case”

* Increases attack surface
* Slows CI
* Hides real requirements

### ❌ Assuming Adapters Auto-Detect Drivers

* No detection exists
* No fallback exists

### ❌ Expecting Graceful Degradation

* Missing dependency = fatal error
* This is intentional and explicit

---

## 8. Versioning & Compatibility

* Optional dependencies are **not version-pinned**
* Compatibility is delegated to:

    * The adapter consumer
    * The vendor library itself

Breaking changes in optional dependencies may require:

* Adapter updates
* Or a new major version of this package

---

## 9. Final Dependency Lock

Dependency rules are **locked**:

* No hidden dependencies
* No conditional loading
* No polyfills
* No automatic installation

Any pull request that violates this policy
will be rejected.

---

## Related Documents

* [`01-scope.md`](01-scope.md)
* [`06-factories.md`](06-factories.md)
* [`README.md`](../README.md)
