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

/**
 * ⚙️ **Abstract Class BaseAdapter**
 *
 * 🎯 **Purpose:**
 * Provides a shared foundation for all adapter implementations (e.g., Redis, MySQL, MongoDB)
 * with lifecycle management, connection validation, and environment-based configuration loading.
 *
 * 🧩 **Key Features:**
 * - Unified connection lifecycle (`connect`, `disconnect`, `isConnected`).
 * - Safe access to configuration variables.
 * - Common error handling through {@see ConnectionException}.
 * - Centralized structure for consistent adapter behavior.
 *
 * ✅ **Example Usage:**
 * ```php
 * class MyCustomAdapter extends BaseAdapter
 * {
 *     public function connect(): void
 *     {
 *         // Implement connection logic here...
 *     }
 * }
 * ```
 */
abstract class BaseAdapter implements AdapterInterface
{
    /**
     * 🔹 **Connection State Flag**
     *
     * Indicates whether the adapter is currently connected.
     *
     * @var bool
     */
    protected bool $connected = false;

    /**
     * 🔹 **Underlying Connection Instance**
     *
     * Holds the native client or connection object (e.g., Redis, PDO, MongoDB).
     *
     * @var object|null
     */
    protected ?object $connection = null;

    /**
     * 🧩 **Constructor**
     *
     * Accepts a shared {@see EnvironmentConfig} instance to load adapter configuration.
     *
     * @param EnvironmentConfig $config Shared configuration manager for environment variables.
     */
    public function __construct(
        protected readonly EnvironmentConfig $config
    ) {}

    // =====================================================================
    // 🔹 Core Adapter Lifecycle
    // =====================================================================

    /**
     * ⚙️ **Establish Connection**
     *
     * Must be implemented by all concrete adapter subclasses.
     * Responsible for initializing and authenticating the connection.
     *
     * @throws ConnectionException If the connection fails to establish.
     *
     * @return void
     */
    abstract public function connect(): void;

    /**
     * 🔍 **Check Connection Status**
     *
     * Returns whether the adapter is currently connected to its data source.
     *
     * @return bool `true` if connected, otherwise `false`.
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * 🧠 **Retrieve the Underlying Connection Object**
     *
     * Provides access to the raw connection object (e.g., Redis, PDO).
     * Useful for low-level operations or debugging.
     *
     * @return object|null The connection object, or `null` if disconnected.
     */
    public function getConnection(): ?object
    {
        return $this->connection;
    }

    /**
     * ❌ **Disconnect from Data Source**
     *
     * Safely terminates the connection by resetting the connection reference
     * and connection status flag.
     *
     * @return void
     */
    public function disconnect(): void
    {
        $this->connection = null;
        $this->connected = false;
    }

    /**
     * 🧩 **Require Environment Configuration Key**
     *
     * Retrieves a configuration value by key and throws a {@see ConnectionException}
     * if the key is missing or undefined.
     *
     * @param string $key Configuration key to retrieve.
     *
     * @throws ConnectionException If the key is not found in configuration.
     *
     * @return string The retrieved configuration value.
     *
     * ✅ **Example:**
     * ```php
     * $host = $this->requireEnv('REDIS_HOST');
     * ```
     */
    protected function requireEnv(string $key): string
    {
        $value = $this->config->get($key);

        if ($value === null) {
            throw new ConnectionException("Missing required environment variable: {$key}");
        }

        return $value;
    }
}
