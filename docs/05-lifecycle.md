[![Maatify Data Adapters](https://img.shields.io/badge/Maatify-Data%20Adapters-blue?style=for-the-badge)](../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

# Lifecycle & Responsibility Boundaries

This document defines **what lifecycle responsibilities are explicitly
outside the scope** of `maatify/data-adapters`.

Understanding this is critical to correct usage.

---

## 1. No Lifecycle Management — By Design

This package **does not manage lifecycle**.

That means it does NOT:
- Open connections
- Close connections
- Reconnect on failure
- Check health or liveness
- Track connection state
- Handle graceful shutdown

Adapters simply **hold a driver reference**.

---

## 2. What an Adapter’s Lifetime Actually Is

An adapter’s lifetime is:

> Exactly the same as the lifetime of the driver instance it wraps.

- If the driver is short-lived → the adapter is short-lived
- If the driver is long-lived → the adapter is long-lived

The adapter adds **no lifecycle semantics** of its own.

---

## 3. Who Owns the Lifecycle?

### This package owns:
- Nothing related to lifecycle

### The application (or bootstrap layer) owns:
- Creating drivers
- Deciding driver scope (request / worker / singleton)
- Closing or recreating drivers
- Handling failures and retries
- Integrating with process managers

This separation is intentional.

---

## 4. Why Lifecycle Is Explicitly Excluded

Lifecycle management depends on:
- Execution model (FPM, CLI, workers)
- Infrastructure (containers, pools)
- Driver behavior
- Application requirements

Encoding lifecycle logic inside adapters would:
- Break determinism
- Introduce hidden behavior
- Force assumptions that do not hold universally

Therefore, lifecycle logic **must live elsewhere**.

---

## 5. Long-Lived Processes (Important)

In long-running processes (workers, daemons):

- Connections may become stale
- Network conditions may change
- Credentials may rotate

Because adapters do not handle lifecycle:
- You must recreate drivers when appropriate
- You must recreate adapters accordingly

Failing to do so is an application bug — not an adapter bug.

---

## 6. No “Close” or “Disconnect” Methods

Adapters intentionally do NOT expose:
- `close()`
- `disconnect()`
- `shutdown()`

Reasons:
- Not all drivers support explicit closing
- Semantics differ across drivers
- Providing these methods implies responsibility

Responsibility remains with the driver owner.

---

## 7. Serialization and Process Boundaries

Adapters and drivers are **process-bound objects**.

They must NOT:
- Cross process boundaries
- Be serialized
- Be cached between requests

If a process ends, adapters must be recreated.

---

## 8. Error Handling and Failures

Adapters:
- Do not catch runtime driver errors
- Do not retry failed operations
- Do not translate exceptions

Driver exceptions propagate as-is.

Error handling strategies belong to:
- Application services
- Resilience layers
- Higher-level infrastructure libraries

---

## 9. Final Boundary Statement

If you expect:
- Automatic reconnection
- Health monitoring
- Lifecycle hooks
- Managed shutdown

**This package is not responsible.**

It is intentionally small, explicit, and passive.

---

## Related Documents

- [`01-scope.md`](01-scope.md)
- [`04-misuse-traps.md`](04-misuse-traps.md)
- [`06-factories.md`](06-factories.md)
