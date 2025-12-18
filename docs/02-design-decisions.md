[![Maatify Data Adapters](https://img.shields.io/badge/Maatify-Data%20Adapters-blue?style=for-the-badge)](../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

# Design Decisions

This document explains the **key architectural decisions**
behind `maatify/data-adapters`.

These decisions are **intentional, final, and non-negotiable**.
They exist to prevent ambiguity, misuse, and architectural drift.

---

## 1. Why This Package Exists

The primary goal is **not abstraction**.

The goal is:

> To introduce a **clear dependency-injection boundary**
> between application code and low-level infrastructure drivers.

This boundary:
- Reduces accidental coupling
- Improves testability
- Makes infrastructure ownership explicit

Nothing more.

---

## 2. Why There Is NO Unified API

Unifying APIs across different drivers would require:

- Hiding real behavioral differences
- Dropping driver-specific capabilities
- Introducing a “lowest common denominator”
- Adding conditional logic and runtime branching

All of these lead to:
- Fragile abstractions
- Unexpected runtime behavior
- Increased maintenance cost

### Decision

**No API unification is allowed.**

Driver differences are:
- Real
- Important
- Intentionally visible

If you need a unified API, it belongs in a **higher-level library**.

---

## 3. Why `getDriver(): object`

The adapter contract exposes exactly one method:

```php
public function getDriver(): object;
````

### Runtime Reason

* PHP cannot express “generic return types” at runtime
* Returning `object` avoids false promises

### Static Analysis Reason

* Docblock generics preserve precise typing
* IDEs and PHPStan/Psalm infer the real driver type

This creates:

* Zero runtime cost
* Maximum static safety

---

## 4. Why Docblock Generics Are Mandatory

PHP lacks native generics.

Docblock templates are used to:

* Preserve type information
* Enable static analysis
* Avoid runtime metadata or reflection

Example:

```php
/**
 * @implements AdapterInterface<PDO>
 */
final class MySQLPDOAdapter {}
```

This decision is:

* Explicit
* Tooling-driven
* Future-proof

---

## 5. Why There Is No Base “Smart” Adapter

Adapters do **not**:

* Detect driver state
* Expose metadata
* Proxy method calls
* Provide helper methods

Reasons:

* Prevent hidden behavior
* Avoid magic delegation
* Keep adapters mechanically simple

Adapters are **data holders**, not services.

---

## 6. Why Configuration Is Explicitly Excluded

Configuration is intentionally kept **outside** this package.

This includes:

* Environment variables
* Config files
* Secrets managers

Reasons:

* Different applications load config differently
* Mixing config with adapters creates hidden coupling
* Determinism is lost when config is implicit

Adapters only accept **ready objects**.

---

## 7. Why Factories Are Optional

Factories exist only to:

* Reduce repetitive boilerplate
* Provide a clear error boundary

They are **not required**.

Rules:

* No env access
* No auto-detection
* No fallback logic

Factories are convenience — never core behavior.

---

## 8. Why Typed Exceptions Exist

`AdapterCreationException` exists to:

* Mark the boundary between application code and vendor errors
* Preserve the original exception via chaining
* Avoid leaking construction logic upward

Adapters themselves **never throw** these exceptions.
Only factories may.

---

## 9. Why Determinism Is Mandatory

Deterministic behavior means:

* Same input → same output
* No runtime decisions
* No hidden state

This enables:

* 100% test coverage
* Stable CI
* Predictable usage

Determinism is a **design constraint**, not a side effect.

---

## 10. Why Backward Compatibility Is NOT Guaranteed

Version 2.x is a **clean architectural reset**.

As a result:

* No v1.x compatibility is preserved
* No transitional shims exist
* Future breaking changes require a new major version

This prevents:

* Legacy complexity
* Design compromise

---

## 11. Final Decision Lock

These decisions are **locked**.

They may only change if:

* A new major version is released
* The scope of the package changes explicitly

Any pull request that violates these decisions
will be rejected regardless of implementation quality.

---

## Related Documents

* [`01-scope.md`](01-scope.md)
* [`03-static-analysis.md`](03-static-analysis.md)
* [`04-misuse-traps.md`](04-misuse-traps.md)
* [`06-factories.md`](06-factories.md)
