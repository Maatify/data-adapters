<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm)
 * @since       2025-11-08 20:48
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Adapters;

use Maatify\DataAdapters\Core\BaseAdapter;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use PDO;
use PDOException;

/**
 * ⚙️ Class MySQLAdapter
 *
 * 🧩 Purpose:
 * Implements a PDO-based MySQL database adapter extending {@see BaseAdapter}.
 * Provides secure, environment-based connection management with built-in
 * error handling, health checks, and reconnection support.
 *
 * ✅ Features:
 * - Establishes PDO connections using DSN built from environment variables.
 * - Automatically sets `ERRMODE_EXCEPTION` for robust error handling.
 * - Supports secure credentials loading from {@see EnvironmentConfig}.
 * - Provides database connectivity validation and reconnection capabilities.
 *
 * ⚙️ Example Usage:
 * ```php
 * use Maatify\DataAdapters\Adapters\MySQLAdapter;
 * use Maatify\DataAdapters\Core\EnvironmentConfig;
 *
 * $config = new EnvironmentConfig(__DIR__ . '/../');
 * $mysql = new MySQLAdapter($config);
 * $mysql->connect();
 *
 * if ($mysql->healthCheck()) {
 *     echo "✅ MySQL connection is active.";
 * }
 * ```
 *
 * @package Maatify\DataAdapters\Adapters
 */
final class MySQLAdapter extends BaseAdapter
{
    /**
     * 🔌 Establish a connection to the MySQL server using PDO.
     *
     * Builds a DSN string from environment configuration and creates a secure
     * PDO connection with UTF-8 support and exception-based error handling.
     *
     * @throws ConnectionException If connection or authentication fails.
     */
    public function connect(): void
    {
        try {
            // ⚙️ Build DSN for MySQL connection
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                $this->requireEnv('MYSQL_HOST'),
                $this->requireEnv('MYSQL_PORT'),
                $this->requireEnv('MYSQL_DB')
            );

            // 🔗 Establish PDO connection
            $this->connection = new PDO(
                $dsn,
                $this->requireEnv('MYSQL_USER'),
                $this->config->get('MYSQL_PASS'),
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );

            // ✅ Mark as connected
            $this->connected = true;
        } catch (PDOException $e) {
            throw new ConnectionException("MySQL connection failed: " . $e->getMessage());
        }
    }

    /**
     * 🩺 Check the health of the MySQL connection.
     *
     * Executes a lightweight query (`SELECT 1`) to verify if the
     * database connection is still valid.
     *
     * @return bool True if connection is alive, false otherwise.
     */
    public function healthCheck(): bool
    {
        try {
            return (bool)$this->connection?->query('SELECT 1')->fetchColumn();
        } catch (PDOException) {
            return false;
        }
    }

    /**
     * ♻️ Attempt to reconnect to the MySQL server.
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
