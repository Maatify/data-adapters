<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-11-08
 * Time: 20:44
 * Project: maatify:data-adapters
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Adapters;

use Maatify\DataAdapters\Core\BaseAdapter;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use Predis\Client;
use Throwable;

/**
 * ⚙️ Class PredisAdapter
 *
 * 🧩 Purpose:
 * Provides a Redis-compatible adapter using the **Predis** PHP library instead of the native Redis extension.
 * Useful in environments where the PHP Redis extension is unavailable or not supported.
 *
 * ✅ Features:
 * - Fully compatible with Redis commands through the Predis client.
 * - Secure connection with password authentication.
 * - Connection health checking and auto-reconnection.
 * - Extends {@see BaseAdapter} for consistent interface and behavior.
 *
 * ⚙️ Example Usage:
 * ```php
 * use Maatify\DataAdapters\Adapters\PredisAdapter;
 * use Maatify\DataAdapters\Core\EnvironmentConfig;
 *
 * $config = new EnvironmentConfig(__DIR__ . '/../');
 * $predis = new PredisAdapter($config);
 * $predis->connect();
 *
 * if ($predis->healthCheck()) {
 *     echo "✅ Predis connected successfully.";
 * }
 * ```
 *
 * @package Maatify\DataAdapters\Adapters
 */
final class PredisAdapter extends BaseAdapter
{
    /**
     * 🔌 Establish a connection to Redis using the Predis client.
     *
     * Reads configuration from environment variables and creates a TCP connection.
     * Supports optional password authentication.
     *
     * @throws ConnectionException If the connection fails or Predis encounters an error.
     */
    public function connect(): void
    {
        try {
            // ⚙️ Initialize the Predis client
            $this->connection = new Client([
                'scheme'   => 'tcp',
                'host'     => $this->requireEnv('REDIS_HOST'),
                'port'     => (int)$this->requireEnv('REDIS_PORT'),
                'password' => $this->config->get('REDIS_PASSWORD'),
            ]);

            // 🔗 Establish connection
            $this->connection->connect();
            $this->connected = true;
        } catch (Throwable $e) {
            throw new ConnectionException("Predis connection failed: " . $e->getMessage());
        }
    }

    /**
     * 🩺 Check if the Predis connection is active and responsive.
     *
     * Executes a PING command to confirm connectivity with the Redis server.
     *
     * @return bool True if the Redis server responds; false if not connected.
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
     * ♻️ Reconnect to the Redis server via Predis.
     *
     * Disconnects any existing connection and attempts a fresh reconnection.
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
