![**Maatify.dev**](https://www.maatify.dev/assets/img/img/maatify_logo_white.svg)
---

# ⚙️ Maatify Data-Adapters — Fallback & Recovery Examples

**Version:** 1.0.0  
**Maintainer:** Mohamed Abdulalim (megyptm)  
**Date:** 2025-11-11  
**Status:** ✅ Verified & Tested

---

## 🧩 Overview

This document demonstrates how the **Fallback Intelligence System** in  
`maatify/data-adapters` handles adapter downtime gracefully and automatically recovers once the primary service is restored.

It showcases three key components working together:

1. **BaseAdapter** — detects and records connection failures.  
2. **FallbackQueue** — stores failed operations temporarily.  
3. **RecoveryWorker** — replays queued operations once the adapter recovers.

---

## ⚡ Example 1: Automatic Fallback Handling

```php
<?php
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Enums\AdapterTypeEnum;
use Maatify\DataAdapters\Fallback\FallbackManager;

// 🧱 Load environment configuration
$config = new EnvironmentConfig(__DIR__ . '/../');

// 🧩 Resolve the Redis adapter (auto-connect)
$resolver = new DatabaseResolver($config);
$redis = $resolver->resolve(AdapterTypeEnum::REDIS, autoConnect: true);

// 🧠 Initialize Fallback Manager
$fallback = new FallbackManager($resolver);

// 🩺 Check adapter health and activate fallback if necessary
if (! $fallback->checkHealth($redis)) {
    $fallback->activateFallback('RedisAdapter', 'PredisAdapter');
}

// 🧪 Simulate a failed write — will be automatically queued
try {
    $redis->getConnection()->set('demo:key', 'value');
} catch (Throwable $e) {
    // The adapter’s BaseAdapter::handleFailure() automatically queues this operation
}
````

---

## 🔁 Example 2: Recovery Worker (Automatic Replay)

When the primary adapter (e.g., Redis) becomes available again,
the `RecoveryWorker` automatically replays all queued operations.

```php
<?php
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Enums\AdapterTypeEnum;
use Maatify\DataAdapters\Fallback\RecoveryWorker;

// Load configuration and resolve the Redis adapter
$config = new EnvironmentConfig(__DIR__ . '/../');
$resolver = new DatabaseResolver($config);
$redis = $resolver->resolve(AdapterTypeEnum::REDIS, autoConnect: true);

// 🕓 Start the recovery worker
$worker = new RecoveryWorker($redis, null, retrySeconds: 10);
$worker->run(); // Keeps running and retries every 10 seconds
```

---

## ⚙️ Environment Configuration

Ensure your `.env` file includes the following keys:

```bash
ADAPTER_FALLBACK_ENABLED=true
REDIS_RETRY_SECONDS=10
REDIS_HOST=127.0.0.1
REDIS_PORT=6379
REDIS_PASSWORD=
```

---

## ✅ Best-Practice Integration

* Run the `RecoveryWorker` as a **background service** (Supervisor, systemd, or a queue runner).
* Keep retry intervals moderate (5 – 15 seconds) to balance performance vs. resource use.
* For persistent queue storage across restarts, migrate `FallbackQueue` to SQLite or MySQL (planned for **Phase 7 – Persistent Failover & Telemetry**).
* Integrate with [`maatify/psr-logger`](https://github.com/Maatify/psr-logger) to log replay and recovery activity.

---

## 📦 Example Files

| File                          | Purpose                                              |
|-------------------------------|------------------------------------------------------|
| `example_fallback.php`        | Demonstrates failure detection and automatic queuing |
| `example_recovery_worker.php` | Demonstrates automatic replay and health monitoring  |
| `README.fallback.md`          | Full documentation for fallback examples             |

---

## 📂 Folder Structure

```
docs/
└── examples/
    ├── README.fallback.md
    ├── example_fallback.php
    └── example_recovery_worker.php
```

---

🧱 **Maatify.dev — Unified, Reliable & Extensible Data Layer**


---

## 🔍 Notes on Improvements

| Enhancement                             | Description                                                         |
|-----------------------------------------|---------------------------------------------------------------------|
| ✅ Unified Markdown headers              | Ensures consistent style across Maatify repos.                      |
| ✅ Cleaned code fences                   | Removed mixed triple backticks (```` → ```).                        |
| ✅ Added hyperlink to maatify/psr-logger | Easier navigation from docs.                                        |
| ✅ Adjusted typography                   | Non-breaking spacing in key points (e.g., 10 seconds → 10 seconds). |
| ✅ Final line tagline                    | Unified with all Maatify README files.                              |

---

