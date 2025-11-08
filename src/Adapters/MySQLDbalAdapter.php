<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-11-08
 * Time: 20:50
 * Project: data-adapters
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Adapters;

use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
use Maatify\DataAdapters\Core\BaseAdapter;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use Throwable;

final class MySQLDbalAdapter extends BaseAdapter
{
    public function connect(): void
    {
        try {
            $connectionParams = [
                'dbname'   => $this->requireEnv('MYSQL_DB'),
                'user'     => $this->requireEnv('MYSQL_USER'),
                'password' => $this->config->get('MYSQL_PASS'),
                'host'     => $this->requireEnv('MYSQL_HOST'),
                'port'     => $this->requireEnv('MYSQL_PORT'),
                'driver'   => 'pdo_mysql',
            ];
            $this->connection = DriverManager::getConnection($connectionParams, new Configuration());
            $this->connection->connect();
            $this->connected = $this->connection->isConnected();
        } catch (Throwable $e) {
            throw new ConnectionException("MySQL DBAL connection failed: " . $e->getMessage());
        }
    }

    public function healthCheck(): bool
    {
        try {
            return $this->connected && $this->connection->executeQuery('SELECT 1')->fetchOne() === 1;
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
