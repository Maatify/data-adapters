<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm)
 * @since       2025-11-08 20:41
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Adapters;

use Maatify\Common\Enums\ConnectionTypeEnum;
use Maatify\DataAdapters\Core\BaseAdapter;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use Redis;
use Throwable;

final class RedisAdapter extends BaseAdapter
{
    public function connect(): void
    {
        $cfg = $this->resolveConfig(ConnectionTypeEnum::REDIS);
        try {
            // --------------------------------------------------
            // 🔧 Fix Redis DSN Parsing (special case for Redis)
            // --------------------------------------------------
            if ($cfg->dsn && (!$cfg->host || !$cfg->port || !$cfg->pass)) {
                $parts = parse_url($cfg->dsn);

                if ($parts !== false) {
                    $cfg->host = $parts['host'] ?? $cfg->host;
                    $cfg->port = $parts['port'] ?? $cfg->port;
                    $cfg->pass = $parts['pass'] ?? $cfg->pass;
                    $cfg->user = $parts['user'] ?? $cfg->user;
                }
            }
            // --------------------------------------------------

            // If DSN exists → use DSN parser
            if ($cfg->dsn) {
                $redis = new Redis();
                $redis->connect($cfg->host ?? '127.0.0.1', (int)($cfg->port ?? 6379));

                if ($cfg->pass) {
                    $redis->auth($cfg->pass);
                }
            } else {
                // Legacy ENV
                $redis = new Redis();
                $redis->connect($cfg->host, (int)$cfg->port);

                if ($cfg->pass) {
                    $redis->auth($cfg->pass);
                }
            }

            $this->connection = $redis;
            $this->connected  = $redis->ping() !== false;

        } catch (Throwable $e) {
            throw new ConnectionException("Redis connection failed: " . $e->getMessage());
        }
    }

    public function healthCheck(): bool
    {
        try {
            return (bool)$this->connection?->ping();
        } catch (Throwable) {
            return false;
        }
    }

    public function reconnect(): bool
    {
        $this->disconnect();
        $this->connect();
        return $this->connected;
    }
}
