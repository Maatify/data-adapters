<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-11-08
 * Time: 20:32
 * Project: maatify:data-adapters
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Core;

use Maatify\DataAdapters\Contracts\AdapterInterface;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use Maatify\DataAdapters\Enums\AdapterTypeEnum;

/**
 * ⚙️ Class DatabaseResolver
 *
 * 🧩 Purpose:
 * Dynamically resolves and instantiates the correct database or cache adapter
 * (Redis, MongoDB, MySQL) based on environment configuration and enum-driven type safety.
 *
 * ✅ Features:
 * - Uses {@see AdapterTypeEnum} for strict adapter type resolution.
 * - Automatically selects Predis if the native Redis extension is unavailable.
 * - Supports both PDO and Doctrine DBAL for MySQL.
 * - Ensures centralized adapter instantiation for consistent configuration.
 * - Throws meaningful exceptions for unsupported or invalid adapter types.
 *
 * ⚙️ Example Usage:
 * ```php
 * use Maatify\DataAdapters\Core\DatabaseResolver;
 * use Maatify\DataAdapters\Core\EnvironmentConfig;
 * use Maatify\DataAdapters\Enums\AdapterTypeEnum;
 *
 * $config = new EnvironmentConfig(__DIR__ . '/../');
 * $resolver = new DatabaseResolver($config);
 *
 * $redis = $resolver->resolve(AdapterTypeEnum::Redis);
 * $mongo = $resolver->resolve(AdapterTypeEnum::Mongo);
 * $mysql = $resolver->resolve(AdapterTypeEnum::MySQL);
 * ```
 *
 * @package Maatify\DataAdapters\Core
 */
final readonly class DatabaseResolver
{
    /**
     * 🧠 Constructor
     *
     * Initializes the resolver with a shared configuration provider
     * that supplies environment-based connection settings.
     *
     * @param EnvironmentConfig $config The configuration loader for adapter environment variables.
     */
    public function __construct(private EnvironmentConfig $config) {}

    /**
     * 🎯 Resolve and return the appropriate adapter instance.
     *
     * Uses a match expression on {@see AdapterTypeEnum} to select
     * the correct driver and return an instantiated adapter.
     *
     * @param AdapterTypeEnum $type The adapter type to resolve (Redis, Mongo, MySQL).
     *
     * @return AdapterInterface The resolved adapter instance implementing {@see AdapterInterface}.
     *
     * @throws ConnectionException If the adapter type is unsupported or unavailable.
     */
    public function resolve(AdapterTypeEnum $type): AdapterInterface
    {
        return match ($type) {
            AdapterTypeEnum::Redis  => $this->makeRedis(),
            AdapterTypeEnum::Mongo  => $this->makeMongo(),
            AdapterTypeEnum::MySQL  => $this->makeMySQL(),
            default => throw new ConnectionException("Unsupported adapter: {$type->value}")
        };
    }

    /**
     * 🔹 Create and return a Redis adapter instance.
     *
     * Uses the native Redis extension if installed; otherwise falls back to Predis.
     * Ensures seamless operation across environments with or without Redis PECL.
     *
     * @return AdapterInterface The Redis-compatible adapter.
     */
    private function makeRedis(): AdapterInterface
    {
        $class = class_exists('\\Redis')
            ? '\\Maatify\\DataAdapters\\Adapters\\RedisAdapter'
            : '\\Maatify\\DataAdapters\\Adapters\\PredisAdapter';

        return new $class($this->config);
    }

    /**
     * 🔹 Create and return a MongoDB adapter instance.
     *
     * @return AdapterInterface The MongoDB adapter configured with environment settings.
     */
    private function makeMongo(): AdapterInterface
    {
        return new \Maatify\DataAdapters\Adapters\MongoAdapter($this->config);
    }

    /**
     * 🔹 Create and return a MySQL adapter instance.
     *
     * Dynamically selects between `PDO` and `Doctrine DBAL` based on the `MYSQL_DRIVER` environment variable.
     * Default driver is PDO unless explicitly overridden.
     *
     * @return AdapterInterface The MySQL adapter instance.
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
