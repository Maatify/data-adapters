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
    public function connect(): void
    {
        $cfg = $this->resolveConfig(ConnectionTypeEnum::MYSQL);

        try {
            // =====================================================
            // 1) DSN MODE
            // =====================================================
            if ($cfg->dsn) {

                // Case A: Doctrine-style URL (mysql://...)
                if (str_starts_with($cfg->dsn, 'mysql://')) {
                    $params = [
                        'url'    => $cfg->dsn,
                        'driver' => 'pdo_mysql',
                    ];

                } else {
                    // Case B: PDO-style DSN (official format we support)
                    // Convert "mysql:host=...;dbname=..." → array parts
                    $dsn = str_replace('mysql:', '', $cfg->dsn);
                    $dsn = str_replace(';', '&', $dsn);

                    parse_str($dsn, $pdo);

                    $params = [
                        'host'     => $pdo['host']     ?? $cfg->host,
                        'port'     => isset($pdo['port']) ? (int)$pdo['port'] : (int)$cfg->port,
                        'dbname'   => $pdo['dbname']   ?? $cfg->database,
                        'user'     => $cfg->user,
                        'password' => $cfg->pass,
                        'driver'   => 'pdo_mysql',
                        'charset'  => $pdo['charset']  ?? 'utf8mb4',
                    ];
                }

            } else {
                // =====================================================
                // 2) LEGACY MODE (HOST / PORT / USER / PASS)
                // =====================================================
                $params = [
                    'dbname'   => $cfg->database,
                    'user'     => $cfg->user,
                    'password' => $cfg->pass,
                    'host'     => $cfg->host,
                    'port'     => (int)$cfg->port,
                    'driver'   => 'pdo_mysql',
                    'charset'  => 'utf8mb4',
                ];
            }

            // Create DBAL connection
            $this->connection = DriverManager::getConnection($params, new Configuration());

            // Validate connection
            $this->connection->executeQuery('SELECT 1');
            $this->connected = $this->connection->isConnected();

        } catch (Throwable $e) {
            throw new ConnectionException('MySQL DBAL connection failed: ' . $e->getMessage(), 0, $e);
        }
    }

    public function healthCheck(): bool
    {
        try {
            return (int)$this->connection->executeQuery('SELECT 1')->fetchOne() === 1;
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