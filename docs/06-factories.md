[![Maatify Data Adapters](https://img.shields.io/badge/Maatify-Data%20Adapters-blue?style=for-the-badge)](../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

# Factories & Construction Boundary

This document defines **what adapter factories are**,  
**what they are not**, and **why they exist** in `maatify/data-adapters`.

Factories are a **convenience layer only**.

---

## 1. Why Factories Exist

Factories exist to solve **one narrow problem**:

> Reduce repetitive adapter construction code  
> while preserving explicitness and determinism.

They do **not** introduce new behavior.  
They do **not** change adapter responsibilities.

---

## 2. Factories Are Optional

Factories are **never required**.

You can always construct adapters directly:

```php
new MySQLPDOAdapter($pdo);
new RedisAdapter($redis);
new MongoDatabaseAdapter($database);
````

Factories exist only to:

* Improve readability
* Centralize construction patterns
* Provide a clear error boundary

---

## 3. What Factories Are Allowed to Do

Factories MAY:

* Accept **ready driver instances**
* Accept **explicit factory callables**
* Construct adapters
* Catch vendor-specific exceptions
* Throw `AdapterCreationException` as a boundary

Factories MUST remain:

* Explicit
* Deterministic
* Side-effect free

---

## 4. What Factories Are NOT Allowed to Do

Factories MUST NOT:

* ❌ Read environment variables
* ❌ Load configuration files
* ❌ Perform runtime auto-detection
* ❌ Switch drivers implicitly
* ❌ Implement fallback logic
* ❌ Retry failed connections
* ❌ Manage lifecycle or pooling
* ❌ Normalize vendor behavior

If a factory does any of the above, it violates package scope.

---

## 5. Naming Rules (Strict)

Factory method names must reflect **exactly what they accept**.

Examples:

* `fromPDO(PDO $pdo)`
* `fromDBAL(Connection $connection)`
* `fromRedis(Redis $redis)`
* `fromDatabase(Database $database)`

Forbidden names:

* `fromEnv`
* `fromConfigFile`
* `auto`
* `detect`
* `resolve`

Naming is part of the API contract.

---

## 6. Factory Callables

Some factories accept callables:

```php
fromPDOFactory(callable(): PDO)
```

Purpose:

* Defer driver creation
* Allow application-controlled instantiation
* Isolate vendor exceptions

Factories MUST:

* Invoke the callable exactly once
* Treat the callable as opaque
* Never introspect its logic

---

## 7. Typed Error Boundary

Factories define a **clear construction boundary**.

When driver creation fails:

* Vendor exceptions are caught
* An `AdapterCreationException` is thrown
* The original exception is preserved as `previous`

Adapters themselves:

* Never throw `AdapterCreationException`
* Never catch vendor errors

This keeps error responsibility explicit.

---

## 8. Why Factories Do NOT Accept DTOs Here

Although DTO-based configuration may exist in other layers:

* Factories in this package remain **driver-centric**
* Configuration parsing belongs elsewhere
* Passing DTOs here would blur responsibility boundaries

If a project needs DTO-based construction:

* Implement it in a bootstrap or integration layer
* Not inside adapters or factories

---

## 9. Determinism Guarantee

Factories must guarantee:

* Same input → same adapter
* No hidden state
* No conditional behavior

If a factory’s output depends on runtime conditions,
it violates the design.

---

## 10. Final Rule

Factories are:

> **Helpers, not abstractions.**

They reduce boilerplate
without increasing responsibility.

Any attempt to make factories “smart”
is a design violation.

---

## Related Documents

* [`01-scope.md`](01-scope.md)
* [`02-design-decisions.md`](02-design-decisions.md)
* [`04-misuse-traps.md`](04-misuse-traps.md)
* [`05-lifecycle.md`](05-lifecycle.md)
