<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm)
 * @since       2025-11-08 20:47
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Adapters;

use Maatify\Common\Enums\ConnectionTypeEnum;
use Maatify\DataAdapters\Core\BaseAdapter;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use MongoDB\Client;
use Throwable;

final class MongoAdapter extends BaseAdapter
{
    public function connect(): void
    {
        $cfg = $this->resolveConfig(ConnectionTypeEnum::MONGO);

        try {
            if ($cfg->dsn) {
                $dsn = $cfg->dsn;
                $options = [];
            } else {
                // legacy
                $dsn = sprintf(
                    'mongodb://%s:%s/%s',
                    $cfg->host,
                    $cfg->port,
                    $cfg->database ?? 'admin'
                );

                $options = [
                    'username' => $cfg->user,
                    'password' => $cfg->pass,
                ];
            }

            $this->connection = new Client($dsn, $options);
            $this->connected = true;

        } catch (Throwable $e) {
            throw new ConnectionException("Mongo connection failed: " . $e->getMessage());
        }
    }

    public function healthCheck(): bool
    {
        try {
            $db = $this->connection->selectDatabase($this->config->get('MONGO_DB', 'admin'));
            $db->command(['ping' => 1]);
            return true;
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