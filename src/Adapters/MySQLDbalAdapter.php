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

final class MySQLDbalAdapter extends BaseAdapter
{
    protected function resolveConfig(ConnectionTypeEnum $type): ConnectionConfigDTO
    {
        $legacy = parent::resolveConfig($type);

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

    public function connect(): void
    {
        $cfg = $this->resolveConfig(ConnectionTypeEnum::MYSQL);

        try {
            // Doctrine URL DSN
            if ($cfg->dsn && str_starts_with($cfg->dsn, 'mysql://')) {
                $params = [
                    'url'    => $cfg->dsn,
                    'driver' => 'pdo_mysql',
                ];
            }
            // PDO-style DSN
            elseif ($cfg->dsn) {
                $dnsBody = str_replace('mysql:', '', $cfg->dsn);
                $dnsBody = str_replace(';', '&', $dnsBody);

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
            // legacy
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

            $this->connection = DriverManager::getConnection($params, new Configuration());
            $this->connection->executeQuery('SELECT 1');

            $this->connected = $this->connection->isConnected();

        } catch (Throwable $e) {
            throw new ConnectionException('MySQL DBAL connection failed: ' . $e->getMessage(), 0, $e);
        }
    }

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