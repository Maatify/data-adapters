<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-11 18:20
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Fallback;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Diagnostics\AdapterFailoverLog;
use Maatify\DataAdapters\Enums\AdapterTypeEnum;
use Psr\Log\LoggerInterface;
use Throwable;

/**
 * ⚙️ **Class FallbackManager**
 *
 * 🎯 **Purpose:**
 * Coordinates fallback activation, health monitoring, and automatic recovery
 * for adapter instances (Redis, Mongo, MySQL, etc.) within the Maatify ecosystem.
 *
 * 🧠 **Core Responsibilities:**
 * - Tracks and activates fallback adapters (e.g., Redis → Predis).
 * - Performs health checks and restoration of primary adapters.
 * - Integrates with {@see DatabaseResolver} for runtime resolution.
 * - Logs all fallback activities for monitoring and auditing.
 *
 * ✅ **Typical Workflow:**
 * 1. Detect adapter failure and trigger `activateFallback()`.
 * 2. Route future requests to the fallback adapter.
 * 3. Monitor health with `checkHealth()` and restore primary when recovered.
 *
 * ✅ **Example Usage:**
 * ```php
 * $fallback = new FallbackManager($resolver, $logger);
 * $fallback->activateFallback('RedisAdapter', 'PredisAdapter');
 * $active = $fallback->resolveActive(AdapterTypeEnum::Redis);
 * ```
 */
final class FallbackManager
{
    /**
     * 🧾 Logger instance for recording fallback events.
     *
     * @var LoggerInterface|null
     */
    private ?LoggerInterface $logger;

    /**
     * 🧩 Shared DatabaseResolver for adapter instantiation.
     *
     * @var DatabaseResolver
     */
    private DatabaseResolver $resolver;

    /**
     * 🧠 Active fallback adapters map (primary → fallback).
     *
     * @var array<string, string>
     */
    private array $activeFallbacks = [];

    /**
     * 🧩 **Constructor**
     *
     * Initializes the fallback manager with a resolver and optional logger.
     *
     * @param DatabaseResolver     $resolver Central adapter resolver.
     * @param LoggerInterface|null $logger   Optional PSR-3 compliant logger.
     */
    public function __construct(DatabaseResolver $resolver, ?LoggerInterface $logger = null)
    {
        $this->resolver = $resolver;
        $this->logger   = $logger;
    }

    /**
     * 🩺 **Check Adapter Health**
     *
     * Invokes `healthCheck()` on the provided adapter and logs any errors.
     *
     * @param AdapterInterface $adapter The adapter to check.
     *
     * @return bool True if the adapter is healthy, otherwise false.
     */
    public function checkHealth(AdapterInterface $adapter): bool
    {
        try {
            return $adapter->healthCheck();
        } catch (Throwable $e) {
            $this->log(get_class($adapter), 'Health check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 🔄 **Activate a Fallback Adapter**
     *
     * Marks a fallback adapter as active for the given primary.
     * Logs and records the transition via {@see AdapterFailoverLog}.
     *
     * @param string $primary  The primary adapter class name.
     * @param string $fallback The fallback adapter identifier.
     *
     * @return void
     */
    public function activateFallback(string $primary, string $fallback): void
    {
        AdapterFailoverLog::record($primary, "Fallback activated → {$fallback}");
        $this->log($primary, "⚠️ Fallback activated → {$fallback}");
        $this->activeFallbacks[$primary] = $fallback;
    }

    /**
     * ♻️ **Restore the Primary Adapter**
     *
     * Removes the fallback state for the given adapter and logs the restoration.
     *
     * @param string $adapterName The adapter class name to restore.
     *
     * @return void
     */
    public function restorePrimary(string $adapterName): void
    {
        AdapterFailoverLog::record($adapterName, "Primary adapter restored");
        $this->log($adapterName, "✅ Primary adapter restored");
        unset($this->activeFallbacks[$adapterName]);
    }

    /**
     * 🧩 **Resolve Active Adapter (Primary or Fallback)**
     *
     * Determines the appropriate adapter to use based on current fallback state.
     * If a fallback is active, resolves it instead of the primary adapter.
     *
     * @param AdapterTypeEnum $type        The adapter type to resolve.
     * @param bool            $autoConnect Whether to auto-connect after resolution.
     *
     * @return AdapterInterface Resolved adapter instance (either primary or fallback).
     */
    public function resolveActive(AdapterTypeEnum $type, bool $autoConnect = true): AdapterInterface
    {
        $name = $type->name;

        // 🔁 If a fallback is active → resolve fallback instead
        if (isset($this->activeFallbacks[$name])) {
            $fallback = $this->activeFallbacks[$name];
            $this->log($name, "🔁 Resolving fallback adapter {$fallback}");
            return $this->resolver->resolve(AdapterTypeEnum::from(strtoupper($fallback)), $autoConnect);
        }

        // ✅ Otherwise resolve the normal primary adapter
        return $this->resolver->resolve($type, $autoConnect);
    }

    /**
     * 🪵 **Log Internal Event**
     *
     * Writes a message to the PSR-3 logger, if available.
     *
     * @param string $adapter Adapter name or identifier.
     * @param string $msg     Log message.
     *
     * @return void
     */
    private function log(string $adapter, string $msg): void
    {
        $this->logger?->info("[{$adapter}] {$msg}");
    }
}
