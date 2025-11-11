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

use Maatify\DataAdapters\Core\BaseAdapter;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use Redis;
use Throwable;

/**
 * ⚙️ Class RedisAdapter
 *
 * 🧩 Purpose:
 * Provides a native Redis implementation of {@see BaseAdapter} for managing
 * in-memory cache and data operations. Utilizes the PHP Redis extension for
 * optimal performance and reliability, with **optional Fallback Intelligence**
 * handled by the BaseAdapter.
 *
 * ✅ Features:
 * - Secure connection handling with optional authentication.
 * - Built-in connection state tracking and health checks.
 * - Automatic reconnection logic for resilience.
 * - Optional fallback (Redis → Predis) when enabled via `.env`.
 */
final class RedisAdapter extends BaseAdapter
{
    /**
     * 🔌 Establish a connection to the Redis server.
     *
     * Reads host, port, and optional password from environment variables
     * via {@see EnvironmentConfig}, and performs a connection and authentication.
     *
     * @throws ConnectionException|Throwable When connection or authentication fails.
     */
    public function connect(): void
    {
        try {
            // ⚙️ Initialize Redis client
            $redis = new Redis();

            // 🔹 Connect using environment configuration
            $redis->connect(
                $this->requireEnv('REDIS_HOST'),
                (int)$this->requireEnv('REDIS_PORT')
            );

            // 🔒 Authenticate if password is provided
            $password = $this->config->get('REDIS_PASSWORD');
            if ($password) {
                $redis->auth($password);
            }

            // ✅ Save connection and verify connectivity
            $this->connection = $redis;
            $this->connected  = $redis->ping() === '+PONG';
        } catch (Throwable $e) {
            // 🔁 If fallback mode enabled, delegate to BaseAdapter handler
            if ($this->isFallbackEnabled()) {
                $this->handleFailure($e, 'connect', fn() => $this->connect());
                return;
            }

            // 🚫 Otherwise behave exactly as before
            throw new ConnectionException("Redis connection failed: " . $e->getMessage());
        }
    }

    /**
     * 🩺 Perform a Redis health check.
     */
    public function healthCheck(): bool
    {
        try {
            if (! $this->connection instanceof Redis) {
                return false;
            }

            $pong = $this->connection->ping();
            return $pong === true || $pong === 'PONG' || $pong === '+PONG';
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * ♻️ Attempt to reconnect to Redis.
     *
     * @return bool True if reconnection succeeds.
     * @throws Throwable
     */
    public function reconnect(): bool
    {
        try {
            $this->disconnect();
            $this->connect();
            return $this->connected;
        } catch (Throwable $e) {
            if ($this->isFallbackEnabled()) {
                $this->handleFailure($e, 'reconnect', fn() => $this->reconnect());
                return false;
            }

            throw new ConnectionException('Redis reconnection failed: ' . $e->getMessage());
        }
    }
}
