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
    /**
     * Connect using phpredis extension.
     */
    public function connect(): void
    {
        $cfg = $this->resolveConfig(ConnectionTypeEnum::REDIS);

        try {
            $client = new Redis();

            $client->connect(
                $cfg->host ?? '127.0.0.1',
                (int)($cfg->port ?? 6379)
            );

            // 🔐 AUTH (if password exists)
            if (! empty($cfg->pass)) {
                $client->auth($cfg->pass);
            }

            // 🧪 Force ping to validate connection
            $pong = $client->ping();
            if ($pong === false) {
                throw new ConnectionException("Redis did not respond to PING.");
            }

            $this->connection = $client;
            $this->connected = true;
        } catch (Throwable $e) {
            throw new ConnectionException("Redis connection failed: " . $e->getMessage());
        }
    }

    /**
     * Basic health check.
     */
    public function healthCheck(): bool
    {
        try {
            return (bool)$this->connection?->ping();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Reconnect safely.
     */
    public function reconnect(): bool
    {
        $this->disconnect();
        $this->connect();

        return $this->connected;
    }
}
