# Phase 18 — Adapter API Contract Clarification (v2.0.0)

## Status
DESIGN — CLOSED (Contract-Accurate)

---

## Purpose

Phase 18 exists to **document and freeze the real, enforceable API contract**
of `maatify/data-adapters` as it exists in the current source code.

This phase is **descriptive only**.
No architectural refactors, API changes, or behavioral changes are introduced.

---

## Scope

This phase documents:

- The actual responsibilities of adapters
- The real coupling to `DatabaseResolver`
- Current lifecycle behavior (stateful, lazy connection)
- The boundaries of what this package **is** and **is not**

---

## What Phase 18 IS

Phase 18:

- Documents existing adapter behavior
- Makes implicit coupling explicit
- Defines what consumers may rely on **today**
- Establishes a contract baseline for later phases

---

## What Phase 18 IS NOT

Phase 18 does **not**:

- Decouple `DatabaseResolver`
- Introduce DI-first construction
- Add factories or new APIs
- Change adapter lifecycle semantics
- Remove repositories or traits
- Improve usability or DX

All such changes are explicitly deferred.

---

## Contractual Truths (As-Is)

Based strictly on current source:

### Adapter Construction
- Adapters are constructed via `DatabaseResolver`
- Profiles and configuration resolution are adapter-internal
- `EnvironmentConfig` is part of normal operation

### Lifecycle
- Adapters are stateful
- Lazy connection is supported
- `getDriver()` may trigger `connect()`

### Responsibilities
Adapters are responsible for:
- Connection lifecycle
- Configuration resolution
- Driver exposure
- Health checks

Adapters are **not** repositories, but repository-related code still exists
in this package at this phase.

---

## Explicit Non-Goals

- No code changes
- No refactors
- No removals
- No additions

This phase exists only to **clarify reality**.

---

## Outcome

Phase 18 produces a **contract-accurate baseline**
used to safely justify later structural changes
without relying on assumptions or intent.

---

## Next Phase

Phase 19 begins the **first transformative step**:
removing repository and query-related abstractions
that contradict this clarified contract.
