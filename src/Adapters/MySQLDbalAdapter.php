<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm)
 * @since       2025-11-08 20:50
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Adapters;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;
use Maatify\DataAdapters\Core\BaseAdapter;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use Throwable;

/**
 * ⚙️ **Class MySQLDbalAdapter**
 *
 * 🎯 **Purpose:**
 * Implements a MySQL database adapter using **Doctrine DBAL** for advanced
 * abstraction, query handling, and connection management within the
 * Maatify Data Adapters ecosystem.
 *
 * 🧠 **Core Features:**
 * - Uses Doctrine DBAL for robust database operations.
 * - Handles secure connection initialization with environment-based parameters.
 * - Supports connection testing, reconnection, and failure-safe retries.
 * - Automatically validates connection health before operations.
 *
 * ✅ **Example Usage:**
 * ```php
 * use Maatify\DataAdapters\Adapters\MySQLDbalAdapter;
 * use Maatify\DataAdapters\Core\EnvironmentConfig;
 *
 * $config = new EnvironmentConfig(__DIR__ . '/../');
 * $mysql = new MySQLDbalAdapter($config);
 * $mysql->connect();
 *
 * if ($mysql->healthCheck()) {
 *     echo "✅ MySQL DBAL connection is healthy.";
 * }
 * ```
 */
final class MySQLDbalAdapter extends BaseAdapter
{
    /**
     * 🔌 **Establish a new MySQL (Doctrine DBAL) connection.**
     *
     * Reads configuration values from environment variables via {@see EnvironmentConfig}
     * and initializes a Doctrine DBAL connection. Automatically performs a lightweight
     * connectivity check (`SELECT 1`) to ensure a valid connection.
     *
     * @throws ConnectionException If connection fails due to invalid configuration or network errors.
     *
     * @return void
     */
    public function connect(): void
    {
        try {
            // 🧠 Define DB connection parameters from environment configuration
            $connectionParams = [
                'dbname'   => $this->requireEnv('MYSQL_DB'),
                'user'     => $this->requireEnv('MYSQL_USER'),
                'password' => $this->config->get('MYSQL_PASS'),
                'host'     => $this->requireEnv('MYSQL_HOST'),
                'port'     => (int) $this->config->get('MYSQL_PORT', '3306'),
                'driver'   => 'pdo_mysql',
                'charset'  => 'utf8mb4',
            ];

            // ⚙️ Establish connection using Doctrine DBAL
            $this->connection = DriverManager::getConnection($connectionParams, new Configuration());

            // 🧩 Trigger connection validation with lightweight query
            $this->connection->executeQuery('SELECT 1');
            $this->connected = $this->connection->isConnected();
        } catch (Throwable $e) {
            throw new ConnectionException(
                'MySQL DBAL connection failed: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * 🩺 **Perform a health check on the active MySQL connection.**
     *
     * Executes a simple `SELECT 1` query to verify connection stability.
     *
     * @return bool `true` if connection is valid and responsive, otherwise `false`.
     */
    public function healthCheck(): bool
    {
        try {
            // 🚫 Return false if no active connection
            if (! $this->connected || ! $this->connection) {
                return false;
            }

            // ✅ Execute test query
            $result = $this->connection->executeQuery('SELECT 1')->fetchOne();

            return (int) $result === 1;
        } catch (Throwable) {
            // 🚫 Catch all exceptions (connection lost, timeout, etc.)
            return false;
        }
    }

    /**
     * 🔁 **Reconnect to the database.**
     *
     * Safely terminates any existing connection and attempts to reconnect.
     * Returns a boolean representing reconnection success.
     *
     * @return bool `true` if reconnection succeeds, otherwise `false`.
     */
    public function reconnect(): bool
    {
        // 🧹 Clean up current connection
        $this->disconnect();

        try {
            $this->connect();
            return $this->connected;
        } catch (Exception) {
            return false;
        }
    }
}
