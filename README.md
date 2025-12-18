![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---

[![Version](https://img.shields.io/packagist/v/maatify/data-adapters?label=Version&color=4C1)](https://packagist.org/packages/maatify/data-adapters)
[![PHP](https://img.shields.io/packagist/php-v/maatify/data-adapters?label=PHP&color=777BB3)](https://packagist.org/packages/maatify/data-adapters)
![PHP Version](https://img.shields.io/badge/php-%3E%3D8.4-blue)

[![Build](https://github.com/Maatify/data-adapters/actions/workflows/ci.yml/badge.svg?label=Build&color=brightgreen)](https://github.com/Maatify/data-adapters/actions/workflows/ci.yml)

![Monthly Downloads](https://img.shields.io/packagist/dm/maatify/data-adapters?label=Monthly%20Downloads&color=00A8E8)
![Total Downloads](https://img.shields.io/packagist/dt/maatify/data-adapters?label=Total%20Downloads&color=2AA9E0)

![Stars](https://img.shields.io/github/stars/Maatify/data-adapters?label=Stars&color=FFD43B)
[![License](https://img.shields.io/github/license/Maatify/data-adapters?label=License&color=blueviolet)](LICENSE)
![Status](https://img.shields.io/badge/Status-Stable-success)

![PHPStan](https://img.shields.io/badge/PHPStan-Level%20Max-4E8CAE)
![Coverage](https://img.shields.io/endpoint?url=https://raw.githubusercontent.com/Maatify/data-adapters/badges/coverage.json)

[![Changelog](https://img.shields.io/badge/Changelog-View-blue)](CHANGELOG.md)
[![Security](https://img.shields.io/badge/Security-Policy-important)](SECURITY.md)

---

# Maatify Data Adapters

**Clean, DI-first infrastructure adapters for real drivers.**

`maatify/data-adapters` provides **explicit adapter implementations**
around real infrastructure drivers such as PDO, Redis, and MongoDB.

It exists to act as a **dependency-injection boundary** — nothing more.

---

## What This Package IS

- A **DI boundary** around infrastructure drivers
- An **ownership wrapper** for real driver instances
- **Explicit by design** (no magic, no auto-detection)
- **Deterministic** and statically analyzable
- **100% testable** without real databases

➡️ See full scope definition:  
[`docs/01-scope.md`](docs/01-scope.md)

---

## What This Package IS NOT

This package is **NOT**:

- ❌ A unified database API
- ❌ An abstraction layer
- ❌ An ORM or query builder
- ❌ A repository layer
- ❌ A connection manager
- ❌ A configuration loader
- ❌ A lifecycle or retry system

If you expect convenience or API unification, **do not use this package**.

---

## Supported Drivers

### MySQL
- PDO
- Doctrine DBAL (`Connection`)

### Redis
- `ext-redis`
- `Predis\Client`

### MongoDB
- `MongoDB\Database`

> Driver choice is **explicit** and decided by the application or DI container.

---

## Core Mental Model

```text
Application
   ↓
Configuration / Secrets / Env   (outside this package)
   ↓
Real Driver (PDO / Redis / Mongo)
   ↓
Adapter (DI boundary only)
   ↓
Application / Higher Layers
````

* No env access
* No runtime detection
* No hidden behavior

---

## Adapter Contract

All adapters implement a **minimal contract**:

```php
interface AdapterInterface
{
    public function getDriver(): object;
}
```

* Runtime return type is `object`
* Static typing is preserved via **docblock generics**
* No unified API is introduced

➡️ Static analysis details:
[`docs/03-static-analysis.md`](docs/03-static-analysis.md)

---

## Available Adapters

* `MySQLPDOAdapter`
* `MySQLDBALAdapter`
* `RedisAdapter` (ext-redis)
* `RedisPredisAdapter`
* `MongoDatabaseAdapter`

Each adapter:

* Accepts a ready driver instance
* Stores it
* Returns it via `getDriver()`

Nothing else.

---

## Factories (Optional Convenience)

Factories exist only to **reduce boilerplate**.

* No env reading
* No auto-detection
* No hidden defaults
* Typed error boundary via `AdapterCreationException`

Factories are optional and not required for normal usage.

➡️ See:
[`docs/06-factories.md`](docs/06-factories.md)

---

## Usage Philosophy

This package enforces:

* Explicit DI
* Explicit configuration
* Explicit error handling
* Explicit responsibility boundaries

It intentionally avoids:

* Full examples
* Framework-specific helpers
* Runtime convenience

---

## Common Misuse Warnings

* ❌ Serializing adapters or drivers
* ❌ Expecting unified behavior
* ❌ Treating adapters as services

➡️ Detailed warnings:
[`docs/04-misuse-traps.md`](docs/04-misuse-traps.md)

---

## When to Use This Package

Use it if you want:

* Predictable infrastructure boundaries
* Explicit DI
* Full control over drivers

---

## When NOT to Use This Package

Do NOT use it if you want:

* Automatic configuration
* API unification
* Runtime magic

---

## Documentation Index

* [Scope & Boundaries](docs/01-scope.md)
* [Design Decisions](docs/02-design-decisions.md)
* [Static Analysis](docs/03-static-analysis.md)
* [Misuse Traps](docs/04-misuse-traps.md)
* [Lifecycle](docs/05-lifecycle.md)
* [Factories](docs/06-factories.md)
* [Dependency Policy & Matrix](docs/07-dependencies.md)

---

## Examples

The following examples demonstrate **explicit, real-world usage**
of `maatify/data-adapters` with supported drivers.

These examples are intentionally minimal:
- No frameworks
- No helpers
- No bootstrap logic
- Explicit driver creation and adapter usage

### MySQL
- [PDO Example](examples/mysql/pdo.php)
- [Doctrine DBAL Example](examples/mysql/dbal.php)

### Redis
- [ext-redis Example](examples/redis/ext-redis.php)
- [Predis Example](examples/redis/predis.php)

### MongoDB
- [MongoDB Database Example](examples/mongo/database.php)

---

## 🪪 License

**[MIT License](LICENSE)**
© [Maatify.dev](https://www.maatify.dev) — Free to use, modify, and distribute with attribution.

---

## 👤 Author

Engineered by **Mohamed Abdulalim** ([@megyptm](https://github.com/megyptm))
Backend Lead & Technical Architect — [https://www.maatify.dev](https://www.maatify.dev)

---

## 🤝 Contributors

Special thanks to the Maatify.dev engineering team and all open-source contributors.

Contributions are welcome.
Please read the [Contributing Guide](CONTRIBUTING.md) before opening a PR.

---

<p align="center">
  <sub>Built with ❤️ by <a href="https://www.maatify.dev">Maatify.dev</a> — Infrastructure-first PHP libraries</sub>
</p>