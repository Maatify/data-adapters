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

namespace Maatify\DataAdapters\Adapters;use Maatify\Common\DTO\ConnectionConfigDTO;
use Maatify\Common\Enums\ConnectionTypeEnum;
use Maatify\DataAdapters\Core\BaseAdapter;
use Maatify\DataAdapters\Core\Config\MongoConfigBuilder;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use MongoDB\Client;
use Throwable;

final class MongoAdapter extends BaseAdapter
{
    /**
     * Phase 12 override:
     * Combine BaseAdapter legacy config with profile DSN parsing.
     */
    protected function resolveConfig(ConnectionTypeEnum $type): ConnectionConfigDTO
    {
        $legacy  = parent::resolveConfig($type);

        $builder = new MongoConfigBuilder($this->config);
        $profile = $this->profile ?? 'main';
        $mongo   = $builder->build($profile);

        // Merge DSN-first, identical to MySQLAdapter pattern
        return new ConnectionConfigDTO(
            dsn     : $mongo->dsn      ?? $legacy->dsn,
            host    : $mongo->host     ?? $legacy->host,
            port    : $mongo->port     ?? $legacy->port,
            user    : $legacy->user,
            pass    : $legacy->pass,
            database: $mongo->database ?? $legacy->database,
            options : $legacy->options,
            driver  : 'mongo',
            profile : $profile
        );
    }

    public function connect(): void
    {
        $cfg = $this->resolveConfig(ConnectionTypeEnum::MONGO);

        try {
            // -----------------------------------
            // 1) DSN MODE
            // -----------------------------------
            if ($cfg->dsn) {
                $dsn = $cfg->dsn;
                $options = [];
            }

            // -----------------------------------
            // 2) LEGACY MODE (ENV-driven fallback)
            // -----------------------------------
            else {
                // Profile → Base → Safe Defaults
                $host = $cfg->host
                        ?? $this->config->get('MONGO_HOST')
                           ?? '127.0.0.1';

                $port = $cfg->port
                        ?? $this->config->get('MONGO_PORT')
                           ?? '27017';

                $database = $cfg->database
                            ?? $this->config->get('MONGO_DB')
                               ?? 'admin';

                $dsn = sprintf(
                    'mongodb://%s:%s/%s',
                    $host,
                    $port,
                    $database
                );

                // user/pass: profile → base
                $options = [
                    'username' => $cfg->user ?? $this->config->get('MONGO_USER'),
                    'password' => $cfg->pass ?? $this->config->get('MONGO_PASS'),
                ];
            }

            $this->connection = new Client($dsn, $options);
            $this->connected  = true;

        } catch (Throwable $e) {
            throw new ConnectionException("Mongo connection failed: " . $e->getMessage());
        }
    }

    public function healthCheck(): bool
    {
        try {
            $db = $this->connection->selectDatabase(
                $this->config->get('MONGO_DB', 'admin')
            );

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
