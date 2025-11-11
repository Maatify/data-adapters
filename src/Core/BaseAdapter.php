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
 * 🧩 Abstract Class BaseAdapter
 *
 * 🎯 Purpose:
 * Serves as the **foundation class** for all data adapters within the Maatify ecosystem.
 * Implements shared logic, environment handling, connection state management, and
 * optional fallback recovery for concrete adapter implementations.
 *
 * ✅ Features:
 * - Centralized environment configuration via {@see EnvironmentConfig}.
 * - Common connection handling logic (connect/disconnect/isConnected).
 * - Standardized access to connection objects.
 * - Optional fallback resilience for Redis, MySQL, and Mongo adapters.
 */
abstract class BaseAdapter implements AdapterInterface
{
    /** @var bool 🔹 Indicates whether the adapter is currently connected. */
    protected bool $connected = false;

    /** @var object|null 🔹 Holds the underlying connection instance (e.g., Redis, PDO, MongoDB). */
    protected ?object $connection = null;

    /** @var bool 🔹 Determines if fallback mode is globally enabled. */
    protected bool $fallbackEnabled = false;

    /** @var FallbackManager|null 🔹 Optional fallback handler for intelligent recovery. */
    protected ?FallbackManager $fallbackManager = null;

    /**
     * 🧠 Constructor
     */
    public function __construct(
        protected readonly EnvironmentConfig $config,
        ?FallbackManager $fallbackManager = null
    ) {
        $this->fallbackManager = $fallbackManager;
        $this->fallbackEnabled = filter_var($_ENV['ADAPTER_FALLBACK_ENABLED'] ?? false, FILTER_VALIDATE_BOOL);
    }

    /**
     * ⚙️ Establish connection to the target data source.
     */
    abstract public function connect(): void;

    /**
     * 🔍 Check if the adapter is currently connected.
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * 🧠 Retrieve the underlying connection object.
     */
    public function getConnection(): ?object
    {
        return $this->connection;
    }

    /**
     * ❌ Gracefully terminate the connection and clear state.
     */
    public function disconnect(): void
    {
        $this->connection = null;
        $this->connected  = false;
    }

    /**
     * 🧩 Retrieve a required environment variable from configuration.
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
    // 🧠 Optional Fallback Logic (Phase 6)
    // =====================================================================

    /**
     * ✅ Whether fallback mode is globally enabled.
     */
    protected function isFallbackEnabled(): bool
    {
        return $this->fallbackEnabled === true;
    }

    /**
     * 🧱 Assign or replace the active FallbackManager instance.
     */
    public function setFallbackManager(FallbackManager $manager): void
    {
        $this->fallbackManager = $manager;
    }

    /**
     * 🚨 Unified fallback handler for connection or operation failures.
     *
     * Can be called from any adapter when an exception occurs.
     */
    protected function handleFailure(Throwable $e, string $operation, ?callable $callback = null): void
    {
        if (! $this->isFallbackEnabled()) {
            throw $e;
        }

        $adapter = static::class;
        $message = "{$operation} failed: {$e->getMessage()}";

        // Log fallback event
        AdapterFailoverLog::record($adapter, $message);

        // Queue failed operation for later replay
        if ($callback !== null) {
            FallbackQueue::enqueue($adapter, $operation, ['callback' => $callback]);
        }

        // Trigger fallback activation
        $this->fallbackManager?->activateFallback($adapter, 'PredisAdapter');

        $this->connected = false;
    }
}
