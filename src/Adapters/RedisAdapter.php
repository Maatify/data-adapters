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
 * 🧠 **Class RedisAdapter**
 *
 * 🎯 **Purpose:**
 * Implements the Redis data adapter providing connectivity, authentication,
 * health monitoring, and reconnection logic using the native PHP `Redis` extension.
 *
 * 🧩 **Key Features:**
 * - Connects securely to Redis via environment or config-defined credentials.
 * - Performs health checks with `PING`.
 * - Supports automatic reconnection logic on failure.
 * - Integrates seamlessly with the base adapter system.
 *
 * ✅ **Example Usage:**
 * ```php
 * use Maatify\DataAdapters\Adapters\RedisAdapter;
 *
 * $adapter = new RedisAdapter($config);
 * $adapter->connect();
 *
 * if ($adapter->healthCheck()) {
 *     echo "Redis connection healthy!";
 * }
 * ```
 */
final class RedisAdapter extends BaseAdapter
{
    /**
     * ⚙️ **Establish a Connection to Redis**
     *
     * Creates and configures a Redis client connection using host, port, and optional authentication.
     * Throws a {@see ConnectionException} on failure.
     *
     * @throws ConnectionException If connection or authentication fails.
     *
     * @return void
     */
    public function connect(): void
    {
        try {
            // ⚙️ Initialize Redis client instance
            $redis = new Redis();

            // 🔹 Connect using configuration (host & port)
            $redis->connect(
                $this->requireEnv('REDIS_HOST'),
                (int) $this->requireEnv('REDIS_PORT')
            );

            // 🔒 Authenticate if password is set
            $password = $this->config->get('REDIS_PASSWORD');
            if ($password) {
                $redis->auth($password);
            }

            // ✅ Store connection reference
            $this->connection = $redis;
            $this->connected  = $redis->ping() === '+PONG';
        } catch (Throwable $e) {
            // 🚫 Wrap all connection errors into a domain-specific exception
            throw new ConnectionException('Redis connection failed: ' . $e->getMessage());
        }
    }

    /**
     * 🩺 **Perform a Health Check**
     *
     * Tests the current Redis connection with a `PING` command.
     * Returns `true` if Redis responds correctly, otherwise `false`.
     *
     * @return bool `true` if Redis is alive, otherwise `false`.
     */
    public function healthCheck(): bool
    {
        try {
            // 🚫 Return false if no valid Redis instance exists
            if (! $this->connection instanceof Redis) {
                return false;
            }

            // ✅ Allow multiple possible valid responses from Redis
            $pong = $this->connection->ping();
            return $pong === true || $pong === 'PONG' || $pong === '+PONG';
        } catch (Throwable) {
            // 🚨 Any exception implies an unhealthy connection
            return false;
        }
    }

    /**
     * 🔁 **Reconnect to Redis**
     *
     * Attempts to close the existing connection and establish a new one.
     * Returns the updated connection status.
     *
     * @throws ConnectionException If reconnection fails entirely.
     *
     * @return bool `true` if reconnection succeeded, otherwise `false`.
     */
    public function reconnect(): bool
    {
        try {
            // 🧹 Disconnect cleanly before reattempting
            $this->disconnect();
            $this->connect();

            return $this->connected;
        } catch (Throwable $e) {
            // 🚫 Rewrap exceptions with additional context
            throw new ConnectionException('Redis reconnection failed: ' . $e->getMessage());
        }
    }
}
