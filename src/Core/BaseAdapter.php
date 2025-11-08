<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-11-08
 * Time: 20:27
 * Project: maatify:data-adapters
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Core;

use Maatify\DataAdapters\Contracts\AdapterInterface;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;

/**
 * 🧩 Abstract Class BaseAdapter
 *
 * 🎯 Purpose:
 * Serves as the **foundation class** for all data adapters within the Maatify ecosystem.
 * Implements shared logic, environment handling, and connection state management
 * for concrete adapter implementations (e.g., Redis, MySQL, MongoDB).
 *
 * ✅ Features:
 * - Centralized environment configuration via {@see EnvironmentConfig}.
 * - Common connection handling logic (connect/disconnect/isConnected).
 * - Standardized access to connection objects.
 * - Strict enforcement of required environment variables.
 *
 * ⚙️ Usage Example:
 * ```php
 * use Maatify\DataAdapters\Core\BaseAdapter;
 *
 * final class RedisAdapter extends BaseAdapter {
 *     public function connect(): void {
 *         // Implement Redis-specific connection logic here
 *     }
 * }
 * ```
 *
 * @package Maatify\DataAdapters\Core
 */
abstract class BaseAdapter implements AdapterInterface
{
    /** @var bool 🔹 Indicates whether the adapter is currently connected. */
    protected bool $connected = false;

    /** @var object|null 🔹 Holds the underlying connection instance (e.g., Redis, PDO, MongoDB). */
    protected ?object $connection = null;

    /**
     * 🧠 Constructor
     *
     * Initializes the adapter with a configuration handler.
     *
     * @param EnvironmentConfig $config Environment configuration provider instance.
     */
    public function __construct(
        protected readonly EnvironmentConfig $config
    ) {}

    /**
     * ⚙️ Establish connection to the target data source.
     *
     * Each extending adapter must implement its own connection logic.
     *
     * @return void
     *
     * @throws ConnectionException If connection fails.
     */
    abstract public function connect(): void;

    /**
     * 🔍 Check if the adapter is currently connected.
     *
     * @return bool True if the connection is active, false otherwise.
     *
     * ✅ Example:
     * ```php
     * if (! $adapter->isConnected()) {
     *     $adapter->connect();
     * }
     * ```
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * 🧠 Retrieve the underlying connection object.
     *
     * Allows low-level access to the native driver instance.
     *
     * @return object|null Connection object if connected, null otherwise.
     *
     * ✅ Example:
     * ```php
     * $client = $adapter->getConnection();
     * $client->ping();
     * ```
     */
    public function getConnection(): ?object
    {
        return $this->connection;
    }

    /**
     * ❌ Gracefully terminate the connection and clear state.
     *
     * Ensures proper resource cleanup and connection state reset.
     *
     * @return void
     *
     * ✅ Example:
     * ```php
     * $adapter->disconnect();
     * ```
     */
    public function disconnect(): void
    {
        $this->connection = null;
        $this->connected  = false;
    }

    /**
     * 🧩 Retrieve a required environment variable from configuration.
     *
     * Ensures the presence of a mandatory configuration key and throws
     * an exception if it is missing.
     *
     * @param string $key The name of the required configuration variable.
     *
     * @return string The corresponding environment value.
     *
     * @throws ConnectionException If the required key is missing.
     *
     * ✅ Example:
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
