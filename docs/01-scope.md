[![Maatify Data Adapters](https://img.shields.io/badge/Maatify-Data%20Adapters-blue?style=for-the-badge)](../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

# Scope & Boundaries

**What this package is — and strictly what it is not**

This document defines the **non-negotiable scope** of `maatify/data-adapters`.
If your expectations fall outside these boundaries, **this package is not for you**.

---

## 1. Purpose of This Package

`maatify/data-adapters` exists to solve **one problem only**:

> Provide a **clean, explicit dependency-injection boundary**
> around **real infrastructure drivers**.

Without this package:
- Applications pass raw drivers everywhere
- Higher layers become tightly coupled to vendor APIs
- Testing and replacement become ad-hoc and inconsistent

This package introduces **structure**, not behavior.

---

## 2. What This Package IS

This package **IS**:

- A **DI boundary** around infrastructure drivers
- An **ownership wrapper** for real drivers (PDO, Redis, MongoDB, etc.)
- **Explicit by design** — no hidden behavior
- **Deterministic** — no runtime decisions
- **Statically analyzable** via docblock generics
- **Testable at 100%** without real databases

In short:

> It holds drivers.  
> It returns drivers.  
> It does nothing else.

---

## 3. What This Package IS NOT (Hard No)

This package is **NOT**:

- ❌ A unified database API
- ❌ An abstraction layer that normalizes behavior
- ❌ An ORM
- ❌ A query builder
- ❌ A repository layer
- ❌ A connection manager
- ❌ A configuration loader
- ❌ A lifecycle controller
- ❌ A retry / fallback / resilience system
- ❌ A health-check or pooling solution
- ❌ A framework integration layer

If you are looking for any of the above,
**you are in the wrong package**.

---

## 4. Explicit Non-Goals

The following are **intentionally excluded**:

- No API normalization between different drivers
- No hiding of vendor differences
- No runtime detection or switching
- No environment variable access
- No configuration parsing
- No convenience magic
- No backward compatibility with v1.x concepts

These are not “missing features”.
They are **explicit design decisions**.

---

## 5. Supported Scope (What Is Allowed)

The only allowed responsibilities are:

- Accepting **ready-to-use driver instances**
- Wrapping drivers inside explicit adapter objects
- Returning drivers via `getDriver()`
- Providing **optional factories** as pure convenience
- Preserving static typing through docblock generics

Anything beyond this scope is rejected by design.

---

## 6. Responsibility Boundaries

### This package owns:
- Adapter construction
- Driver ownership
- Explicit boundaries

### This package does NOT own:
- Configuration (env, files, secrets)
- Connection creation
- Error handling strategies
- Reconnection logic
- Observability
- Business logic
- Query logic

Those responsibilities belong to:
- The application
- Bootstrap layers
- Higher-level libraries

---

## 7. Consequences of This Design

Because of these boundaries:

- Users must **explicitly choose drivers**
- Users must **explicitly manage configuration**
- Driver differences remain visible
- Higher layers gain **full control**
- No hidden behavior can surprise you at runtime

This is a **trade-off**, and it is intentional.

---

## 8. Misuse Warning (Short)

Common misuses include:

- Treating adapters as abstractions
- Expecting unified behavior across drivers
- Expecting lifecycle management

These are covered in detail in:
➡️ [`04-misuse-traps.md`](04-misuse-traps.md)

---

## 9. Final Scope Lock

If you need:
- A unified API
- Automatic configuration
- Runtime magic
- Connection management

**Do not use this package.**

If you need:
- Explicit DI boundaries
- Deterministic behavior
- Total control over infrastructure

**This package fits exactly.**

---

## 🔒 Scope Status

- Scope: **LOCKED**
- Extension policy: **FORBIDDEN**
- Evolution: **New major version only**

---

### Related Documents

- [`README.md`](../README.md)
- [`02-design-decisions.md`](02-design-decisions.md)
- [`04-misuse-traps.md`](04-misuse-traps.md)
