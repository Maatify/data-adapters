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

use Maatify\Common\DTO\ConnectionConfigDTO;
use Maatify\Common\Enums\ConnectionTypeEnum;
use Maatify\DataAdapters\Core\BaseAdapter;
use Maatify\DataAdapters\Core\Config\MySqlConfigBuilder;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use PDO;
use PDOException;

/**
 * 🧩 **Class MySQLAdapter**
 *
 * 🎯 Provides a unified, profile-aware MySQL adapter built on top of PDO.
 * This class integrates both:
 * - **Legacy BaseAdapter configuration** (backward compatible)
 * - **New DSN + profile-based configuration** via `MySqlConfigBuilder`
 *
 * ⚙️ Features:
 * - DSN-first priority (introduced in Phase 11)
 * - Host/port/db fallback for older `.env` setups
 * - Automatic UTF-8MB4 charset enforcement
 * - Health check and reconnect utilities
 *
 * @example Basic usage:
 * ```php
 * $mysql = new MySQLAdapter($config, 'reporting');
 * $mysql->connect();
 * if ($mysql->healthCheck()) {
 *     echo "MySQL OK!";
 * }
 * ```
 *
 * @example With DSN:
 * ```env
 * MYSQL_MAIN_DSN="mysql:host=localhost;dbname=maatify;charset=utf8mb4"
 * ```
 */
final class MySQLAdapter extends BaseAdapter
{
    /**
     * 🧠 **Resolve configuration for MySQL**
     *
     * 🔹 Phase 11 behavior:
     * Merges the legacy configuration from `BaseAdapter` with the
     * profile-specific configuration returned by `MySqlConfigBuilder`.
     *
     * Only overrides fields returned by the builder (DSN-first approach).
     *
     * @param ConnectionTypeEnum $type  Always `ConnectionTypeEnum::MYSQL`
     *
     * @return ConnectionConfigDTO Fully resolved configuration object
     */
    protected function resolveConfig(ConnectionTypeEnum $type): ConnectionConfigDTO
    {
        $legacy = parent::resolveConfig($type);

        // 🧩 Build profile-based DSN/host configuration
        $builder = new MySqlConfigBuilder($this->config);
        $mysql   = $builder->build($this->profile);

        // 🔹 Merge DSN-first while keeping legacy safe defaults
        return new ConnectionConfigDTO(
            dsn:      $mysql->dsn      ?? $legacy->dsn,
            host:     $mysql->host     ?? $legacy->host,
            port:     $mysql->port     ?? $legacy->port,
            user:     $legacy->user,
            pass:     $legacy->pass,
            database: $mysql->database ?? $legacy->database,
            options:  $legacy->options,
            driver:   'pdo',
            profile:  $legacy->profile
        );
    }

    /**
     * ⚙️ **Establish a MySQL connection using PDO**
     *
     * 🔹 Priority:
     * 1. DSN mode (preferred, modern, profile-based)
     * 2. Host/port/database fallback (legacy environment configuration)
     *
     * @return void
     *
     * @throws ConnectionException When PDO fails to connect
     */
    public function connect(): void
    {
        $cfg = $this->resolveConfig(ConnectionTypeEnum::MYSQL);

        try {
            // 🧠 Build DSN if not explicitly provided
            $dsn = $cfg->dsn ??
                   sprintf(
                       'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                       $cfg->host,
                       $cfg->port,
                       $cfg->database
                   );

            // 🧩 Initialize PDO connection
            $this->connection = new PDO(
                $dsn,
                $cfg->user,
                $cfg->pass,
                $cfg->options + [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Enable exceptions
                ]
            );

            $this->connected = true;

        } catch (PDOException $e) {
            throw new ConnectionException("MySQL connection failed: " . $e->getMessage());
        }
    }

    /**
     * 🩺 **Perform a simple health check against the MySQL connection**
     *
     * Executes `SELECT 1` to confirm the connection is active and responsive.
     *
     * @return bool `true` if connection is alive, otherwise `false`
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
     * 🔄 **Reconnect to MySQL**
     *
     * Disconnects the current connection and attempts to establish a new one.
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
