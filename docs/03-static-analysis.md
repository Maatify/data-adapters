[![Maatify Data Adapters](https://img.shields.io/badge/Maatify-Data%20Adapters-blue?style=for-the-badge)](../README.md)
[![Maatify Ecosystem](https://img.shields.io/badge/Maatify-Ecosystem-9C27B0?style=for-the-badge)](https://github.com/Maatify)

# Static Analysis & Type Safety

This document explains **why static analysis is a core requirement**
for using `maatify/data-adapters`, and how type safety is preserved
without runtime complexity.

---

## 1. Static Analysis Is Not Optional

This package is **designed** to be used with:

- PHPStan (Level Max)
- Psalm (strict mode)

Static analysis is not an enhancement here —  
it is a **design dependency**.

Without static analysis:
- The adapter contract loses most of its value
- Type safety degrades to `object`
- Misuse becomes easy and silent

---

## 2. The Core Problem: PHP Has No Native Generics

PHP cannot express:

- Generic interfaces
- Generic return types
- Template constraints

At runtime, this forces signatures like:

```php
public function getDriver(): object;
````

By itself, this is insufficient.

---

## 3. Docblock Generics as the Solution

Docblock templates are used to **restore type information** at analysis time.

The adapter contract is defined as:

```php
/**
 * @template TDriver of object
 */
interface AdapterInterface
{
    /** @return TDriver */
    public function getDriver(): object;
}
```

Concrete adapters then bind the template explicitly:

```php
/**
 * @implements AdapterInterface<PDO>
 */
final class MySQLPDOAdapter {}
```

This allows static analyzers to infer:

* The exact driver type
* Method availability
* Invalid usage at analysis time

---

## 4. Runtime vs Static Behavior (Important Distinction)

### Runtime

* `getDriver()` returns `object`
* No metadata
* No reflection
* No runtime type enforcement

### Static Analysis

* `getDriver()` is inferred as `PDO`, `Redis`, etc.
* IDE autocomplete works correctly
* Invalid calls are caught before execution

This separation is **intentional**.

---

## 5. Why Not Return the Concrete Type at Runtime?

Returning the concrete type would require:

* Multiple interfaces
* Union types
* Driver-specific contracts
* Runtime branching

All of these introduce:

* Complexity
* Fragile APIs
* Maintenance overhead

Using `object` + static analysis achieves:

* Maximum safety
* Minimum runtime cost

---

## 6. Required Tooling Configuration

To benefit from this design, projects **must**:

* Enable PHPStan or Psalm
* Allow docblock generics
* Run analysis as part of CI

Example (PHPStan):

```neon
parameters:
  level: max
```

Lower levels reduce the guarantees provided by this package.

---

## 7. Common Static Analysis Pitfalls

### Ignoring Generics

If generics are ignored:

* `getDriver()` degrades to `object`
* All driver-specific methods become invisible

### Suppressing Errors

Suppressing analysis errors:

* Defeats the purpose of adapters
* Reintroduces runtime surprises

---

## 8. Why This Is a Design Requirement

Static analysis enables:

* Deterministic behavior
* Early failure
* Safe refactoring
* Clear architectural boundaries

Without it, this package becomes a thin wrapper with no safety value.

---

## 9. Final Requirement Lock

Using this package **without static analysis**
is considered **unsupported usage**.

No runtime checks will be added to compensate.

---

## Related Documents

* [`01-scope.md`](01-scope.md)
* [`02-design-decisions.md`](02-design-decisions.md)
* [`04-misuse-traps.md`](04-misuse-traps.md)
