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

use Maatify\Common\Enums\ConnectionTypeEnum;
use Maatify\DataAdapters\Core\BaseAdapter;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use PDO;
use PDOException;

final class MySQLAdapter extends BaseAdapter
{
    /**
     * Connect to MySQL (DSN-first, handled by BaseAdapter + Builder)
     */
    public function connect(): void
    {
        $cfg = $this->resolveConfig(ConnectionTypeEnum::MYSQL);

        try {

            // 1️⃣ DSN mode
            if (!empty($cfg->dsn)) {
                $dsn = $cfg->dsn;
            }

            // 2️⃣ Legacy fallback mode
            else {
                $dsn = sprintf(
                    'mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                    $cfg->host ?? '127.0.0.1',
                    $cfg->port ?? '3306',
                    $cfg->database ?? ''
                );
            }

            // 🔐 Build PDO options
            $options = $cfg->options + [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES   => false,
                ];

            // 🧩 Create PDO connection
            $this->connection = new PDO(
                $dsn,
                $cfg->user,
                $cfg->pass,
                $options
            );

            $this->connected = true;

        } catch (PDOException $e) {
            throw new ConnectionException(
                "MySQL connection failed: " . $e->getMessage()
            );
        }
    }

    /**
     * Check MySQL connection by running SELECT 1
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
     * Attempt reconnection
     */
    public function reconnect(): bool
    {
        $this->disconnect();
        $this->connect();
        return $this->connected;
    }
}

