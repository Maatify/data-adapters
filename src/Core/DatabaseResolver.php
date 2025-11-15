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

final class DatabaseResolver
{
    private array $mongoCache = [];

    public function __construct(
        private readonly EnvironmentConfig $config
    )
    {
    }

    public function resolve(string $route, bool $autoConnect = false): AdapterInterface
    {
        [$type, $profile] = $this->parseStringRoute($route);

        // 🔥 Always ensure a non-null profile (Phase 13 rule)
        $profile = $profile ? : 'main';

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

    private function parseStringRoute(string $value): array
    {
        $value = strtolower(trim($value));

        if (str_contains($value, '.')) {
            [$type, $profile] = explode('.', $value, 2);

            return [$type, $profile];
        }

        return [$value, null];
    }

    private function makeRedis(string $profile): AdapterInterface
    {
        $class = class_exists('\\Redis')
            ? '\\Maatify\\DataAdapters\\Adapters\\RedisAdapter'
            : '\\Maatify\\DataAdapters\\Adapters\\PredisAdapter';

        return new $class($this->config, $profile);
    }

    private function makeMongo(string $profile): AdapterInterface
    {
        return $this->mongoCache[$profile]
            ??= new MongoAdapter($this->config, $profile);
    }

    private function makeMySQL(string $profile): AdapterInterface
    {
        $driverKey = strtoupper("MYSQL_{$profile}_DRIVER");

        $driver = strtolower($this->config->get($driverKey, 'pdo'));

        $class = $driver === 'dbal'
            ? '\\Maatify\\DataAdapters\\Adapters\\MySQLDbalAdapter'
            : '\\Maatify\\DataAdapters\\Adapters\\MySQLAdapter';

        return new $class($this->config, $profile);
    }
}
