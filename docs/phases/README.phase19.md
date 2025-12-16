# Phase 19 — Repository Layer Removal & Responsibility Realignment (v2.0.0)

## Status
DESIGN — APPROVED FOR IMPLEMENTATION

---

## Purpose

Phase 19 removes **all repository and query-related abstractions**
from `maatify/data-adapters` and realigns the package
to function strictly as a **connection and driver access library**.

This phase performs **structural cleanup**, not architectural redesign.

---

## Scope

Phase 19 covers:

- Deleting repository-layer code from this package
- Removing repository-related traits
- Updating tests to use adapters directly
- Clarifying responsibility boundaries

---

## Components to Be Removed

The following are removed entirely:

### Directories
- `src/Repository/**`

### Classes
- `BaseMongoRepository`
- `BaseMySQLRepository`
- `BaseRedisRepository`

### Traits
- `ProvidesRawAccessTrait`

These removals are **intentional breaking changes**.

---

## Components to Be Retained

Phase 19 explicitly retains:

- Adapters and adapter APIs
- `BaseAdapter`
- `DatabaseResolver`
- Configuration builders and registry logic
- Adapter lifecycle and raw driver exposure

No adapter behavior changes occur in this phase.

---

## Responsibility Realignment

After Phase 19:

### `maatify/data-adapters` is responsible for:
- Connection lifecycle
- Configuration resolution
- Driver exposure
- Health checks

### Responsibilities moved out:
- Repository base classes
- Query abstractions
- Pagination
- Domain data access patterns

These belong in:
- `maatify/data-repository`
- Consumer applications
- ORM / DBAL / ODM libraries

---

## Public API Impact

### Removed (Breaking)
- All repository base classes
- `ProvidesRawAccessTrait`
- Any repository-mediated raw access

### Unchanged
- Adapter APIs
- Resolver behavior
- Driver access through adapters

No compatibility shims are added.

---

## Non-Goals (Locked)

Phase 19 does **not**:

- Refactor adapters
- Change resolver behavior
- Introduce DI-first construction
- Add new APIs or helpers
- Modify lifecycle semantics

Those are reserved for later phases.

---

## Acceptance Criteria

Phase 19 is complete when:

- `src/Repository/**` does not exist
- `ProvidesRawAccessTrait.php` does not exist
- No references remain in `src/**` or `tests/**`
- Tests pass using adapters directly
- No scope expansion occurs

---

## Role in Roadmap

Phase 19 is the **first enforcement phase**
that acts on the Phase 18 contract clarification.

It prepares the codebase for later architectural reform
without making assumptions or premature redesigns.
