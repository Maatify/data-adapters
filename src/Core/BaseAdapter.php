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

use JsonException;
use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\Common\DTO\ConnectionConfigDTO;
use Maatify\Common\Enums\ConnectionTypeEnum;
use Maatify\DataAdapters\Core\Config\MongoConfigBuilder;
use Maatify\DataAdapters\Core\Config\MySqlConfigBuilder;
use Maatify\DataAdapters\Core\Config\RedisConfigBuilder;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;

abstract class BaseAdapter implements AdapterInterface
{
    protected bool $connected = false;
    protected mixed $connection = null;
    protected ?string $profile;

    public function __construct(
        protected readonly EnvironmentConfig $config,
        ?string $profile = null
    )
    {
        $this->profile = $profile ?? 'main';
    }

    /**
     * DSN-first config resolution
     */
    protected function resolveConfig(ConnectionTypeEnum $type): ConnectionConfigDTO
    {
        return match ($type) {
            ConnectionTypeEnum::MYSQL => (new MySqlConfigBuilder($this->config))
                ->build($this->profile),

            ConnectionTypeEnum::MONGO => (new MongoConfigBuilder($this->config))
                ->build($this->profile),

            ConnectionTypeEnum::REDIS => (new RedisConfigBuilder($this->config))
                ->build($this->profile),

            default => throw new ConnectionException(
                "Unsupported adapter type: {$type->value}"
            ),
        };
    }

    /**
     * Required ENV helper
     */
    protected function requireEnv(string $key): string
    {
        $value = $this->config->get($key);

        if ($value === null) {
            throw new ConnectionException("Missing required environment variable: {$key}");
        }

        return $value;
    }

    abstract public function connect(): void;

    abstract public function reconnect(): bool;

    abstract public function healthCheck(): bool;

    public function isConnected(): bool
    {
        return $this->connected;
    }

    public function getConnection(): ?object
    {
        return $this->connection;
    }

    public function disconnect(): void
    {
        $this->connection = null;
        $this->connected = false;
    }

    /**
     * For PHPUnit debug only
     */
    public function debugConfig(): ConnectionConfigDTO
    {
        return $this->resolveConfig($this->getTypeEnum());
    }

    /**
     * More accurate typing for adapter type
     */
    protected function getTypeEnum(): ConnectionTypeEnum
    {
        return match (true) {
            $this instanceof \Maatify\DataAdapters\Adapters\MySQLAdapter,
                $this instanceof \Maatify\DataAdapters\Adapters\MySQLDbalAdapter => ConnectionTypeEnum::MYSQL,

            $this instanceof \Maatify\DataAdapters\Adapters\MongoAdapter => ConnectionTypeEnum::MONGO,

            $this instanceof \Maatify\DataAdapters\Adapters\RedisAdapter,
                $this instanceof \Maatify\DataAdapters\Adapters\PredisAdapter => ConnectionTypeEnum::REDIS,
        };
    }
}
