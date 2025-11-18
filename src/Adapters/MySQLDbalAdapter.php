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
use Maatify\Common\Enums\ConnectionTypeEnum;
use Maatify\DataAdapters\Core\BaseAdapter;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use Maatify\DataAdapters\Core\Parser\MysqlDsnParser;
use Throwable;

/**
 * 🧩 **Class MySQLDbalAdapter**
 *
 * 🎯 Provides MySQL connectivity using **Doctrine DBAL** instead of raw PDO.
 * This adapter is ideal when the consumer requires:
 * - Doctrine QueryBuilder
 * - Schema management tools
 * - Cross-database abstraction layers
 *
 * It fully supports the Maatify DSN-first connection strategy (Phase 10), with
 * graceful fallback to traditional host/port/database configurations.
 *
 * ---
 * ### ✅ Example Usage
 * ```php
 * use Maatify\DataAdapters\Core\DatabaseResolver;
 *
 * $resolver = new DatabaseResolver($config);
 * $dbal = $resolver->resolve('mysql.reporting');
 *
 * $result = $dbal->connection()->executeQuery('SELECT NOW()')->fetchOne();
 * ```
 * ---
 */
final class MySQLDbalAdapter extends BaseAdapter
{
    /**
     * 🧠 **Connect using Doctrine DBAL**
     *
     * Supports 3 modes:
     *
     * 1️⃣ **Doctrine URL-style DSN**
     * ```
     * MYSQL_DSN="mysql://user:pass@127.0.0.1:3306/dbname"
     * ```
     *
     * 2️⃣ **PDO-style DSN**
     * Parsed and converted into DBAL format automatically.
     *
     * 3️⃣ **Legacy ENV fallback**
     * For older systems still using host/port/user/pass.
     *
     * ---
     * @throws ConnectionException When DBAL fails to initialize or execute the ping query.
     */
    public function connect(): void
    {
        $cfg = $this->resolveConfig(ConnectionTypeEnum::MYSQL);

        try {
            // --------------------------------------
            // 1️⃣ URL-style DSN → Full auto-parsed by DBAL
            // --------------------------------------
            if (! empty($cfg->dsn) && str_starts_with($cfg->dsn, 'mysql://')) {

                // Phase 15 unified parser
                $dsnParts = MysqlDsnParser::parse($cfg->dsn);

                $params = [
                    'host'     => $dsnParts['host']     ?? $cfg->host,
                    'port'     => isset($dsnParts['port']) ? (int)$dsnParts['port'] : (int)$cfg->port,
                    'dbname'   => $dsnParts['database'] ?? $cfg->database,   // IMPORTANT!!!
                    'user'     => $dsnParts['user']     ?? $cfg->user,
                    'password' => $dsnParts['pass']     ?? $cfg->pass,
                    'driver'   => 'pdo_mysql',
                    'charset'  => 'utf8mb4',
                ];

                $this->connection = DriverManager::getConnection(
                    $params,
                    new Configuration()
                );

                //                print_r($cfg);
                //                print_r($_ENV);
                $this->connection->executeQuery('SELECT 1');
                $this->connected = true;
                return;
            }

            // --------------------------------------
            // 2️⃣ PDO DSN → Convert manually to DBAL params
            // --------------------------------------
            elseif (! empty($cfg->dsn)) {
                // Convert mysql:host=127;dbname=xx → host=127&dbname=xx
                $body = str_replace('mysql:', '', $cfg->dsn);
                $body = str_replace(';', '&', $body);

                parse_str($body, $dsnParts);

                $params = [
                    'host'     => $dsnParts['host']   ?? $cfg->host,
                    'port'     => isset($dsnParts['port']) ? (int)$dsnParts['port'] : (int)$cfg->port,
                    'dbname'   => $dsnParts['dbname'] ?? $cfg->database,
                    'user'     => $cfg->user,
                    'password' => $cfg->pass,
                    'driver'   => 'pdo_mysql',
                    'charset'  => 'utf8mb4',
                ];
            }

            // --------------------------------------
            // 3️⃣ Legacy fallback (host/port/db/user/pass)
            // --------------------------------------
            else {
                $params = [
                    'host'     => $cfg->host,
                    'port'     => (int)$cfg->port,
                    'dbname'   => $cfg->database,
                    'user'     => $cfg->user,
                    'password' => $cfg->pass,
                    'driver'   => 'pdo_mysql',
                    'charset'  => 'utf8mb4',
                ];
            }

            // -------------------------------------------------
            // 🧩 Create Doctrine DBAL connection
            // -------------------------------------------------
            $this->connection = DriverManager::getConnection(
                $params,
                new Configuration()
            );

            //                print_r($cfg);
            //                print_r($_ENV);

            // 🧪 Force connection validation
            $this->connection->executeQuery('SELECT 1');

            $this->connected = true;
        } catch (Throwable $e) {
            throw new ConnectionException(
                'MySQL DBAL connection failed: ' . $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * 🧪 **Health Check**
     *
     * Executes a simple `SELECT 1` using DBAL's query engine.
     *
     * @return bool `true` if connection responds properly.
     *
     * ---
     * ### 🔹 Example
     * ```php
     * if (!$dbal->healthCheck()) {
     *     echo "DBAL service unreachable";
     * }
     * ```
     */
    public function healthCheck(): bool
    {
        try {
            return (int)$this->connection?->executeQuery('SELECT 1')->fetchOne() === 1;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * 🔄 **Reconnect**
     *
     * Recreates the Doctrine DBAL connection from scratch.
     *
     * @return bool Whether reconnection succeeded.
     *
     * ---
     * ### 🔹 Example
     * ```php
     * if (!$dbal->reconnect()) {
     *     throw new RuntimeException("Failed to reconnect DBAL");
     * }
     * ```
     */
    public function reconnect(): bool
    {
        $this->disconnect();
        $this->connect();

        return $this->connected;
    }

    public function getDriver(): \Doctrine\DBAL\Connection
    {
        if (!$this->isConnected()) {
            $this->connect();
        }

        return $this->connection;
    }

}
