<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-11-08
 * Time: 20:47
 * Project: maatify:data-adapters
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Adapters;

use Maatify\DataAdapters\Core\BaseAdapter;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use MongoDB\Client;
use Throwable;

/**
 * ⚙️ Class MongoAdapter
 *
 * 🧩 Purpose:
 * Implements a MongoDB adapter extending {@see BaseAdapter}, providing
 * a unified connection layer for MongoDB databases. Supports authentication,
 * environment-based configuration, and connection health checks.
 *
 * ✅ Features:
 * - Connects securely using MongoDB DSN.
 * - Supports authentication credentials.
 * - Verifies connectivity via `ping` command.
 * - Provides reconnection handling for fault tolerance.
 *
 * ⚙️ Example Usage:
 * ```php
 * use Maatify\DataAdapters\Adapters\MongoAdapter;
 * use Maatify\DataAdapters\Core\EnvironmentConfig;
 *
 * $config = new EnvironmentConfig(__DIR__ . '/../');
 * $mongo = new MongoAdapter($config);
 * $mongo->connect();
 *
 * if ($mongo->healthCheck()) {
 *     echo "✅ MongoDB connection is healthy.";
 * }
 * ```
 *
 * @package Maatify\DataAdapters\Adapters
 */
final class MongoAdapter extends BaseAdapter
{
    /**
     * 🔌 Establish a connection to the MongoDB server.
     *
     * Constructs a MongoDB DSN string using environment configuration values.
     * Supports optional authentication via username and password.
     *
     * @throws ConnectionException If the connection attempt fails.
     */
    public function connect(): void
    {
        try {
            // 🧩 Build DSN string
            $dsn = sprintf(
                'mongodb://%s:%s',
                $this->requireEnv('MONGO_HOST'),
                $this->requireEnv('MONGO_PORT')
            );

            // ⚙️ Initialize MongoDB client with optional authentication
            $this->connection = new Client($dsn, [
                'username' => $this->config->get('MONGO_USER'),
                'password' => $this->config->get('MONGO_PASS'),
            ]);

            // ✅ Mark as connected
            $this->connected = true;
        } catch (Throwable $e) {
            throw new ConnectionException("Mongo connection failed: " . $e->getMessage());
        }
    }

    /**
     * 🩺 Perform a MongoDB health check.
     *
     * Executes a simple `ping` command against the target database
     * (defaults to `admin` if not specified) to verify connectivity.
     *
     * @return bool True if MongoDB responds successfully, false otherwise.
     */
    public function healthCheck(): bool
    {
        try {
            $dbName = $this->config->get('MONGO_DB', 'admin');
            $this->connection->selectDatabase($dbName)->command(['ping' => 1]);
            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * ♻️ Attempt to reconnect to MongoDB.
     *
     * Closes the existing connection (if any) and re-establishes a new one.
     *
     * @return bool True if reconnection succeeds, false otherwise.
     */
    public function reconnect(): bool
    {
        $this->disconnect();
        $this->connect();

        return $this->connected;
    }
}
