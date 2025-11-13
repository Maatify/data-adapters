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


abstract class BaseAdapter implements AdapterInterface
{
    protected bool $connected = false;

    protected mixed $connection = null;

    protected ?string $profile;

    public function __construct(
        protected readonly EnvironmentConfig $config,
        ?string $profile = null
    ) {
        $this->profile = $profile;
    }

    /**
     * Resolve connection config (DSN-first strategy).
     */
/*    protected function resolveConfig(ConnectionTypeEnum $type): ConnectionConfigDTO
    {
        // 1 — Try DSN
        $dsnKey = strtoupper($type->value . ($this->profile ? "_{$this->profile}" : "") . "_DSN");
        $dsnVal = $this->config->get($dsnKey);

        if ($dsnVal) {
            return new ConnectionConfigDTO(
                dsn: $dsnVal,
                user: $this->config->get($type->envPrefix() . '_USER'),
                pass: $this->config->get($type->envPrefix() . '_PASS'),
                database: $this->config->get($type->envPrefix() . '_DB'),
                profile: $this->profile
            );
        }

        // 2 — Legacy env fallback
        return new ConnectionConfigDTO(
            dsn: null,
            host: $this->config->get($type->envPrefix() . '_HOST'),
            port: $this->config->get($type->envPrefix() . '_PORT'),
            user: $this->config->get($type->envPrefix() . '_USER'),
            pass: $this->config->get($type->envPrefix() . '_PASS'),
            database: $this->config->get($type->envPrefix() . '_DB'),
            profile: $this->profile
        );
    }*/
    protected function resolveConfig(ConnectionTypeEnum $type): ConnectionConfigDTO
    {
        // -------------------------------
        // 1) Build dynamic profile prefix
        // -------------------------------
        //
        // Example:
        //   type = "mysql"
        //   profile = "main"
        //
        // Result:
        //   MYSQL_MAIN_DSN
        //   MYSQL_MAIN_USER
        //   MYSQL_MAIN_PASS
        //   MYSQL_MAIN_DB
        //
        $prefix = strtoupper($type->value);
        if ($this->profile) {
            $prefix .= '_' . strtoupper($this->profile);
        }

        // Build keys:
        $dsnKey  = $prefix . '_DSN';
        $userKey = $prefix . '_USER';
        $passKey = $prefix . '_PASS';
        $dbKey   = $prefix . '_DB';
        $hostKey = $prefix . '_HOST';
        $portKey = $prefix . '_PORT';

        // ---------------------------------------
        // 2) Try DSN mode first (highest priority)
        // ---------------------------------------
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

        // --------------------------------------------
        // 3) Legacy mode (separate host/port/user/pass)
        // --------------------------------------------
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

    /** @internal For PHPUnit tests only */
    public function debugConfig(): ConnectionConfigDTO
    {
        return $this->resolveConfig(ConnectionTypeEnum::from($this->getType()));
    }

    /** @internal */
    protected function getType(): string
    {
        return match (true) {
            $this instanceof \Maatify\DataAdapters\Adapters\MySQLAdapter,
                $this instanceof \Maatify\DataAdapters\Adapters\MySQLDbalAdapter => 'mysql',

            $this instanceof \Maatify\DataAdapters\Adapters\MongoAdapter => 'mongo',

            $this instanceof \Maatify\DataAdapters\Adapters\RedisAdapter,
                $this instanceof \Maatify\DataAdapters\Adapters\PredisAdapter => 'redis',
        };
    }
}
