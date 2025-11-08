<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-11-08
 * Time: 20:41
 * Project: maatify:data-adapters
 * IDE: PhpStorm
 * https://www.Maatify.dev
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
     * 🩺 Check the health of the Redis connection.
     *
     * Performs a PING command if a connection is active to verify availability.
     *
     * @return bool True if the Redis server responds with `+PONG`.
     */
    public function healthCheck(): bool
    {
        return $this->connected && $this->connection?->ping() === '+PONG';
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
