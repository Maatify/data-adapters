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

final class MySQLAdapter extends BaseAdapter
{
    /**
     * Phase 11 override:
     * Merge BaseAdapter config (backward compatible)
     * + MySqlConfigBuilder (profile-based)
     */
    protected function resolveConfig(ConnectionTypeEnum $type): ConnectionConfigDTO
    {
        $legacy = parent::resolveConfig($type);

        $builder = new MySqlConfigBuilder($this->config);
        $mysql   = $builder->build($this->profile ?? 'main');

        // ONLY override legacy fields when builder returns non-null
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


    public function connect(): void
    {
        $cfg = $this->resolveConfig(ConnectionTypeEnum::MYSQL);

        try {
            $dsn = $cfg->dsn ??
                   sprintf('mysql:host=%s;port=%s;dbname=%s;charset=utf8mb4',
                       $cfg->host,
                       $cfg->port,
                       $cfg->database
                   );

            $this->connection = new PDO(
                $dsn,
                $cfg->user,
                $cfg->pass,
                $cfg->options + [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                ]
            );

            $this->connected = true;

        } catch (PDOException $e) {
            throw new ConnectionException("MySQL connection failed: " . $e->getMessage());
        }
    }

    public function healthCheck(): bool
    {
        try {
            return (bool)$this->connection?->query('SELECT 1')->fetchColumn();
        } catch (PDOException) {
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