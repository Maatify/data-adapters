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

use Maatify\Common\DTO\ConnectionConfigDTO;
use Maatify\Common\Enums\ConnectionTypeEnum;
use Maatify\DataAdapters\Core\BaseAdapter;
use Maatify\DataAdapters\Core\Config\MongoConfigBuilder;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use MongoDB\Client;
use Throwable;

/**
 * 🧩 **Class MongoAdapter**
 *
 * 🎯 Provides a MongoDB adapter implementation based on the unified
 * Maatify Data-Adapters architecture.
 * It supports both **DSN-based configuration** and **legacy ENV-based fallback**,
 * with profile routing identical to MySQL/Redis adapters.
 *
 * ⚙️ The adapter:
 * - Resolves configuration using `MongoConfigBuilder`
 * - Connects using DSN-first priority (Phase 12)
 * - Supports legacy host/port/user/pass fallback
 * - Provides `healthCheck()` and `reconnect()` utilities
 *
 * @example
 * ```php
 * $mongo = new MongoAdapter($config, 'main');
 * $mongo->connect();
 * if ($mongo->healthCheck()) {
 *     echo "Mongo is healthy!";
 * }
 * ```
 */
final class MongoAdapter extends BaseAdapter
{
    /**
     * 🎯 **Resolve configuration for MongoDB**
     *
     * 🧩 Overrides BaseAdapter to merge legacy config with DSN-profile parsing.
     * DSN always takes priority (Phase 12 behavior).
     *
     * @param ConnectionTypeEnum $type  Connection type (always MONGO for this adapter)
     *
     * @return ConnectionConfigDTO Fully resolved connection config (DSN-first)
     *
     * @throws ConnectionException When configuration is invalid
     */
    protected function resolveConfig(ConnectionTypeEnum $type): ConnectionConfigDTO
    {
        $legacy  = parent::resolveConfig($type);

        // 🧠 Build config from DSN-aware MongoConfigBuilder
        $builder = new MongoConfigBuilder($this->config);
        $profile = $this->profile ?? 'main';
        $mongo   = $builder->build($profile);

        // 🔹 Merge DSN-first, following MySQLAdapter pattern
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

    /**
     * ⚙️ **Establish MongoDB connection**
     *
     * Supports:
     * - ✅ DSN mode
     * - 🔄 Legacy environment-based fallback
     *
     * @return void
     *
     * @throws ConnectionException When connection fails
     */
    public function connect(): void
    {
        $cfg = $this->resolveConfig(ConnectionTypeEnum::MONGO);

        try {
            // -----------------------------------
            // 1) 🧠 DSN MODE (highest priority)
            // -----------------------------------
            if ($cfg->dsn) {
                $dsn = $cfg->dsn;
                /**
                * DSN mode: credentials inside DSN OR ignored.
                * Start with empty options.
                */
                $options = [];
            }

            // -----------------------------------
            // 2) 🧩 LEGACY MODE (ENV-driven)
            // -----------------------------------
            else {
                // 🔹 Profile → Base → Safe Defaults
                $host = $cfg->host
                        ?? $this->config->get('MONGO_HOST')
                           ?? '127.0.0.1';

                $port = $cfg->port
                        ?? $this->config->get('MONGO_PORT')
                           ?? '27017';

                $database = $cfg->database
                            ?? $this->config->get('MONGO_DB')
                               ?? 'admin';

                // 🔨 Build legacy DSN
                $dsn = sprintf(
                    'mongodb://%s:%s/%s',
                    $host,
                    $port,
                    $database
                );

                // 🔐 Credentials: profile → base
                $username = $cfg->user ?? $this->config->get('MONGO_USER');
                $password = $cfg->pass ?? $this->config->get('MONGO_PASS');

                // 🛠 Build options WITHOUT null keys
                $options = [];
                if (!empty($username)) {
                    $options['username'] = $username;
                }
                if (!empty($password)) {
                    $options['password'] = $password;
                }
            }

            // 🧩 Create MongoDB client
            /**
             * 🔒 FIX: Pass only valid string credentials.
             * MongoDB driver throws if username/password = null.
             */
            $this->connection = new Client($dsn, $options);
            $this->connected  = true;

        } catch (Throwable $e) {
            throw new ConnectionException("Mongo connection failed: " . $e->getMessage());
        }
    }

    /**
     * 🩺 **Check MongoDB connection health**
     *
     * Performs a simple `ping` command on the selected database.
     *
     * @return bool `true` if database responds to ping, `false` otherwise
     */
    public function healthCheck(): bool
    {
        try {
            $db = $this->connection->selectDatabase(
                $this->config->get('MONGO_DB', 'admin')
            );

            // 🔍 Ping the server to validate connection
            $db->command(['ping' => 1]);
            return true;

        } catch (Throwable) {
            return false;
        }
    }

    /**
     * 🔄 **Reconnect to MongoDB**
     *
     * Disconnects the current connection and attempts to create a new one.
     *
     * @return bool `true` if reconnection succeeds, otherwise `false`
     */
    public function reconnect(): bool
    {
        $this->disconnect();
        $this->connect();
        return $this->connected;
    }
}
