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
use Maatify\Common\DTO\ConnectionConfigDTO;
use Maatify\Common\Enums\ConnectionTypeEnum;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;

/**
 * 🧩 **Abstract Class BaseAdapter**
 *
 * 🎯 Serves as the foundational adapter for all Maatify database
 * and backend connection drivers.
 * This class provides:
 *
 * - DSN-first configuration resolution
 * - Legacy ENV fallback (host/port/user/pass/db)
 * - Unified profile-based environment variable naming
 * - Connection state handling (`connected`, `disconnect()`, `getConnection()`)
 * - Utility helpers used across all adapters (MySQL, Mongo, Redis, Predis)
 *
 * 🧠 **Environment Naming Convention**
 *
 * The class supports dynamic profile keys such as:
 * ```
 * MYSQL_MAIN_DSN
 * MYSQL_MAIN_HOST
 * MYSQL_MAIN_PORT
 * MYSQL_MAIN_USER
 * MYSQL_MAIN_PASS
 * MYSQL_MAIN_DB
 * ```
 *
 * ✔ `resolveConfig()` implements the DSN-first merging strategy
 * ✔ Concrete adapters only override behavior when necessary
 *
 * @example Resolving profile "main" for Redis:
 * ```php
 * $adapter = new RedisAdapter($env, 'main');
 * $config  = $adapter->debugConfig();
 * ```
 *
 * @example DSN precedence:
 * ```env
 * MYSQL_MAIN_DSN="mysql://127.0.0.1:3306/mydb"
 * ```
 */
abstract class BaseAdapter implements AdapterInterface
{
    /**
     * Indicates whether the adapter is currently connected.
     *
     * @var bool
     */
    protected bool $connected = false;

    /**
     * The underlying connection object (PDO, Redis, Mongo, Predis, etc.).
     *
     * @var mixed
     */
    protected mixed $connection = null;

    /**
     * Optional connection profile name (e.g., "main", "logs").
     *
     * @var string|null
     */
    protected ?string $profile;

    /**
     * @param EnvironmentConfig $config  The environment configuration handler
     * @param string|null       $profile Optional connection profile
     */
    public function __construct(
        protected readonly EnvironmentConfig $config,
        ?string $profile = null
    ) {
        $this->profile = $profile;
    }

    /**
     * 🎯 **Resolve connection configuration (DSN-first strategy)**
     *
     * Generates dynamic environment variable keys based on:
     * - Connection type (`mysql`, `mongo`, `redis`)
     * - Optional profile (`main`, `logs`, etc.)
     *
     * ✔ If DSN exists → DSN takes priority
     * ✔ Otherwise → fallback to legacy individual fields
     *
     * @param ConnectionTypeEnum $type The connection type
     *
     * @return ConnectionConfigDTO Resolved configuration for the adapter
     */
    protected function resolveConfig(ConnectionTypeEnum $type): ConnectionConfigDTO
    {
        // -------------------------------
        // 1) Build dynamic profile prefix
        // -------------------------------
        //
        // Example:
        //   type    = "mysql"
        //   profile = "main"
        //
        // Keys:
        //   MYSQL_MAIN_DSN
        //   MYSQL_MAIN_USER
        //   MYSQL_MAIN_PASS
        //   MYSQL_MAIN_DB
        //
        $prefix = strtoupper($type->value);
        if ($this->profile) {
            $prefix .= '_' . strtoupper($this->profile);
        }

        // Build keys for all connection fields
        $dsnKey  = $prefix . '_DSN';
        $userKey = $prefix . '_USER';
        $passKey = $prefix . '_PASS';
        $dbKey   = $prefix . '_DB';
        $hostKey = $prefix . '_HOST';
        $portKey = $prefix . '_PORT';

        // -------------------------------------------------
        // 2) DSN mode (highest priority)
        // -------------------------------------------------
        $dsnVal = $this->config->get($dsnKey);

        if ($dsnVal) {
            return new ConnectionConfigDTO(
                dsn:      $dsnVal,
                user:     $this->config->get($userKey),
                pass:     $this->config->get($passKey),
                database: $this->config->get($dbKey),
                profile:  $this->profile
            );
        }

        // --------------------------------------------------------
        // 3) Legacy mode (host/port/user/pass/db style fallback)
        // --------------------------------------------------------
        return new ConnectionConfigDTO(
            dsn:      null,
            host:     $this->config->get($hostKey),
            port:     $this->config->get($portKey),
            user:     $this->config->get($userKey),
            pass:     $this->config->get($passKey),
            database: $this->config->get($dbKey),
            profile:  $this->profile
        );
    }

    /**
     * 🧩 **Require a specific environment key**
     *
     * Useful for strict adapters that **must** have certain keys present.
     *
     * @param string $key Environment key to validate
     *
     * @return string The environment value
     *
     * @throws ConnectionException When the key is missing
     */
    protected function requireEnv(string $key): string
    {
        $value = $this->config->get($key);

        if ($value === null) {
            throw new ConnectionException("Missing required environment variable: {$key}");
        }

        return $value;
    }

    // -------------------------------------------------
    // Abstract methods implemented by concrete adapters
    // -------------------------------------------------

    /**
     * Establish an adapter-specific connection.
     *
     * @return void
     */
    abstract public function connect(): void;

    /**
     * Attempt to re-establish the connection.
     *
     * @return bool `true` if successfully reconnected
     */
    abstract public function reconnect(): bool;

    /**
     * Perform a health check on the current connection.
     *
     * @return bool `true` if connection is healthy
     */
    abstract public function healthCheck(): bool;

    // -------------------------------------------------
    // Generic utilities applicable to all adapters
    // -------------------------------------------------

    /**
     * Indicates whether the adapter is currently connected.
     *
     * @return bool
     */
    public function isConnected(): bool
    {
        return $this->connected;
    }

    /**
     * Get the underlying connection object (PDO, Redis, Mongo client, etc.).
     *
     * @return object|null
     */
    public function getConnection(): ?object
    {
        return $this->connection;
    }

    /**
     * Disconnect the current connection by resetting connection attributes.
     *
     * @return void
     */
    public function disconnect(): void
    {
        $this->connection = null;
        $this->connected = false;
    }

    /**
     * 🧪 **Return resolved configuration — used only for PHPUnit tests**
     *
     * @internal
     *
     * @return ConnectionConfigDTO
     */
    public function debugConfig(): ConnectionConfigDTO
    {
        return $this->resolveConfig(ConnectionTypeEnum::from($this->getType()));
    }

    /**
     * 🧩 **Determine adapter type based on instance**
     *
     * Used internally to map adapter classes to connection types.
     *
     * @internal
     *
     * @return string One of: "mysql", "mongo", "redis"
     */
    protected function getType(): string
    {
        return match (true) {
            $this instanceof \Maatify\DataAdapters\Adapters\MySQLAdapter,
                $this instanceof \Maatify\DataAdapters\Adapters\MySQLDbalAdapter => 'mysql',

            $this instanceof \Maatify\DataAdapters\Adapters\MongoAdapter     => 'mongo',

            $this instanceof \Maatify\DataAdapters\Adapters\RedisAdapter,
                $this instanceof \Maatify\DataAdapters\Adapters\PredisAdapter    => 'redis',
        };
    }
}
