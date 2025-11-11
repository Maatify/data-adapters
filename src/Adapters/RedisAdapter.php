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
 * optimal performance and reliability.
 *
 * ✅ Features:
 * - Secure connection handling with optional authentication.
 * - Built-in connection state tracking and health checks.
 * - Automatic reconnection logic for resilience.
 * - Extends shared adapter base class for unified structure.
 *
 * ⚙️ Example Usage:
 * ```php
 * use Maatify\DataAdapters\Adapters\RedisAdapter;
 * use Maatify\DataAdapters\Core\EnvironmentConfig;
 *
 * $config = new EnvironmentConfig(__DIR__ . '/../');
 * $redis = new RedisAdapter($config);
 * $redis->connect();
 *
 * if ($redis->healthCheck()) {
 *     echo "✅ Redis is connected and responding.";
 * }
 * ```
 *
 * @package Maatify\DataAdapters\Adapters
 */
final class RedisAdapter extends BaseAdapter
{
    /**
     * 🔌 Establish a connection to the Redis server.
     *
     * Reads host, port, and optional password from environment variables
     * via {@see EnvironmentConfig}, and performs a connection and authentication.
     *
     * @throws ConnectionException When connection or authentication fails.
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
            $this->connected = $redis->ping() === '+PONG';
        } catch (Throwable $e) {
            throw new ConnectionException("Redis connection failed: " . $e->getMessage());
        }
    }

    /**
     * 🩺 **Perform a Redis health check.**
     *
     * 🎯 **Purpose:**
     * Validates that the active Redis connection is alive and responsive.
     * Used to ensure that adapter-dependent operations can proceed safely.
     *
     * 🧠 **Logic:**
     * - Verifies that `$this->connection` is an instance of `Redis`.
     * - Executes a `PING` command to confirm server responsiveness.
     * - Interprets valid responses (`true`, `'PONG'`, or `'+PONG'`) as healthy.
     * - Silently returns `false` on any exception or invalid state.
     *
     * @return bool `true` if Redis responds successfully; otherwise `false`.
     *
     * ✅ **Example:**
     * ```php
     * if (! $redis->healthCheck()) {
     *     throw new RuntimeException('Redis is unavailable.');
     * }
     * ```
     */
    public function healthCheck(): bool
    {
        try {
            // 🧠 Ensure the connection object is valid before testing
            if (! $this->connection instanceof Redis) {
                return false;
            }

            // ⚙️ Perform a PING command to verify connectivity
            $pong = $this->connection->ping();

            // ✅ Accept any known valid PONG responses
            return $pong === true || $pong === 'PONG' || $pong === '+PONG';
        } catch (Throwable) {
            // 🚫 Any error during the ping indicates an unhealthy connection
            return false;
        }
    }

    /**
     * ♻️ Attempt to reconnect to Redis.
     *
     * Safely closes the current connection and reinitializes it.
     *
     * @return bool True if reconnection succeeds.
     */
    public function reconnect(): bool
    {
        $this->disconnect();
        $this->connect();

        return $this->connected;
    }
}
