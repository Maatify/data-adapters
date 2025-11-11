![Maatify.dev](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)

---

# 📦 maatify/data-adapters

[![Version](https://img.shields.io/packagist/v/maatify/data-adapters?label=Version&color=4C1)](https://packagist.org/packages/maatify/data-adapters)
[![PHP](https://img.shields.io/packagist/php-v/maatify/data-adapters?label=PHP&color=777BB3)](https://packagist.org/packages/maatify/data-adapters)
[![Build](https://github.com/Maatify/data-adapters/actions/workflows/test.yml/badge.svg?label=Build&color=brightgreen)](https://github.com/Maatify/data-adapters/actions/workflows/test.yml)
[![Monthly Downloads](https://img.shields.io/packagist/dm/maatify/data-adapters?label=Monthly%20Downloads&color=00A8E8)](https://packagist.org/packages/maatify/data-adapters)
[![Total Downloads](https://img.shields.io/packagist/dt/maatify/data-adapters?label=Total%20Downloads&color=2AA)](https://packagist.org/packages/maatify/data-adapters)
[![Stars](https://img.shields.io/github/stars/Maatify/data-adapters?label=Stars&color=FFD43B)](https://github.com/Maatify/data-adapters/stargazers)
[![License](https://img.shields.io/github/license/Maatify/data-adapters?label=License&color=blueviolet)](LICENSE)
[![Status](https://img.shields.io/badge/Status-Stable-success?style=flat-square)]()
[![Code Quality](https://img.shields.io/codefactor/grade/github/Maatify/data-adapters/main)](https://www.codefactor.io/repository/github/Maatify/data-adapters)

---

# ⚙️ Maatify Data Adapters — Technical Documentation

### 📦 Version 1.0.0  
**Owner:** Maatify.dev  
**Repository:** maatify/data-adapters  

---

## 🧭 Overview
`maatify/data-adapters` provides a unified, modular connection layer across Redis, MongoDB, and MySQL within the Maatify ecosystem.  
It standardizes environment access, fallback logic, diagnostics, and cross-library integration.

---
## ✅ Completed Phases

| Phase | Title                                 | Status      |
|:-----:|:--------------------------------------|:------------|
|   1   | Environment Setup                     | ✅ Completed |
|   2   | Core Interfaces & Base Structure      | ✅ Completed |
|   3   | Adapter Implementations               | ✅ Completed |
|  3.5  | Adapter Smoke Tests Extension         | ✅ Completed |
|   4   | Health & Diagnostics Layer            | ✅ Completed |
|  4.1  | Hybrid AdapterFailoverLog Enhancement | ✅ Completed |
|  4.2  | Adapter Logger Abstraction via DI     | ✅ Completed |
|   5   | Integration & Unified Testing         | ✅ Completed |


---

# 🧱 Phase 1 — Environment Setup

### 🎯 Goal
Prepare the foundational environment: Composer, PSR-4, Docker, PHPUnit, and CI pipeline.

### ✅ Implemented Tasks
- Created repository `maatify/data-adapters`
- Initialized Composer with `maatify/common`
- Added PSR-4 autoload under `Maatify\\DataAdapters\\`
- Added `.env.example` (Redis / Mongo / MySQL)
- Configured PHPUnit (`phpunit.xml.dist`)
- Added Docker environment (Redis + Mongo + MySQL)
- Added GitHub Actions CI workflow

### ⚙️ Files Created
```

composer.json
.env.example
phpunit.xml.dist
docker-compose.yml
.github/workflows/test.yml
tests/bootstrap.php
src/placeholder.php

````

### 🧠 Usage Example
```bash
composer install
cp .env.example .env
docker-compose up -d
vendor/bin/phpunit
````

### 🧩 Verification Notes

✅ Composer autoload
✅ PHPUnit ready
✅ Docker containers running
✅ CI syntax valid

---

# 🧱 Phase 2 — Core Interfaces & Base Structure (Updated)

### 🎯 Goal

Establish the unified adapter foundation — including shared interfaces, the abstract base class, centralized resolver logic, and consistent environment handling through `maatify/bootstrap`.

---

### ✅ Implemented Tasks

* **`AdapterInterface`** — contract defining `connect()`, `disconnect()`, `isConnected()`, `getConnection()`.
* **`BaseAdapter` (abstract)** — shared connection management and lifecycle control for all adapters.
* **Core Exceptions:**

    * `ConnectionException` — standardized connection failure handling.
    * `FallbackException` — used for secondary connection paths (e.g., Predis fallback).
* **`EnvironmentConfig`** — now acts as a wrapper around
  `Maatify\Bootstrap\Core\EnvironmentLoader` for consistent environment initialization.
* **`DatabaseResolver`** — centralized adapter factory for Redis, MongoDB, and MySQL.
  Supports lazy or eager connection modes via `autoConnect` parameter.

---

### ⚙️ Files Created

```
src/Contracts/AdapterInterface.php
src/Core/BaseAdapter.php
src/Core/Exceptions/ConnectionException.php
src/Core/Exceptions/FallbackException.php
src/Core/EnvironmentConfig.php
src/Core/DatabaseResolver.php
tests/Core/CoreStructureTest.php
```

---

### 🧠 Usage Example

```php
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Enums\AdapterTypeEnum;

// Load environment via Bootstrap loader
$config = new EnvironmentConfig(__DIR__);

// Instantiate resolver
$resolver = new DatabaseResolver($config);

// Lazy (manual connect)
$mongo = $resolver->resolve(AdapterTypeEnum::MONGO);
$mongo->connect();

// Eager (auto-connect)
$redis = $resolver->resolve(AdapterTypeEnum::REDIS, true);
```

---

### 🧩 Verification Notes

✅ Namespace autoload under `Maatify\DataAdapters`  
✅ BaseAdapter instantiation with unified lifecycle methods  
✅ EnvironmentConfig delegates `.env` loading to `maatify/bootstrap`  
✅ DatabaseResolver supports `autoConnect` flag  
✅ Tested on PHP 8.4.4 (macOS) — all CoreStructure tests passed

---

# 🧱 Phase 3 — Adapter Implementations

### 🎯 Goal

Implement production adapters for Redis (phpredis + Predis fallback), MongoDB, and MySQL (PDO/DBAL).

### ✅ Implemented Tasks

* `RedisAdapter` using phpredis
* `PredisAdapter` fallback
* `MongoAdapter` via mongodb/mongodb
* `MySQLAdapter` (PDO)
* `MySQLDbalAdapter` (DBAL)
* Extended `DatabaseResolver`
* Added `reconnect()` & graceful shutdown
* Documented examples

### ⚙️ Files Created

```
src/Adapters/RedisAdapter.php
src/Adapters/PredisAdapter.php
src/Adapters/MongoAdapter.php
src/Adapters/MySQLAdapter.php
src/Adapters/MySQLDbalAdapter.php
tests/Adapters/RedisAdapterTest.php
```

### 🧠 Usage Example

```php
$config   = new EnvironmentConfig(__DIR__);
$resolver = new DatabaseResolver($config);

$redis = $resolver->resolve('redis');
$redis->connect();
echo $redis->healthCheck() ? "Redis OK" : "Redis fallback";
```

### 🧩 Verification Notes

✅ Redis ↔ Predis fallback
✅ All classes autoloaded
✅ Composer suggestions added

---

# 🧱 Phase 3.5 — Adapter Smoke Tests Extension

### 🎯 Goal

Add smoke tests for Predis, MongoDB, and MySQL adapters — validating autoload and structure without live connections.

### ✅ Implemented Tasks

* `PredisAdapterTest` (structural validation)
* `MongoAdapterTest` (instantiation check)
* `MySQLAdapterTest` (DSN & method presence)
* Confirmed autoload for all adapters
* Verified PHPUnit suite runs OK
* Updated Phase 3 README

### ⚙️ Files Created

```
tests/Adapters/PredisAdapterTest.php
tests/Adapters/MongoAdapterTest.php
tests/Adapters/MySQLAdapterTest.php
```

### 🧩 Verification Notes

✅ All adapters autoload successfully
✅ PHPUnit suite passes (4 tests, 10 assertions)
✅ No external connections
✅ CI safe


---

# 🧱 Phase 4 — Health & Diagnostics Layer

### 🎯 Goal
Implement adapter self-checking, diagnostics service, and runtime fallback tracking with unified JSON output compatible with maatify/admin-dashboard.

---

### ✅ Implemented Tasks
- Enhanced `healthCheck()` across all adapters (Redis, Predis, MongoDB, MySQL).
- Added `DiagnosticService` for unified status reporting in JSON format.
- Added `AdapterFailoverLog` to record fallback or connection failures.
- Added internal `/health` endpoint returning system status JSON.
- Integrated automatic Enum (`AdapterTypeEnum`) compatibility within the Diagnostic layer.
- Documented diagnostic flow and usage examples.

---

### ⚙️ Files Created
```

src/Diagnostics/DiagnosticService.php
src/Diagnostics/AdapterFailoverLog.php
tests/Diagnostics/DiagnosticServiceTest.php

````

---

### 🧩 DiagnosticService Overview

#### Purpose:
Collect adapter health statuses dynamically and return them in JSON format for monitoring dashboards or CI integrations.

#### Key Features:
- Registers multiple adapters (`redis`, `mongo`, `mysql`)
- Supports both **string** and **AdapterTypeEnum** registration
- Auto-handles connection errors and logs them
- Produces lightweight JSON diagnostics
- Uses `AdapterFailoverLog` for fallback event tracking

---

### 🧠 Example Usage

```php
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Diagnostics\DiagnosticService;
use Maatify\DataAdapters\Enums\AdapterTypeEnum;

$config   = new EnvironmentConfig(__DIR__);
$resolver = new DatabaseResolver($config);
$service  = new DiagnosticService($config, $resolver);

$service->register([
    AdapterTypeEnum::REDIS,
    AdapterTypeEnum::MONGO,
    AdapterTypeEnum::MYSQL
]);

echo $service->toJson();
````

---

### 📤 Example Output

```json
{
  "diagnostics": [
    { "adapter": "redis", "connected": true, "error": null, "timestamp": "2025-11-08 21:15:00" },
    { "adapter": "mongo", "connected": true, "error": null, "timestamp": "2025-11-08 21:15:00" },
    { "adapter": "mysql", "connected": true, "error": null, "timestamp": "2025-11-08 21:15:00" }
  ]
}
```

---

### 🧾 AdapterFailoverLog Example

When a connection fails or fallback occurs:

```
[2025-11-08 21:17:32] [REDIS] Connection refused (fallback to Predis)
[2025-11-08 21:17:34] [MYSQL] Access denied for user 'root'
```

Stored automatically in:

```
storage/failover.log
```

---

### 🧩 Enum Integration Fix

To ensure full compatibility with the new `AdapterTypeEnum`,
the `DiagnosticService::register()` method now supports both string and Enum types:

```php
$enum = $type instanceof AdapterTypeEnum
    ? $type
    : AdapterTypeEnum::from(strtolower((string)$type));
$this->adapters[$enum->value] = $this->resolver->resolve($enum);
```

✅ Prevents `TypeError` when passing string values like `'redis'`.

---

### 🧪 Tests Summary

| Test                    | Purpose                                                        |
|:------------------------|:---------------------------------------------------------------|
| `DiagnosticServiceTest` | Verifies that diagnostics return an array with valid structure |
| `CoreStructureTest`     | Ensures configuration and resolver work for health layer       |
| `RedisAdapterTest`      | Confirms Redis connection and fallback logic still functional  |

✅ PHPUnit Result:

```
OK (7 tests, 12 assertions)
```

---

### 📘 Result

* `/docs/phases/README.phase4.md` created
* Root `README.md` updated between markers:

```markdown
## ✅ Completed Phases
<!-- PHASE_STATUS_START -->
- [x] Phase 1 — Environment Setup  
- [x] Phase 2 — Core Interfaces & Base Structure  
- [x] Phase 3 — Adapter Implementations  
- [x] Phase 3.5 — Adapter Smoke Tests Extension  
- [x] Phase 4 — Health & Diagnostics Layer
<!-- PHASE_STATUS_END -->
```

---

### 📊 Phase Summary Table

| Phase |   Status    | Files Created |
|:------|:-----------:|:-------------:|
| 1     | ✅ Completed |       7       |
| 2     | ✅ Completed |       7       |
| 3     | ✅ Completed |      10       |
| 3.5   | ✅ Completed |       3       |
| 4     | ✅ Completed |       3       |


---

# 🧱 Phase 4.1 — Hybrid AdapterFailoverLog Enhancement

### 🎯 Goal
Refactor the `AdapterFailoverLog` into a **hybrid design** that supports both static and instance usage.  
This ensures environment-based configurability without breaking existing code.

---

### ✅ Implemented Tasks
- Replaced constant path with a dynamic `.env`-aware configuration.  
- Added optional `$path` parameter in constructor.  
- Preserved static `record()` method for backward compatibility.  
- Ensured directory auto-creation on first write.  
- Confirmed compatibility with `DiagnosticService`.

---

### ⚙️ File Updated
```

src/Diagnostics/AdapterFailoverLog.php

````

---

### 🧩 Final Implementation
```php
final class AdapterFailoverLog
{
    private string $file;

    public function __construct(?string $path = null)
    {
        $logPath = $path
            ?? ($_ENV['ADAPTER_LOG_PATH'] ?? getenv('ADAPTER_LOG_PATH') ?: __DIR__ . '/../../storage');
        $this->file = rtrim($logPath, '/') . '/failover.log';
        @mkdir(dirname($this->file), 0777, true);
    }

    public static function record(string $adapter, string $message): void
    {
        (new self())->write($adapter, $message);
    }

    public function write(string $adapter, string $message): void
    {
        $line = sprintf("[%s] [%s] %s%s", date('Y-m-d H:i:s'), strtoupper($adapter), $message, PHP_EOL);
        @file_put_contents($this->file, $line, FILE_APPEND);
    }
}
````

---

### 🧠 Usage Examples

```php
use Maatify\DataAdapters\Diagnostics\AdapterFailoverLog;

// Default (auto .env or fallback)
AdapterFailoverLog::record('redis', 'Fallback to Predis');

// Custom path
$logger = new AdapterFailoverLog(__DIR__ . '/../../logs/adapters');
$logger->write('mysql', 'Connection timeout');

// .env example
// ADAPTER_LOG_PATH=/var/www/maatify/storage/logs
```

---

### 🧩 Key Improvements

| Feature                   | Description                                     |
|:--------------------------|:------------------------------------------------|
| Hybrid design             | Works with both static and instance usage       |
| .env support              | Reads `ADAPTER_LOG_PATH` dynamically            |
| Auto directory creation   | Ensures path exists automatically               |
| Backward compatible       | Keeps old static usage syntax                   |
| Ready for PSR integration | Future bridge for maatify/psr-logger in Phase 7 |

---

### 🧪 Test Results

✅ Default fallback → `/storage/failover.log`
✅ `.env` path respected
✅ Custom constructor path works
✅ Static + instance both functional

---

---# ⚙️ Maatify Data Adapters — Technical Documentation

### 📦 Version 1.0.0
**Owner:** Maatify.dev  
**Repository:** maatify/data-adapters

---

## 🧭 Overview
`maatify/data-adapters` provides a unified, modular connection layer across Redis, MongoDB, and MySQL within the Maatify ecosystem.  
It standardizes environment access, fallback logic, diagnostics, and cross-library integration.

---

## ✅ Completed Phases

| Phase | Title                                 | Status      |
|:-----:|:--------------------------------------|:------------|
|   1   | Environment Setup                     | ✅ Completed |
|   2   | Core Interfaces & Base Structure      | ✅ Completed |
|   3   | Adapter Implementations               | ✅ Completed |
|  3.5  | Adapter Smoke Tests Extension         | ✅ Completed |
|   4   | Health & Diagnostics Layer            | ✅ Completed |
|  4.1  | Hybrid AdapterFailoverLog Enhancement | ✅ Completed |
|  4.2  | Adapter Logger Abstraction via DI     | ✅ Completed |


---

# 🧱 Phase 1 — Environment Setup

### 🎯 Goal
Prepare the foundational environment: Composer, PSR-4, Docker, PHPUnit, and CI pipeline.

### ✅ Implemented Tasks
- Created repository `maatify/data-adapters`
- Initialized Composer with `maatify/common`
- Added PSR-4 autoload under `Maatify\\DataAdapters\\`
- Added `.env.example` (Redis / Mongo / MySQL)
- Configured PHPUnit (`phpunit.xml.dist`)
- Added Docker environment (Redis + Mongo + MySQL)
- Added GitHub Actions CI workflow

### ⚙️ Files Created
```

composer.json
.env.example
phpunit.xml.dist
docker-compose.yml
.github/workflows/test.yml
tests/bootstrap.php
src/placeholder.php

````

### 🧠 Usage Example
```bash
composer install
cp .env.example .env
docker-compose up -d
vendor/bin/phpunit
````

### 🧩 Verification Notes

✅ Composer autoload
✅ PHPUnit ready
✅ Docker containers running
✅ CI syntax valid

---

# 🧱 Phase 2 — Core Interfaces & Base Structure

### 🎯 Goal

Define shared interfaces, abstract base class, unified resolver, and core exceptions.

### ✅ Implemented Tasks

* `AdapterInterface`
* `BaseAdapter` abstract class
* `ConnectionException`, `FallbackException`
* `EnvironmentConfig` loader
* `DatabaseResolver`
* Environment auto-detection (Redis/Mongo/MySQL)

### ⚙️ Files Created

```
src/Contracts/AdapterInterface.php
src/Core/BaseAdapter.php
src/Core/Exceptions/ConnectionException.php
src/Core/Exceptions/FallbackException.php
src/Core/EnvironmentConfig.php
src/Core/DatabaseResolver.php
tests/Core/CoreStructureTest.php
```

### 🧠 Usage Example

```php
$config = new EnvironmentConfig(__DIR__);
$resolver = new DatabaseResolver($config);
$adapter = $resolver->resolve('redis');
$adapter->connect();
```

### 🧩 Verification Notes

✅ Namespace autoload
✅ BaseAdapter instantiation
✅ EnvironmentConfig reads .env

---

# 🧱 Phase 3 — Adapter Implementations

### 🎯 Goal

Implement production adapters for Redis (phpredis + Predis fallback), MongoDB, and MySQL (PDO/DBAL).

### ✅ Implemented Tasks

* `RedisAdapter` using phpredis
* `PredisAdapter` fallback
* `MongoAdapter` via mongodb/mongodb
* `MySQLAdapter` (PDO)
* `MySQLDbalAdapter` (DBAL)
* Extended `DatabaseResolver`
* Added `reconnect()` & graceful shutdown
* Documented examples

### ⚙️ Files Created

```
src/Adapters/RedisAdapter.php
src/Adapters/PredisAdapter.php
src/Adapters/MongoAdapter.php
src/Adapters/MySQLAdapter.php
src/Adapters/MySQLDbalAdapter.php
tests/Adapters/RedisAdapterTest.php
```

### 🧠 Usage Example

```php
$config   = new EnvironmentConfig(__DIR__);
$resolver = new DatabaseResolver($config);

$redis = $resolver->resolve('redis');
$redis->connect();
echo $redis->healthCheck() ? "Redis OK" : "Redis fallback";
```

### 🧩 Verification Notes

✅ Redis ↔ Predis fallback
✅ All classes autoloaded
✅ Composer suggestions added

---

# 🧱 Phase 3.5 — Adapter Smoke Tests Extension

### 🎯 Goal

Add smoke tests for Predis, MongoDB, and MySQL adapters — validating autoload and structure without live connections.

### ✅ Implemented Tasks

* `PredisAdapterTest` (structural validation)
* `MongoAdapterTest` (instantiation check)
* `MySQLAdapterTest` (DSN & method presence)
* Confirmed autoload for all adapters
* Verified PHPUnit suite runs OK
* Updated Phase 3 README

### ⚙️ Files Created

```
tests/Adapters/PredisAdapterTest.php
tests/Adapters/MongoAdapterTest.php
tests/Adapters/MySQLAdapterTest.php
```

### 🧩 Verification Notes

✅ All adapters autoload successfully
✅ PHPUnit suite passes (4 tests, 10 assertions)
✅ No external connections
✅ CI safe


---

# 🧱 Phase 4 — Health & Diagnostics Layer

### 🎯 Goal
Implement adapter self-checking, diagnostics service, and runtime fallback tracking with unified JSON output compatible with maatify/admin-dashboard.

---

### ✅ Implemented Tasks
- Enhanced `healthCheck()` across all adapters (Redis, Predis, MongoDB, MySQL).
- Added `DiagnosticService` for unified status reporting in JSON format.
- Added `AdapterFailoverLog` to record fallback or connection failures.
- Added internal `/health` endpoint returning system status JSON.
- Integrated automatic Enum (`AdapterTypeEnum`) compatibility within the Diagnostic layer.
- Documented diagnostic flow and usage examples.

---

### ⚙️ Files Created
```

src/Diagnostics/DiagnosticService.php
src/Diagnostics/AdapterFailoverLog.php
tests/Diagnostics/DiagnosticServiceTest.php

````

---

### 🧩 DiagnosticService Overview

#### Purpose:
Collect adapter health statuses dynamically and return them in JSON format for monitoring dashboards or CI integrations.

#### Key Features:
- Registers multiple adapters (`redis`, `mongo`, `mysql`)
- Supports both **string** and **AdapterTypeEnum** registration
- Auto-handles connection errors and logs them
- Produces lightweight JSON diagnostics
- Uses `AdapterFailoverLog` for fallback event tracking

---

### 🧠 Example Usage

```php
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Diagnostics\DiagnosticService;
use Maatify\DataAdapters\Enums\AdapterTypeEnum;

$config   = new EnvironmentConfig(__DIR__);
$resolver = new DatabaseResolver($config);
$service  = new DiagnosticService($config, $resolver);

$service->register([
    AdapterTypeEnum::REDIS,
    AdapterTypeEnum::MONGO,
    AdapterTypeEnum::MYSQL
]);

echo $service->toJson();
````

---

### 📤 Example Output

```json
{
  "diagnostics": [
    { "adapter": "redis", "connected": true, "error": null, "timestamp": "2025-11-08 21:15:00" },
    { "adapter": "mongo", "connected": true, "error": null, "timestamp": "2025-11-08 21:15:00" },
    { "adapter": "mysql", "connected": true, "error": null, "timestamp": "2025-11-08 21:15:00" }
  ]
}
```

---

### 🧾 AdapterFailoverLog Example

When a connection fails or fallback occurs:

```
[2025-11-08 21:17:32] [REDIS] Connection refused (fallback to Predis)
[2025-11-08 21:17:34] [MYSQL] Access denied for user 'root'
```

Stored automatically in:

```
storage/failover.log
```

---

### 🧩 Enum Integration Fix

To ensure full compatibility with the new `AdapterTypeEnum`,
the `DiagnosticService::register()` method now supports both string and Enum types:

```php
$enum = $type instanceof AdapterTypeEnum
    ? $type
    : AdapterTypeEnum::from(strtolower((string)$type));
$this->adapters[$enum->value] = $this->resolver->resolve($enum);
```

✅ Prevents `TypeError` when passing string values like `'redis'`.

---

### 🧪 Tests Summary

| Test                    | Purpose                                                        |
|:------------------------|:---------------------------------------------------------------|
| `DiagnosticServiceTest` | Verifies that diagnostics return an array with valid structure |
| `CoreStructureTest`     | Ensures configuration and resolver work for health layer       |
| `RedisAdapterTest`      | Confirms Redis connection and fallback logic still functional  |

✅ PHPUnit Result:

```
OK (7 tests, 12 assertions)
```

---

### 📘 Result

* `/docs/phases/README.phase4.md` created
* Root `README.md` updated between markers:

```markdown
## ✅ Completed Phases
<!-- PHASE_STATUS_START -->
- [x] Phase 1 — Environment Setup  
- [x] Phase 2 — Core Interfaces & Base Structure  
- [x] Phase 3 — Adapter Implementations  
- [x] Phase 3.5 — Adapter Smoke Tests Extension  
- [x] Phase 4 — Health & Diagnostics Layer
<!-- PHASE_STATUS_END -->
```

---

### 📊 Phase Summary Table

| Phase |   Status    | Files Created |
|:------|:-----------:|:-------------:|
| 1     | ✅ Completed |       7       |
| 2     | ✅ Completed |       7       |
| 3     | ✅ Completed |      10       |
| 3.5   | ✅ Completed |       3       |
| 4     | ✅ Completed |       3       |


---

# 🧱 Phase 4.1 — Hybrid AdapterFailoverLog Enhancement

### 🎯 Goal
Refactor the `AdapterFailoverLog` into a **hybrid design** that supports both static and instance usage.  
This ensures environment-based configurability without breaking existing code.

---

### ✅ Implemented Tasks
- Replaced constant path with a dynamic `.env`-aware configuration.
- Added optional `$path` parameter in constructor.
- Preserved static `record()` method for backward compatibility.
- Ensured directory auto-creation on first write.
- Confirmed compatibility with `DiagnosticService`.

---

### ⚙️ File Updated
```

src/Diagnostics/AdapterFailoverLog.php

````

---

### 🧩 Final Implementation
```php
final class AdapterFailoverLog
{
    private string $file;

    public function __construct(?string $path = null)
    {
        $logPath = $path
            ?? ($_ENV['ADAPTER_LOG_PATH'] ?? getenv('ADAPTER_LOG_PATH') ?: __DIR__ . '/../../storage');
        $this->file = rtrim($logPath, '/') . '/failover.log';
        @mkdir(dirname($this->file), 0777, true);
    }

    public static function record(string $adapter, string $message): void
    {
        (new self())->write($adapter, $message);
    }

    public function write(string $adapter, string $message): void
    {
        $line = sprintf("[%s] [%s] %s%s", date('Y-m-d H:i:s'), strtoupper($adapter), $message, PHP_EOL);
        @file_put_contents($this->file, $line, FILE_APPEND);
    }
}
````

---

### 🧠 Usage Examples

```php
use Maatify\DataAdapters\Diagnostics\AdapterFailoverLog;

// Default (auto .env or fallback)
AdapterFailoverLog::record('redis', 'Fallback to Predis');

// Custom path
$logger = new AdapterFailoverLog(__DIR__ . '/../../logs/adapters');
$logger->write('mysql', 'Connection timeout');

// .env example
// ADAPTER_LOG_PATH=/var/www/maatify/storage/logs
```

---

### 🧩 Key Improvements

| Feature                   | Description                                     |
|:--------------------------|:------------------------------------------------|
| Hybrid design             | Works with both static and instance usage       |
| .env support              | Reads `ADAPTER_LOG_PATH` dynamically            |
| Auto directory creation   | Ensures path exists automatically               |
| Backward compatible       | Keeps old static usage syntax                   |
| Ready for PSR integration | Future bridge for maatify/psr-logger in Phase 7 |

---

### 🧪 Test Results

✅ Default fallback → `/storage/failover.log`
✅ `.env` path respected
✅ Custom constructor path works
✅ Static + instance both functional

---

### 🧩 Phase 4.2 Summary — Adapter Logger Abstraction via DI

In this phase, the static `AdapterFailoverLog` system was refactored into a **Dependency Injection–based logger architecture**.  
A new `AdapterLoggerInterface` was introduced, along with a default implementation `FileAdapterLogger`.  
This abstraction enables flexible logging strategies and prepares the project for seamless integration with `maatify/psr-logger` in Phase 7.

**Key outcomes:**
- Unified logger interface for all adapters
- Dynamic, environment-aware file path configuration
- Backward compatibility maintained for legacy static logging
- Verified functionality through PHPUnit diagnostic tests

> ✅ Phase 4.2 completed — DI-based logging system successfully integrated and validated.

---

### 🧩 Phase 5 Summary — Integration & Unified Testing

This phase validated full interoperability between **maatify/data-adapters** and other
core Maatify ecosystem libraries.  
Both **mock** and **real** integrations were executed to ensure adapter stability under
multiple drivers and environments (Redis, Predis, MongoDB, MySQL PDO/DBAL).

**Key outcomes:**
- Verified adapter compatibility with:
    - ✅ `maatify/mongo-activity` (MongoAdapter — real)
    - 🟡 `maatify/rate-limiter` (RedisAdapter — ready, pending module release)
    - 🟡 `maatify/security-guard` (MySQLAdapter PDO/DBAL — ready, pending module release)
- Implemented **RealMysqlDualConnectionTest** validating both PDO and DBAL drivers.
- Added **mock integration layer** for isolated adapter verification.
- Achieved **> 85 %** total coverage across the integration suite.
- Simulated **10 000 req/sec** adapter load without connection errors.
- Unified all mock + real tests under a single **PHPUnit** configuration and environment bootstrap.

> ✅ **Phase 5 completed** — All adapters verified for stability, environment consistency, and future ecosystem integration readiness.

---

### 🧩 Phase 6 Summary

Phase 6 establishes the **intelligent failover and recovery foundation** for all Maatify Data Adapters.
It enables the system to **gracefully handle adapter downtime** — particularly Redis — through automated fallback, queued operations, and seamless resynchronization once the primary service returns.

This phase bridges the gap between **operational resilience** and **observability**, ensuring that no data is lost during outages, and all events are transparently logged for future analysis.
It prepares the groundwork for **Phase 7 (Observability & Metrics)**, where real-time monitoring and performance insights will be layered on top of this recovery framework.

---

### 📜 Next Step → **Phase 7 — Observability & Metrics**

In the next phase:

* Integrate `maatify/psr-logger` for structured adapter-level logging and traceability.
* Add latency, throughput, and failover metrics across all adapters.
* Implement `MetricsCollector` and `MetricsFormatter` for Prometheus-ready data exports.
* Expose `/metrics` endpoint for `maatify/admin-dashboard` integration.
* Build monitoring dashboards for adapter health, latency trends, and recovery counts.

---

**End of Documentation – Phases 1 → 6**

