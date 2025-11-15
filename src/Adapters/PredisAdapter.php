<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm)
 * @since       2025-11-08 20:44
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Adapters;

use Maatify\Common\Enums\ConnectionTypeEnum;
use Maatify\DataAdapters\Core\BaseAdapter;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use Predis\Client;
use Throwable;

/**
 * 🧩 **Class PredisAdapter**
 *
 * 🎯 Provides a Redis adapter using **Predis**, serving as a fallback
 * or secondary Redis client for the Maatify Data-Adapters system.
 *
 * Supports:
 * - DSN connection strings (preferred)
 * - Legacy host/port/password configurations
 * - Health checks and automatic reconnection
 *
 * This adapter is used heavily in the **Redis fallback subsystem**:
 * When php-redis fails, the system switches to Predis (Phase 6/6.1).
 *
 * @example Using DSN:
 * ```env
 * REDIS_CACHE_DSN="redis://127.0.0.1:6379?password=secret"
 * ```
 *
 * @example Classic ENV:
 * ```env
 * REDIS_HOST=127.0.0.1
 * REDIS_PORT=6379
 * REDIS_PASS=secret
 * ```
 */
final class PredisAdapter extends BaseAdapter
{
    /**
     * ⚙️ **Connect to Redis via Predis**
     *
     * Priority order:
     * 1. DSN URI (Predis-native)
     * 2. Legacy TCP configuration (scheme/host/port/password)
     *
     * @return void
     *
     * @throws ConnectionException If Predis cannot establish a connection
     */
    public function connect(): void
    {
        $cfg = $this->resolveConfig(ConnectionTypeEnum::REDIS);

        try {
            // -----------------------------------
            // 1️⃣ DSN MODE (recommended modern flow)
            // -----------------------------------
            if ($cfg->dsn) {
                $params = $cfg->dsn; // Predis accepts URI strings directly
            }

            // -----------------------------------
            // 2️⃣ Legacy mode (TCP)
            // -----------------------------------
            else {
                $params = [
                    'scheme'   => 'tcp',
                    'host'     => $cfg->host,
                    'port'     => (int)$cfg->port,
                    'password' => $cfg->pass,
                ];
            }

            // 🧩 Initialize Predis client
            $this->connection = new Client($params);
            $this->connection->connect();

            // 🎉 Mark as connected
            $this->connected = true;

        } catch (Throwable $e) {
            throw new ConnectionException("Predis connection failed: " . $e->getMessage());
        }
    }

    /**
     * 🩺 **Health check for Predis**
     *
     * Uses a simple `PING` command to verify connectivity.
     *
     * @return bool `true` if Redis responds, otherwise `false`
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
     * 🔄 **Reconnect to Redis using Predis**
     *
     * Disconnects and re-establishes the connection.
     *
     * @return bool `true` if reconnection works, otherwise `false`
     */
    public function reconnect(): bool
    {
        $this->disconnect();
        $this->connect();
        return $this->connected;
    }
}
