![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---

[![Version](https://img.shields.io/packagist/v/maatify/data-adapters?label=Version&color=4C1)](https://packagist.org/packages/maatify/data-adapters)
[![PHP](https://img.shields.io/packagist/php-v/maatify/data-adapters?label=PHP&color=777BB3)](https://packagist.org/packages/maatify/data-adapters)
[![Build](https://github.com/Maatify/data-adapters/actions/workflows/test.yml/badge.svg?label=Build&color=brightgreen)](https://github.com/Maatify/data-adapters/actions/workflows/test.yml)

[![Monthly Downloads](https://img.shields.io/packagist/dm/maatify/data-adapters?label=Monthly%20Downloads&color=00A8E8)](https://packagist.org/packages/maatify/data-adapters)
[![Total Downloads](https://img.shields.io/packagist/dt/maatify/data-adapters?label=Total%20Downloads&color=2AA9E0)](https://packagist.org/packages/maatify/data-adapters)

[![Stars](https://img.shields.io/github/stars/Maatify/data-adapters?label=Stars&color=FFD43B&cacheSeconds=3600)](https://github.com/Maatify/data-adapters/stargazers)
[![License](https://img.shields.io/github/license/Maatify/data-adapters?label=License&color=blueviolet)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Stable-success?style=flat-square)]()
[![Code Quality](https://img.shields.io/codefactor/grade/github/Maatify/data-adapters/main?color=brightgreen)](https://www.codefactor.io/repository/github/Maatify/data-adapters)

[![Changelog](https://img.shields.io/badge/Changelog-View-blue)](CHANGELOG.md)
[![Security](https://img.shields.io/badge/Security-Policy-important)](SECURITY.md)

---

# 📦 maatify/data-adapters
**Unified Data Connectivity & Diagnostics Layer**

---

> 🔗 [بالعربي 🇸🇦 ](./README-AR.md)

## 🧭 Overview

**maatify/data-adapters** is a unified, framework-agnostic layer for managing Redis, MongoDB,  
and MySQL connections with centralized diagnostics and auto-detection.  
It serves as the core data layer of the **Maatify Ecosystem**.

---

## ⚙️ Installation

```bash
composer require maatify/data-adapters
```

---

> **Requirements:**  
> • PHP ≥ 8.4  
> • Redis (phpredis recommended — Predis auto-fallback)  
> • MongoDB extension (optional)  
> • PDO MySQL required (DBAL optional)

---

## ✨ Features
- Unified adapters for **MySQL, Redis, MongoDB**
- Multi-profile MySQL (`mysql.main`, `mysql.logs`, …)
- Automatic Redis driver detection (phpredis → predis)
- Centralized diagnostics & health checks

---

## 🧩 Compatibility
Fully framework-agnostic.  
Optional auto-wiring available via **maatify/bootstrap**.

---

## 🚀 Quick Usage

```php
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Core\DatabaseResolver;

$config   = new EnvironmentConfig(__DIR__);
$resolver = new DatabaseResolver($config);

// MySQL — default or profile-based routing
$mainDb     = $resolver->resolve("mysql.main", autoConnect: true);
$logsDb     = $resolver->resolve("mysql.logs");
$analyticsDb = $resolver->resolve("mysql.analytics");

// Dynamic custom profiles (Phase 11)
$billingDb  = $resolver->resolve("mysql.billing");

// Redis
$redis = $resolver->resolve("redis", autoConnect: true);

// MongoDB
$mongo = $resolver->resolve("mongo.main");
$mongo->connect();

```

---

## 🧩 Diagnostics & Health Checks

All adapters include self-diagnostic capabilities and unified health reporting.

```php
use Maatify\DataAdapters\Diagnostics\DiagnosticService;

$diagnostic = new DiagnosticService($config, $resolver);
echo $diagnostic->toJson();
```

**Example Output**

```json
{
  "diagnostics": [
    {"adapter": "redis", "connected": true},
    {"adapter": "mongo", "connected": true},
    {"adapter": "mysql", "connected": true}
  ]
}
```

## 🧪 Testing

```bash
vendor/bin/phpunit
```

**Coverage:** > 87 %  
**Status:** ✅ All tests passing (integration, diagnostics, fallback)

---


## 📚 Documentation

* **Introduction:** [`docs/README.intro.md`](docs/README.intro.md)
* **Environment Reference:** [`docs/env.md`](docs/env.md)
* **Telemetry:** [`docs/telemetry.md`](docs/telemetry.md)
* **Architecture:** [`docs/architecture.md`](docs/architecture.md)
* **Multi-Profile MySQL:** [`docs/mysql-profiles.md`](docs/mysql-profiles.md)
* **Phases:** [`docs/README.roadmap.md`](docs/README.roadmap.md)
* **Changelog:** [`CHANGELOG.md`](CHANGELOG.md)

---

## 🔗 Related Maatify Libraries

* [maatify/common](https://github.com/Maatify/common)
* [maatify/psr-logger](https://github.com/Maatify/psr-logger)
* [maatify/bootstrap](https://github.com/Maatify/bootstrap)
* [maatify/rate-limiter](https://github.com/Maatify/rate-limiter)
* [maatify/security-guard](https://github.com/Maatify/security-guard)
* [maatify/mongo-activity](https://github.com/Maatify/mongo-activity)

---
> 🔗 **Full documentation & release notes:** see [/docs/README.full.md](docs/README.full.md)
---

## 🪪 License

**[MIT license](LICENSE)** © [Maatify.dev](https://www.maatify.dev)  
You’re free to use, modify, and distribute this library with attribution.

---

## 👤 Author
**Mohamed Abdulalim** — Backend Lead & Technical Architect  
🔗 https://www.maatify.dev | ✉️ mohamed@maatify.dev

## 🤝 Contributors
Special thanks to the Maatify.dev engineering team and open-source contributors.


---

<p align="center">
  <sub><span style="color:#777">Built with ❤️ by <a href="https://www.maatify.dev">Maatify.dev</a> — Unified Ecosystem for Modern PHP Libraries</span></sub>
</p>
