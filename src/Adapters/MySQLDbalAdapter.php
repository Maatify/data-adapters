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
use Maatify\Common\DTO\ConnectionConfigDTO;
use Maatify\Common\Enums\ConnectionTypeEnum;
use Maatify\DataAdapters\Core\BaseAdapter;
use Maatify\DataAdapters\Core\Config\MySqlConfigBuilder;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use Throwable;

/**
 * 🧩 **Class MySQLDbalAdapter**
 *
 * 🎯 Doctrine DBAL-based MySQL adapter for the Maatify Data-Adapters library.
 *
 * This adapter provides:
 * - Full support for **DSN-first configuration** (Phase 11)
 * - Doctrine-style URL DSNs (`mysql://user:pass@host/dbname`)
 * - Smart fallback to PDO-style DSNs
 * - Legacy `.env` host/port/database compatibility
 *
 * ⚙️ Under the hood:
 * - Uses Doctrine `DriverManager::getConnection()`
 * - Ensures UTF-8MB4 charset
 * - Performs an initial `SELECT 1` to validate connectivity
 *
 * @example Using Doctrine URL DSN:
 * ```env
 * MYSQL_MAIN_DSN="mysql://user:pass@127.0.0.1:3306/mydb"
 * ```
 *
 * @example Using PDO DSN:
 * ```env
 * MYSQL_MAIN_DSN="mysql:host=127.0.0.1;port=3306;dbname=mydb"
 * ```
 */
final class MySQLDbalAdapter extends BaseAdapter
{
    /**
     * 🧠 **Resolve configuration for Doctrine DBAL**
     *
     * Merges:
     * - Legacy configuration (BaseAdapter)
     * - Profile-based configuration (MySqlConfigBuilder)
     *
     * DSN takes priority when available.
     *
     * @param ConnectionTypeEnum $type Always MYSQL
     *
     * @return ConnectionConfigDTO Fully resolved configuration
     */
    protected function resolveConfig(ConnectionTypeEnum $type): ConnectionConfigDTO
    {
        $legacy = parent::resolveConfig($type);

        // 🧩 Build DSN/host/port/database via builder
        $builder = new MySqlConfigBuilder($this->config);
        $mysql   = $builder->build($this->profile ?? 'main');

        return new ConnectionConfigDTO(
            dsn:      $mysql->dsn      ?? $legacy->dsn,
            host:     $mysql->host     ?? $legacy->host,
            port:     $mysql->port     ?? $legacy->port,
            user:     $legacy->user,
            pass:     $legacy->pass,
            database: $mysql->database ?? $legacy->database,
            options:  $legacy->options,
            driver:   'dbal',
            profile:  $legacy->profile
        );
    }

    /**
     * ⚙️ **Establish a DBAL MySQL connection**
     *
     * Priority:
     * 1. Doctrine URL DSN  → `mysql://...`
     * 2. PDO-style DSN      → `mysql:host=...;port=...;dbname=...`
     * 3. Legacy ENV config  → host/port/dbname/user/pass
     *
     * @return void
     *
     * @throws ConnectionException When DBAL fails to connect
     */
    public function connect(): void
    {
        $cfg = $this->resolveConfig(ConnectionTypeEnum::MYSQL);

        try {
            // -----------------------------
            // 1️⃣ Doctrine URL DSN mode
            // -----------------------------
            if ($cfg->dsn && str_starts_with($cfg->dsn, 'mysql://')) {
                $params = [
                    'url'    => $cfg->dsn,
                    'driver' => 'pdo_mysql',
                ];
            }

            // -----------------------------
            // 2️⃣ PDO-style DSN mode
            // -----------------------------
            elseif ($cfg->dsn) {
                // Convert "mysql:host=...;port=...;dbname=..." → array
                $dnsBody = str_replace('mysql:', '', $cfg->dsn);
                $dnsBody = str_replace(';', '&', $dnsBody);

                // Extract DSN parameters
                parse_str($dnsBody, $pdo);

                $params = [
                    'host'     => $pdo['host'] ?? $cfg->host,
                    'port'     => isset($pdo['port']) ? (int)$pdo['port'] : (int)$cfg->port,
                    'dbname'   => $pdo['dbname'] ?? $cfg->database,
                    'user'     => $cfg->user,
                    'password' => $cfg->pass,
                    'driver'   => 'pdo_mysql',
                    'charset'  => 'utf8mb4',
                ];
            }

            // -----------------------------
            // 3️⃣ Legacy environment fallback
            // -----------------------------
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

            // 🧩 Create Doctrine connection
            $this->connection = DriverManager::getConnection($params, new Configuration());

            // 🔍 Validate connection with a simple query
            $this->connection->executeQuery('SELECT 1');

            // 🎉 Mark connected state
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
     * 🩺 **Health check**
     *
     * Executes `SELECT 1` and ensures the result equals `1`.
     *
     * @return bool `true` if DB responds correctly, otherwise `false`
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
     * Disconnects current connection and attempts to reconnect.
     *
     * @return bool `true` on success, otherwise `false`
     */
    public function reconnect(): bool
    {
        $this->disconnect();
        $this->connect();
        return $this->connected;
    }
}
