# 🧱 Phase 11 — Dynamic Database Registry (JSON Config)

**Version:** 1.1.0  
**Base Version:** 1.0.0  
**Maintainer:** Mohamed Abdulalim (megyptm)  
**Project:** maatify/data-adapters  
**Date:** 2025-11-12

---

## 🎯 Goal

Introduce a **dynamic JSON-based database registry** that allows multiple database
profiles (MySQL, MongoDB, Redis, etc.) to be defined in a single declarative file.  
This phase generalizes the multi-profile environment model introduced in Phase 10
and prepares the system for multi-tenant and containerized deployments.

---

## 🧩 Key Objectives

| Objective                   | Description                                                                           |
|:----------------------------|:--------------------------------------------------------------------------------------|
| **Centralized Config File** | Support `config/databases.json` for managing all adapter connections.                 |
| **Unified Schema**          | Define consistent JSON structure covering host, port, user, password, db, and driver. |
| **Priority Hierarchy**      | Apply config precedence: Runtime JSON > `.env` > Default values.                      |
| **Dynamic Loading**         | Allow new profiles to be added or reloaded at runtime.                                |
| **Future-Ready Design**     | Enable expansion to Redis and Mongo profiles for multi-service setups.                |

---

## ⚙️ Implementation Plan

### 1️⃣ Database Registry File

`config/databases.json` example:

```json
{
  "mysql": {
    "main": {
      "host": "127.0.0.1",
      "port": 3306,
      "user": "root",
      "pass": "",
      "db": "maatify_main",
      "driver": "pdo"
    },
    "logs": {
      "host": "127.0.0.1",
      "port": 3306,
      "user": "root",
      "pass": "",
      "db": "maatify_logs",
      "driver": "pdo"
    }
  },
  "mongo": {
    "activity": {
      "uri": "mongodb://127.0.0.1:27017/maatify_activity"
    }
  }
}
````

> 🧠 Each top-level key matches an adapter type (mysql, mongo, redis…).
> Nested objects define profiles under that adapter.

---

### 2️⃣ Update `EnvironmentConfig`

Add loader for registry file:

```php
public function loadDatabaseRegistry(string $path = __DIR__ . '/../../config/databases.json'): void
{
    if (is_file($path)) {
        $content = file_get_contents($path);
        $this->registry = json_decode($content, true) ?? [];
    }
}
```

Add new helper to retrieve settings dynamically:

```php
public function getDatabaseConfig(string $adapter, string $profile = 'main'): ?array
{
    if (isset($this->registry[$adapter][$profile])) {
        return $this->registry[$adapter][$profile];
    }
    // fallback to environment-based method (Phase 10)
    if ($adapter === 'mysql') {
        return $this->getMySQLConfig($profile);
    }
    return null;
}
```

---

### 3️⃣ DatabaseResolver Integration

Extend `resolve()` to use registry first:

```php
public function resolve(string $type): AdapterInterface
{
    [$adapter, $profile] = explode('.', $type) + [null, 'main'];
    $config = $this->envConfig->getDatabaseConfig($adapter, $profile);

    return match ($adapter) {
        'mysql' => new MySQLAdapter($config),
        'mongo' => new MongoAdapter($config),
        'redis' => new RedisAdapter($config),
        default  => throw new InvalidArgumentException("Unsupported adapter: $adapter"),
    };
}
```

---

### 4️⃣ Priority Resolution Rules

| Priority | Source                           | Description                              |
|:---------|:---------------------------------|:-----------------------------------------|
| 1️⃣      | `config/databases.json`          | Highest – registry-defined connections   |
| 2️⃣      | `.env` prefixed (`MYSQL_MAIN_*`) | Fallback for environment-scoped configs  |
| 3️⃣      | Defaults                         | Safe internal defaults for local testing |

---

### 5️⃣ Dynamic Reloading (optional)

Add runtime reload method:

```php
$config->loadDatabaseRegistry('/path/to/databases.json');
```

Allows refreshing configuration without restarting the app.

---

## 🧠 Design Highlights

| Feature                         | Description                                                        |
|:--------------------------------|:-------------------------------------------------------------------|
| **Declarative Config**          | Enables portable configuration per environment (dev, stage, prod). |
| **Multi-Adapter Support**       | Structure can extend beyond MySQL to Mongo, Redis, etc.            |
| **Reload-Safe**                 | Non-breaking design—runtime refresh supported.                     |
| **Foundation for Multi-Tenant** | Simplifies mapping tenant → database dynamically.                  |

---

## 🧪 Testing & Validation

| Test                           | Description                                    | Expected Result |
|:-------------------------------|:-----------------------------------------------|:----------------|
| `DatabaseRegistryLoadTest`     | Verifies JSON parsing and key lookup           | ✅               |
| `DatabaseResolverPriorityTest` | Confirms JSON > .env > default hierarchy       | ✅               |
| `DynamicReloadTest`            | Validates runtime reloading of config          | ✅               |
| `MultiAdapterResolutionTest`   | Ensures MySQL/Mongo adapters resolve correctly | ✅               |

**Coverage Target:** ≥ 90 %
**Integration Verified:** Works with maatify/bootstrap DI container.

---

## 📘 Example Usage

```php
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Core\DatabaseResolver;

$config = new EnvironmentConfig(__DIR__);
$config->loadDatabaseRegistry(__DIR__ . '/config/databases.json');

$resolver = new DatabaseResolver($config);

$mainDb  = $resolver->resolve('mysql.main');
$logsDb  = $resolver->resolve('mysql.logs');
$activity = $resolver->resolve('mongo.activity');
```

---

## 🧱 Architecture Overview

```
src/
 ├─ Core/
 │   ├─ EnvironmentConfig.php
 │   ├─ DatabaseResolver.php
 │   └─ Exceptions/
 │       └─ InvalidArgumentException.php
config/
 └─ databases.json
tests/
 ├─ DatabaseRegistryLoadTest.php
 ├─ DatabaseResolverPriorityTest.php
 ├─ DynamicReloadTest.php
 └─ MultiAdapterResolutionTest.php
docs/phases/
 └─ README.phase11.md
```

---

## 🧩 Result Summary

| Outcome                                    | Description                           |
|:-------------------------------------------|:--------------------------------------|
| ✅ Registry-based configuration implemented | Supports multiple adapters & profiles |
| ✅ Backward compatible                      | `.env` still works as fallback        |
| ✅ Dynamic reload supported                 | Runtime refresh works                 |
| 🚀 Ready for Phase 12                      | Documentation + Release v1.1.0        |

---

## 🔗 Next Phase

➡ **Phase 12 — Documentation & Release 1.1.0**
Finalize examples, update README, bump version, and publish the new release to Packagist.

---

**© 2025 Maatify.dev**  
Engineered by **Mohamed Abdulalim (megyptm)** — https://www.maatify.dev

📘 Full documentation & source code:  
https://github.com/Maatify/data-adapters

---
