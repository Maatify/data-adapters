[![Maatify Data Adapters](https://img.shields.io/badge/Maatify-Data%20Adapters-blue?style=for-the-badge)](../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

# Misuse Traps & Anti-Patterns

This document lists the **most common misuse patterns**
when working with `maatify/data-adapters`.

If you recognize your intended usage here,
**stop and reconsider**.

---

## 1. Treating Adapters as Abstractions

### The Mistake
Using adapters as if they provide a unified API:

```php
$adapter->query(...);
$adapter->isConnected();
````

### Why This Is Wrong

Adapters:

* Do not unify behavior
* Do not expose driver methods
* Do not hide differences

### Correct Mental Model

Adapters are **wrappers**, not services.

---

## 2. Expecting Unified Behavior Across Drivers

### The Mistake

Assuming PDO and DBAL behave the same way.

### Why This Is Wrong

Different drivers:

* Have different capabilities
* Expose different APIs
* Have different performance characteristics

Adapters intentionally preserve these differences.

---

## 3. Serializing Adapters or Drivers

### The Mistake

Storing adapters or drivers in:

* Queues
* Caches
* Sessions

### Why This Is Dangerous

Drivers often:

* Hold open connections
* Contain non-serializable resources
* Depend on process state

### Rule

**Never serialize adapters or drivers.**

---

## 4. Treating Adapters as Long-Lived Services

### The Mistake

Registering adapters as global singletons without understanding scope.

### Why This Is Risky

* Long-lived processes may outlive connections
* State becomes unclear
* Reconnection logic is missing by design

### Correct Approach

* Control adapter lifetime explicitly
* Recreate adapters when needed

---

## 5. Expecting Lifecycle Management

### The Mistake

Expecting:

* Auto reconnect
* Health checks
* Graceful shutdown

### Why This Fails

This package intentionally provides:

* No lifecycle hooks
* No state tracking

Lifecycle management belongs elsewhere.

---

## 6. Using Adapters as Configuration Carriers

### The Mistake

Passing adapters around to avoid passing configuration.

### Why This Is Wrong

* Adapters are runtime objects
* Configuration should remain explicit
* Mixing both creates hidden coupling

---

## 7. Ignoring Static Analysis

### The Mistake

Using adapters without PHPStan or Psalm.

### Why This Breaks Safety

* Type inference is lost
* Driver-specific methods are invisible
* Errors surface only at runtime

Static analysis is a **requirement**, not an option.

---

## 8. Building Business Logic on Top of Adapters

### The Mistake

Embedding business logic directly around adapters.

### Why This Is Wrong

Adapters are infrastructure boundaries.

Business logic belongs in:

* Services
* Repositories
* Use cases

---

## 9. Final Warning

If your intended usage requires:

* Unified APIs
* Convenience helpers
* Hidden behavior
* Runtime intelligence

**This package is not suitable.**

---

## Related Documents

* [`01-scope.md`](01-scope.md)
* [`02-design-decisions.md`](02-design-decisions.md)
* [`03-static-analysis.md`](03-static-analysis.md)
* [`05-lifecycle.md`](05-lifecycle.md)
