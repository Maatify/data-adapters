<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim
 * @since       2025-11-08 20:32
 * @link        https://github.com/Maatify/data-adapters
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Core;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataAdapters\Adapters\MongoAdapter;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use Maatify\DataAdapters\Enums\AdapterTypeEnum;

/**
 * ⚙️ **Class DatabaseResolver**
 *
 * 🎯 **Purpose:**
 * A centralized factory responsible for creating and returning the appropriate
 * database or cache adapter instance (Redis, MongoDB, MySQL) based on the specified
 * {@see AdapterTypeEnum}. This ensures consistent and safe connection handling
 * across all Maatify data-related libraries.
 *
 * 🧠 **Core Features:**
 * - Dynamically instantiates adapters using enum-driven resolution.
 * - Auto-detects Redis implementation (native or Predis) depending on availability.
 * - Supports multiple MySQL drivers (`PDO`, `Doctrine DBAL`).
 * - Provides consistent, environment-based adapter configuration.
 * - Offers an optional `$autoConnect` mode for instant connection handling.
 *
 * ✅ **Example Usage:**
 * ```php
 * use Maatify\DataAdapters\Core\DatabaseResolver;
 * use Maatify\DataAdapters\Core\EnvironmentConfig;
 * use Maatify\DataAdapters\Enums\AdapterTypeEnum;
 *
 * $config = new EnvironmentConfig(__DIR__ . '/../');
 * $resolver = new DatabaseResolver($config);
 *
 * $redis = $resolver->resolve(AdapterTypeEnum::Redis, autoConnect: true);
 * $mongo = $resolver->resolve(AdapterTypeEnum::Mongo);
 * $mysql = $resolver->resolve(AdapterTypeEnum::MySQL);
 * ```
 *
 * 🧩 **Typical Use Case:**
 * Acts as the entry point for dependency injection containers or service bootstraps
 * needing dynamic adapter selection without hardcoding specific implementations.
 */
final readonly class DatabaseResolver
{
    /**
     * 🧱 **Constructor**
     *
     * Accepts an instance of {@see EnvironmentConfig} to provide adapter-related
     * environment values (like host, port, credentials, driver, etc.).
     *
     * @param EnvironmentConfig $config Shared environment configuration handler.
     */
    public function __construct(private EnvironmentConfig $config) {}

    /**
     * 🧩 **Resolve and instantiate the correct adapter.**
     *
     * Selects the adapter implementation based on the given {@see AdapterTypeEnum}.
     * If `$autoConnect` is true, automatically establishes the connection.
     *
     * @param AdapterTypeEnum $type         The desired adapter type (Redis, Mongo, MySQL).
     * @param bool            $autoConnect  Whether to automatically connect upon instantiation.
     *
     * @return AdapterInterface The fully configured adapter instance.
     *
     * @throws ConnectionException If an unsupported adapter type is specified.
     */
    public function resolve(AdapterTypeEnum $type, bool $autoConnect = false): AdapterInterface
    {
        $adapter = match ($type) {
            AdapterTypeEnum::REDIS => $this->makeRedis(),
            AdapterTypeEnum::MONGO => $this->makeMongo(),
            AdapterTypeEnum::MYSQL => $this->makeMySQL(),
            default => throw new ConnectionException("Unsupported adapter: {$type->value}")
        };

        // ⚙️ Optionally auto-connect the adapter upon creation
        if ($autoConnect) {
            $adapter->connect();
        }

        return $adapter;
    }

    /**
     * 🔹 **Create a Redis adapter instance.**
     *
     * Automatically detects whether the native PHP `Redis` extension is installed.
     * Falls back to `Predis` implementation when unavailable — ensuring portability
     * between local and containerized environments.
     *
     * @return AdapterInterface A Redis-compatible adapter instance.
     */
    private function makeRedis(): AdapterInterface
    {
        $class = class_exists('\\Redis')
            ? '\\Maatify\\DataAdapters\\Adapters\\RedisAdapter'
            : '\\Maatify\\DataAdapters\\Adapters\\PredisAdapter';

        return new $class($this->config);
    }

    /**
     * 🔹 **Create a MongoDB adapter instance.**
     *
     * Returns a new instance of {@see MongoAdapter} configured from environment variables.
     *
     * @return AdapterInterface The MongoDB adapter.
     */
    private function makeMongo(): AdapterInterface
    {
        return new MongoAdapter($this->config);
    }

    /**
     * 🔹 **Create a MySQL adapter instance.**
     *
     * Chooses between the `PDO` or `Doctrine DBAL` driver depending on the
     * `MYSQL_DRIVER` environment variable. Defaults to `PDO` for simplicity.
     *
     * Example:
     * ```
     * MYSQL_DRIVER=pdo
     * MYSQL_DRIVER=dbal
     * ```
     *
     * @return AdapterInterface The MySQL adapter (PDO or DBAL).
     */
    private function makeMySQL(): AdapterInterface
    {
        $driver = strtolower($this->config->get('MYSQL_DRIVER', 'pdo'));

        $class = $driver === 'dbal'
            ? '\\Maatify\\DataAdapters\\Adapters\\MySQLDbalAdapter'
            : '\\Maatify\\DataAdapters\\Adapters\\MySQLAdapter';

        return new $class($this->config);
    }
}
