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

/**
 * 🧩 **Class DatabaseResolver**
 *
 * 🎯 Acts as the central routing system that resolves connection adapters
 * based on string definitions.
 *
 * Supported routes:
 * - `"mysql"`
 * - `"mysql.main"`
 * - `"mongo"`
 * - `"mongo.logs"`
 * - `"redis"`
 *
 * ✔ Supports per-profile adapters
 * ✔ Supports redis fallback (phpredis → Predis)
 * ✔ Transparent adapter selection for MySQL (PDO vs DBAL)
 * ✔ Mongo adapters are cached per profile for better performance
 *
 * @example Basic usage:
 * ```php
 * $resolver = new DatabaseResolver($env);
 * $mysql = $resolver->resolve('mysql.main', autoConnect: true);
 * ```
 *
 * @example Mongo logs profile:
 * ```php
 * $mongo = $resolver->resolve('mongo.logs');
 * ```
 */
final class DatabaseResolver
{
    /**
     * Cache for Mongo adapters keyed by profile.
     *
     * @var array<string, MongoAdapter>
     */
    private array $mongoCache = [];

    /**
     * @param EnvironmentConfig $config Environment configuration loader
     */
    public function __construct(
        private readonly EnvironmentConfig $config
    ) {}

    /**
     * 🎯 **Resolve adapter using string routing**
     *
     * Supported formats:
     * - `"type"`
     * - `"type.profile"`
     *
     * Examples:
     * - `"mysql.main"`
     * - `"redis"`
     * - `"mongo.logs"`
     *
     * @param string $route       Adapter route (e.g., "mysql.main")
     * @param bool   $autoConnect Whether to immediately call `connect()`
     *
     * @return AdapterInterface Resolved adapter instance
     *
     * @throws ConnectionException If adapter type is unsupported
     */
    public function resolve(string $route, bool $autoConnect = false): AdapterInterface
    {
        [$type, $profile] = $this->parseStringRoute($route);

        $adapter = match ($type) {
            'redis' => $this->makeRedis($profile),
            'mongo' => $this->makeMongo($profile),
            'mysql' => $this->makeMySQL($profile),
            default => throw new ConnectionException("Unsupported adapter: {$type}")
        };

        if ($autoConnect) {
            $adapter->connect();
        }

        return $adapter;
    }

    /**
     * 🧩 **Parse route format**
     *
     * `"type.profile"` → `["type", "profile"]`
     * `"type"`         → `["type", null]`
     *
     * @param string $value Route string
     *
     * @return array{string, string|null} Parsed type and profile
     */
    private function parseStringRoute(string $value): array
    {
        $value = strtolower(trim($value));

        if (str_contains($value, '.')) {
            [$type, $profile] = explode('.', $value, 2);
            return [$type, $profile];
        }

        return [$value, null];
    }

    /**
     * ⚙️ **Create Redis adapter**
     *
     * Automatically selects:
     * - `RedisAdapter` if phpredis extension is installed
     * - `PredisAdapter` otherwise
     *
     * @param string|null $profile
     *
     * @return AdapterInterface
     */
    private function makeRedis(?string $profile = null): AdapterInterface
    {
        $class = class_exists('\\Redis')
            ? '\\Maatify\\DataAdapters\\Adapters\\RedisAdapter'
            : '\\Maatify\\DataAdapters\\Adapters\\PredisAdapter';

        return new $class($this->config, $profile);
    }

    /**
     * ⚙️ **Create or fetch cached Mongo adapter**
     *
     * Mongo adapters are cached per profile for performance because
     * MongoDB client creation is heavier than MySQL/Redis.
     *
     * @param string|null $profile Mongo profile (default: "main")
     *
     * @return AdapterInterface
     */
    private function makeMongo(?string $profile = null): AdapterInterface
    {
        $key = $profile ?? 'main';

        return $this->mongoCache[$key]
            ??= new MongoAdapter($this->config, $key);
    }

    /**
     * ⚙️ **Create MySQL adapter**
     *
     * Chooses driver based on:
     * ```
     * MYSQL_DRIVER=pdo
     * MYSQL_MAIN_DRIVER=dbal
     * MYSQL_LOGS_DRIVER=dbal
     * ```
     *
     * Supported drivers:
     * - `"pdo"`  → `MySQLAdapter`
     * - `"dbal"` → `MySQLDbalAdapter`
     *
     * @param string|null $profile
     *
     * @return AdapterInterface
     */
    private function makeMySQL(?string $profile = null): AdapterInterface
    {
        // Build environment key for driver selection
        $driverKey = strtoupper("MYSQL" . ($profile ? "_{$profile}" : "") . "_DRIVER");

        // Default: pdo
        $driver = strtolower($this->config->get($driverKey, 'pdo'));

        $class = $driver === 'dbal'
            ? '\\Maatify\\DataAdapters\\Adapters\\MySQLDbalAdapter'
            : '\\Maatify\\DataAdapters\\Adapters\\MySQLAdapter';

        return new $class($this->config, $profile);
    }
}
