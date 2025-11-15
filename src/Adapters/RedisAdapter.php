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

/**
 * 🧩 **Class RedisAdapter**
 *
 * 🎯 High-performance Redis adapter using the **phpredis** extension.
 *
 * Supports:
 * - DSN-based configuration (`redis://host:port?password=...`)
 * - Legacy ENV-based configuration (`REDIS_HOST`, `REDIS_PORT`, etc.)
 * - Automatic DSN parsing (host/port/pass/user)
 * - Health checking and reconnection utilities
 *
 * ⚙️ This adapter is the primary Redis driver in the Maatify ecosystem.
 * It works in coordination with **PredisAdapter** as a fallback layer.
 *
 * @example Using DSN:
 * ```env
 * REDIS_CACHE_DSN="redis://:mypassword@127.0.0.1:6379"
 * ```
 *
 * @example Legacy mode:
 * ```env
 * REDIS_HOST=127.0.0.1
 * REDIS_PORT=6379
 * REDIS_PASS=mypassword
 * ```
 */
final class RedisAdapter extends BaseAdapter
{
    /**
     * ⚙️ **Connect to Redis using the phpredis extension**
     *
     * Steps:
     * 1. Parse DSN if provided (handles host/port/pass/user)
     * 2. Establish connection
     * 3. Authenticate (if password exists)
     * 4. Verify connection by sending `PING`
     *
     * @return void
     *
     * @throws ConnectionException If connection or authentication fails
     */
    public function connect(): void
    {
        $cfg = $this->resolveConfig(ConnectionTypeEnum::REDIS);

        try {
            // ------------------------------------------------------
            // 🧩 Redis-specific DSN parser (fixes missing fields)
            // ------------------------------------------------------
            if ($cfg->dsn && (!$cfg->host || !$cfg->port || !$cfg->pass)) {
                $parts = parse_url($cfg->dsn);

                if ($parts !== false) {
                    // 🔍 Override missing host
                    if (isset($parts['host'])) {
                        $cfg->host = $parts['host'];
                    }

                    // 🔍 Override missing port (normalize to string)
                    if (isset($parts['port'])) {
                        $cfg->port = (string)$parts['port'];
                    }

                    // 🔍 Override missing password
                    if (isset($parts['pass'])) {
                        $cfg->pass = $parts['pass'];
                    }

                    // 🔍 Override missing username
                    if (isset($parts['user'])) {
                        $cfg->user = $parts['user'];
                    }
                }
            }

            // ------------------------------------------------------
            // 1️⃣ DSN mode or 2️⃣ Legacy mode (same code path after parsing)
            // ------------------------------------------------------
            $redis = new Redis();

            // Connect using resolved host/port
            $redis->connect(
                $cfg->host ?? '127.0.0.1',
                (int)($cfg->port ?? 6379)
            );

            // Authenticate if password exists
            if ($cfg->pass) {
                $redis->auth($cfg->pass);
            }

            $this->connection = $redis;

            // 🎯 Validate connection
            $this->connected = $redis->ping() !== false;

        } catch (Throwable $e) {
            throw new ConnectionException("Redis connection failed: " . $e->getMessage());
        }
    }

    /**
     * 🩺 **Health check**
     *
     * Uses a simple `PING` command to verify that Redis is responsive.
     *
     * @return bool `true` if Redis responds correctly, otherwise `false`
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
     * 🔄 **Reconnect to Redis**
     *
     * Disconnects the existing Redis connection and rebuilds it.
     *
     * @return bool `true` on successful reconnection, otherwise `false`
     */
    public function reconnect(): bool
    {
        $this->disconnect();
        $this->connect();
        return $this->connected;
    }
}
