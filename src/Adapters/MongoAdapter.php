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
    /**
     * 🧩 Connect using DSN-first DTO (provided by BaseAdapter + Builder)
     */
    public function connect(): void
    {
        $cfg = $this->resolveConfig(ConnectionTypeEnum::MONGO);

        try {
            // -------------------------------
            // 1️⃣ DSN MODE  (preferred)
            // -------------------------------
            if (! empty($cfg->dsn)) {
                $options = [];

                // Credentials explicitly included only if present
                if (! empty($cfg->user)) {
                    $options['username'] = $cfg->user;
                }
                if (! empty($cfg->pass)) {
                    $options['password'] = $cfg->pass;
                }

                $this->connection = new Client($cfg->dsn, $options);
                $this->connected = true;

                return;
            }

            // -------------------------------
            // 2️⃣ LEGACY MODE (fallback)
            // -------------------------------
            $host = $cfg->host ?? '127.0.0.1';
            $port = $cfg->port ?? '27017';
            $database = $cfg->database ?? 'admin';

            $dsn = sprintf(
                'mongodb://%s:%s/%s',
                $host,
                $port,
                $database
            );

            $options = [];
            if (! empty($cfg->user)) {
                $options['username'] = $cfg->user;
            }
            if (! empty($cfg->pass)) {
                $options['password'] = $cfg->pass;
            }

            $this->connection = new Client($dsn, $options);
            $this->connected = true;
        } catch (Throwable $e) {
            throw new ConnectionException(
                "Mongo connection failed: " . $e->getMessage()
            );
        }
    }

    /**
     * 🧪 Health check via: ping
     */
    public function healthCheck(): bool
    {
        try {
            $db = $this->connection->selectDatabase(
                $this->profile ?? 'admin'
            );

            $db->command(['ping' => 1]);

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * 🔄 Reconnect by rebuilding client
     */
    public function reconnect(): bool
    {
        $this->disconnect();
        $this->connect();

        return $this->connected;
    }
}
