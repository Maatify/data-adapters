<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm)
 * @since       2025-11-08 20:17
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Core;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use Maatify\DataAdapters\Diagnostics\AdapterFailoverLog;
use Maatify\DataAdapters\Fallback\FallbackManager;
use Maatify\DataAdapters\Fallback\FallbackQueue;
use Throwable;

/**
 * 🧩 **Abstract Class BaseAdapter**
 *
 * 🎯 **Purpose:**
 * Serves as the **foundation class** for all data adapters in the Maatify ecosystem.
 * Implements shared logic for environment configuration, connection state management,
 * and fallback recovery, enabling concrete adapters (Redis, MySQL, MongoDB, etc.)
 * to focus on their specific connection logic.
 *
 * 🧠 **Key Responsibilities:**
 * - Centralized environment configuration through {@see EnvironmentConfig}.
 * - Unified connection lifecycle handling (`connect`, `disconnect`, `isConnected`).
 * - Standardized access to low-level connection objects.
 * - Optional fallback intelligence for Redis, MySQL, and Mongo adapters.
 *
 * ✅ **Example:**
 * ```php
 * final class RedisAdapter extends BaseAdapter
 * {
 *     public function connect(): void
 *     {
 *         $redis = new Redis();
 *         $redis->connect($this->requireEnv('REDIS_HOST'), (int)$this->requireEnv('REDIS_PORT'));
 *         $this->connection = $redis;
 *         $this->connected  = true;
 *     }
 * }
 * ```
 */
abstract class BaseAdapter implements AdapterInterface
{
    /**
     * 🔹 Indicates whether the adapter is currently connected.
     *
     * @var bool
     */
    protected bool $connected = false;

    /**
     * 🔹 Holds the underlying native connection instance (e.g., Redis, PDO, MongoDB client).
     *
     * @var object|null
     */
    protected ?object $connection = null;

    /**
     * 🔹 Whether fallback mode is globally enabled.
     *
     * @var bool
     */
    protected bool $fallbackEnabled = false;

    /**
     * 🔹 Active FallbackManager responsible for orchestrating fallback operations.
     *
     * @var FallbackManager|null
     */
    protected ?FallbackManager $fallbackManager = null;

    /**
     * 🧠 **Constructor**
     *
     * Initializes the adapter with shared environment configuration and optional
     * fallback manager. Automatically determines whether fallback mode is enabled
     * via the `ADAPTER_FALLBACK_ENABLED` environment variable.
     *
     * @param EnvironmentConfig       $config           Environment configuration provider.
     * @param FallbackManager|null    $fallbackManager  Optional manager for fallback operations.
     */
    public function __construct(
        protected readonly EnvironmentConfig $config,
        ?FallbackManager $fallbackManager = null
    ) {
        $this->fallbackManager = $fallbackManager;
        $this->fallbackEnabled = filter_var($_ENV['ADAPTER_FALLBACK_ENABLED'] ?? false, FILTER_VALIDATE_BOOL);
    }

    // =====================================================================
    // 🔹 Core Adapter Lifecycle
    // =====================================================================

    /**
     * ⚙️ **Establish Connection**
     *
     * Must be implemented by all concrete adapters to connect
     * to their respective data source.
     *
     * @throws ConnectionException When the connection cannot be established.
     *
     * @return void
     */
    abstract public function connect(): void;

    /**
     * 🔍 **Check Connection Status**
     *
     * Returns whether the adapter is currently connected to its backend.
     *
     * @return bool True if connected, false otherwise.
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * 🧠 **Retrieve the Underlying Connection Object**
     *
     * Returns the native connection instance used by the adapter
     * (e.g., Redis, PDO, MongoDB client).
     *
     * @return object|null The connection instance, or null if not connected.
     */
    public function getConnection(): ?object
    {
        return $this->connection;
    }

    /**
     * ❌ **Disconnect from the Data Source**
     *
     * Gracefully terminates the connection and resets connection state.
     *
     * @return void
     */
    public function disconnect(): void
    {
        $this->connection = null;
        $this->connected  = false;
    }

    /**
     * 🧩 **Require Environment Variable**
     *
     * Retrieves a required environment variable from the configuration.
     * Throws a {@see ConnectionException} if the variable is missing.
     *
     * @param string $key The environment variable key.
     *
     * @throws ConnectionException If the variable is not defined.
     *
     * @return string The environment variable value.
     */
    protected function requireEnv(string $key): string
    {
        $value = $this->config->get($key);
        if ($value === null) {
            throw new ConnectionException("Missing required environment variable: {$key}");
        }
        return $value;
    }

    // =====================================================================
    // 🧠 Optional Fallback Intelligence (Phase 6)
    // =====================================================================

    /**
     * ✅ **Check if Fallback Mode is Enabled**
     *
     * Determines whether adapter fallback recovery is active.
     *
     * @return bool True if fallback mode is enabled.
     */
    protected function isFallbackEnabled(): bool
    {
        return $this->fallbackEnabled === true;
    }

    /**
     * 🧱 **Assign a FallbackManager Instance**
     *
     * Allows dynamic injection or replacement of the active fallback manager.
     *
     * @param FallbackManager $manager The fallback manager to assign.
     *
     * @return void
     */
    public function setFallbackManager(FallbackManager $manager): void
    {
        $this->fallbackManager = $manager;
    }

    /**
     * 🚨 **Unified Fallback Handler**
     *
     * Provides a consistent mechanism for handling adapter failures and initiating
     * fallback recovery when enabled. This method can be called from any concrete
     * adapter to automatically log the error, queue the failed operation, and trigger
     * a fallback adapter (e.g., `PredisAdapter` for Redis).
     *
     * @param Throwable     $e         The exception that caused the failure.
     * @param string        $operation The operation name (e.g., "connect", "query").
     * @param callable|null $callback  Optional retry callback for deferred execution.
     *
     * @throws Throwable If fallback mode is disabled.
     *
     * @return void
     */
    protected function handleFailure(Throwable $e, string $operation, ?callable $callback = null): void
    {
        if (! $this->isFallbackEnabled()) {
            throw $e;
        }

        $adapter = static::class;
        $message = "{$operation} failed: {$e->getMessage()}";

        // 🪵 Log fallback event for observability
        AdapterFailoverLog::record($adapter, $message);

        // 🕓 Queue failed operation for future replay
        if ($callback !== null) {
            FallbackQueue::enqueue($adapter, $operation, ['callback' => $callback]);
        }

        // 🔁 Activate fallback adapter (e.g., switch to Predis)
        $this->fallbackManager?->activateFallback($adapter, 'PredisAdapter');

        // ⚠️ Mark connection as failed
        $this->connected = false;
    }
}
