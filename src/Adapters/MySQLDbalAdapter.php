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
use Throwable;

final class MySQLDbalAdapter extends BaseAdapter
{
    /**
     * Connect using Doctrine DBAL.
     */
    public function connect(): void
    {
        $cfg = $this->resolveConfig(ConnectionTypeEnum::MYSQL);

        try {
            // --------------------------------------
            // 1️⃣ DSN URL (Doctrine style)
            // --------------------------------------
            if (! empty($cfg->dsn) && str_starts_with($cfg->dsn, 'mysql://')) {
                $params = [
                    'url'     => $cfg->dsn,
                    'driver'  => 'pdo_mysql',
                    'charset' => 'utf8mb4',
                ];
            }

            // --------------------------------------
            // 2️⃣ PDO DSN (mysql:host=...)
            // --------------------------------------
            elseif (! empty($cfg->dsn)) {
                // Convert mysql:host=127;dbname=xx → body
                $body = str_replace('mysql:', '', $cfg->dsn);
                $body = str_replace(';', '&', $body);

                parse_str($body, $dsnParts);

                $params = [
                    'host'     => $dsnParts['host'] ?? $cfg->host,
                    'port'     => isset($dsnParts['port']) ? (int)$dsnParts['port'] : (int)$cfg->port,
                    'dbname'   => $dsnParts['dbname'] ?? $cfg->database,
                    'user'     => $cfg->user,
                    'password' => $cfg->pass,
                    'driver'   => 'pdo_mysql',
                    'charset'  => 'utf8mb4',
                ];
            }

            // --------------------------------------
            // 3️⃣ Legacy ENV fallback
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

            // --------------------------------------
            // Create DBAL connection
            // --------------------------------------
            $this->connection = DriverManager::getConnection(
                $params,
                new Configuration()
            );

            // Force actual connection test
            $this->connection->executeQuery('SELECT 1');

            $this->connected = true;
        } catch (Throwable $e) {
            throw new ConnectionException(
                "MySQL DBAL connection failed: " . $e->getMessage(),
                previous: $e
            );
        }
    }

    /**
     * Basic DBAL health check.
     */
    public function healthCheck(): bool
    {
        try {
            return (int)$this->connection?->executeQuery('SELECT 1')->fetchOne() === 1;
        } catch (Throwable) {
            return false;
        }
    }

    public function reconnect(): bool
    {
        $this->disconnect();
        $this->connect();

        return $this->connected;
    }
}
