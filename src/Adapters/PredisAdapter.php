<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm)
 * @since       2025-11-08 20:44
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Adapters;

use Maatify\Common\Enums\ConnectionTypeEnum;
use Maatify\DataAdapters\Core\BaseAdapter;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use Predis\Client;
use Throwable;

final class PredisAdapter extends BaseAdapter
{
    public function connect(): void
    {
        $cfg = $this->resolveConfig(ConnectionTypeEnum::REDIS);

        try {
            if ($cfg->dsn) {
                $params = $cfg->dsn; // Predis accepts DSN URI
            } else {
                $params = [
                    'scheme'   => 'tcp',
                    'host'     => $cfg->host,
                    'port'     => (int)$cfg->port,
                    'password' => $cfg->pass,
                ];
            }

            $this->connection = new Client($params);
            $this->connection->connect();
            $this->connected = true;

        } catch (Throwable $e) {
            throw new ConnectionException("Predis connection failed: " . $e->getMessage());
        }
    }

    public function healthCheck(): bool
    {
        try {
            return (bool)$this->connection?->ping();
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
