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
 * ⚙️ **Class RedisAdapter**
 *
 * 🎯 **Purpose:**
 * Provides a high-performance, native Redis adapter built upon the official
 * PHP `Redis` extension. Implements the {@see BaseAdapter} contract and adds
 * robust connection management, authentication, and fallback handling.
 *
 * 🧠 **Key Features:**
 * - Secure connection initialization with `.env` configuration support.
 * - Automatic authentication and connectivity verification.
 * - Graceful fallback delegation (Redis → Predis) via {@see BaseAdapter::handleFailure()}.
 * - Integrated health checks and reconnection strategies.
 *
 * 🧩 **Typical Use Case:**
 * Used as the main caching or in-memory data adapter for Maatify projects,
 * seamlessly integrated with distributed systems or hybrid cache architectures.
 *
 * ✅ **Example Usage:**
 * ```php
 * use Maatify\DataAdapters\Adapters\RedisAdapter;
 * use Maatify\DataAdapters\Core\EnvironmentConfig;
 *
 * $config = new EnvironmentConfig(__DIR__ . '/../');
 * $redis  = new RedisAdapter($config);
 * $redis->connect();
 *
 * if ($redis->healthCheck()) {
 *     echo "✅ Redis is healthy and connected!";
 * }
 * ```
 */
final class RedisAdapter extends BaseAdapter
{
    /**
     * 🔌 **Establish a Connection**
     *
     * Connects to a Redis server using the configured host and port.
     * If a password is present in the environment, authentication is applied automatically.
     * When fallback mode is enabled, gracefully delegates connection recovery to
     * the BaseAdapter’s fallback logic.
     *
     * @throws ConnectionException|Throwable If connection or authentication fails.
     *
     * @return void
     */
    public function connect(): void
    {
        try {
            // ⚙️ Initialize Redis client
            $redis = new Redis();

            // 🔹 Connect using environment-defined host and port
            $redis->connect(
                $this->requireEnv('REDIS_HOST'),
                (int) $this->requireEnv('REDIS_PORT')
            );

            // 🔒 Authenticate when a password is defined
            $password = $this->config->get('REDIS_PASSWORD');
            if ($password) {
                $redis->auth($password);
            }

            // ✅ Store connection and confirm connectivity
            $this->connection = $redis;
            $this->connected  = $redis->ping() === '+PONG';
        } catch (Throwable $e) {
            // 🔁 Fallback handling (Redis → Predis) if enabled
            if ($this->isFallbackEnabled()) {
                $this->handleFailure($e, 'connect', fn() => $this->connect());
                return;
            }

            // 🚫 Throw connection exception for direct failure
            throw new ConnectionException('Redis connection failed: ' . $e->getMessage());
        }
    }

    /**
     * 🩺 **Perform a Health Check**
     *
     * Pings the Redis server to verify that the connection is alive and responsive.
     *
     * @return bool `true` if Redis responds successfully, otherwise `false`.
     */
    public function healthCheck(): bool
    {
        try {
            // 🚫 Return false if no valid Redis instance
            if (! $this->connection instanceof Redis) {
                return false;
            }

            // ✅ Accept multiple possible successful responses
            $pong = $this->connection->ping();
            return $pong === true || $pong === 'PONG' || $pong === '+PONG';
        } catch (Throwable) {
            // 🚨 Any exception implies an unhealthy connection
            return false;
        }
    }

    /**
     * ♻️ **Attempt to Reconnect**
     *
     * Closes any active connection and tries to re-establish a new one.
     * Automatically invokes fallback recovery if configured.
     *
     * @throws ConnectionException|Throwable If reconnection fails without fallback.
     *
     * @return bool `true` if reconnection succeeds, otherwise `false`.
     */
    public function reconnect(): bool
    {
        try {
            // 🧹 Cleanly disconnect and reattempt
            $this->disconnect();
            $this->connect();
            return $this->connected;
        } catch (Throwable $e) {
            // 🔁 Trigger fallback handler if enabled
            if ($this->isFallbackEnabled()) {
                $this->handleFailure($e, 'reconnect', fn() => $this->reconnect());
                return false;
            }

            // 🚫 Throw connection exception for unrecoverable error
            throw new ConnectionException('Redis reconnection failed: ' . $e->getMessage());
        }
    }
}
