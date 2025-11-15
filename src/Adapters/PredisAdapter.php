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
    /**
     * Connect using Predis client with DSN → Legacy fallback.
     */
    public function connect(): void
    {
        $cfg = $this->resolveConfig(ConnectionTypeEnum::REDIS);

        try {
            // -----------------------------------------
            // 1️⃣ DSN Mode (preferred modern flow)
            // -----------------------------------------
            if (!empty($cfg->dsn)) {
                $params = $cfg->dsn;
            }

            // -----------------------------------------
            // 2️⃣ Legacy fallback
            // -----------------------------------------
            else {
                $params = [
                    'scheme'   => 'tcp',
                    'host'     => $cfg->host ?? '127.0.0.1',
                    'port'     => (int)($cfg->port ?? 6379),
                    'password' => null, // We authenticate manually
                    'database' => $cfg->database ? (int)$cfg->database : null,
                ];
            }

            // Create client instance
            $client = new Client($params);

            // -----------------------------------------
            // 🔐 AUTH if password exists
            // -----------------------------------------
            if (!empty($cfg->pass)) {
                try {
                    $client->auth($cfg->pass);
                } catch (Throwable $e) {
                    throw new ConnectionException("Predis authentication failed: " . $e->getMessage());
                }
            }

            // -----------------------------------------
            // 🏁 Force connection validation
            // -----------------------------------------
            try {
                $client->ping();
            } catch (Throwable $e) {
                throw new ConnectionException("Predis connection failed: " . $e->getMessage());
            }

            $this->connection = $client;
            $this->connected  = true;

        } catch (Throwable $e) {
            throw new ConnectionException("Predis connection failed: " . $e->getMessage());
        }
    }

    /**
     * Basic health check.
     */
    public function healthCheck(): bool
    {
        try {
            return (bool)$this->connection?->ping();
        } catch (Throwable) {
            return false;
        }
    }

    /**
     * Reconnect.
     */
    public function reconnect(): bool
    {
        $this->disconnect();
        $this->connect();
        return $this->connected;
    }
}
