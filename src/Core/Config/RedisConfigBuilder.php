<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-15 20:22
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Core\Config;

use JsonException;
use Maatify\Common\DTO\ConnectionConfigDTO;
use Maatify\DataAdapters\Core\EnvironmentConfig;

/**
 * 🧩 **RedisConfigBuilder (Phase 13 Unified)**
 *
 * 🎯 Produces a complete and normalized Redis configuration using:
 *
 * ### 🔥 Priority Chain (Highest → Lowest)
 * 1️⃣ **Registry overrides**
 * 2️⃣ **DSN mode** (`redis://password@host:port/db`)
 * 3️⃣ **Legacy environment variables** (`REDIS_MAIN_HOST`, `REDIS_MAIN_PORT`, etc.)
 *
 * Ensures consistent behavior across all adapter builders (MySQL / Mongo / Redis).
 *
 * ✔ Returns a **full** ConnectionConfigDTO
 * ✔ Supports redis:// parsing
 * ✔ Backward compatible with old legacy env setups
 * ✔ No username support (by design)
 *
 * ---
 * ### Example
 * ```php
 * $builder = new RedisConfigBuilder($env);
 * $cfg = $builder->build('cache');
 *
 * echo $cfg->host;      // 127.0.0.1
 * echo $cfg->database;  // 2
 * echo $cfg->dsn;       // redis://pass@127.0.0.1:6379/2
 * ```
 * ---
 */
final readonly class RedisConfigBuilder
{
    /**
     * @param EnvironmentConfig $config Unified environment loader.
     */
    public function __construct(
        private EnvironmentConfig $config
    ) {
    }

    /**
     * 🧠 **Build Redis configuration for a specific profile**
     *
     * The method:
     * - Extracts DSN (if present)
     * - Parses redis://
     * - Loads legacy values
     * - Applies registry overrides
     * - Returns a complete `ConnectionConfigDTO`
     *
     * @param string $profile Redis profile (e.g., `main`, `cache`, `queue`)
     *
     * @return ConnectionConfigDTO Fully resolved configuration
     * @throws JsonException When invalid JSON is encountered in registry or env
     */
    public function build(string $profile): ConnectionConfigDTO
    {
        $upper  = strtoupper($profile);
        $prefix = "REDIS_{$upper}_";

        // ---------------------------------------------------------
        // (1) Legacy fallback (lowest priority)
        // ---------------------------------------------------------
        $legacy = [
            'dsn'      => null,
            'host'     => $this->config->get($prefix . 'HOST'),
            'port'     => (string)$this->config->get($prefix . 'PORT'),
            'pass'     => $this->config->get($prefix . 'PASS'),
            'database' => $this->config->get($prefix . 'DB'),
            'options'  => [],
        ];

        // ---------------------------------------------------------
        // (2) DSN resolution (middle priority)
        // ---------------------------------------------------------
        $dsnKey = $prefix . 'DSN';
        $dsnVal = $this->config->get($dsnKey);

        $dsn = [];
        if (!empty($dsnVal)) {
            $parsed = $this->parseRedisDsn($dsnVal);

            $dsn = [
                'dsn'      => $dsnVal,
                'host'     => $parsed['host'] ?? null,
                'port'     => $parsed['port'] ?? null,
                'pass'     => $parsed['pass'] ?? null,
                'database' => $parsed['db']   ?? null,
            ];
        }

        // ---------------------------------------------------------
        // (3) Registry (highest priority)
        // ---------------------------------------------------------
        $merged = $this->config->mergeWithRegistry(
            type    : 'redis',
            profile : $profile,
            dsn     : $dsn,
            legacy  : $legacy
        );

        // ---------------------------------------------------------
        // (4) Build FULL DTO (matching MySQL/Mongo builder structure)
        // ---------------------------------------------------------
        return new ConnectionConfigDTO(
            dsn      : $merged['dsn']      ?? $dsnVal,
            host     : $merged['host']     ?? $legacy['host'],
            port     : isset($merged['port']) ? (string)$merged['port'] : $legacy['port'],
            user     : null, // Redis username intentionally unsupported
            pass     : $merged['pass']     ?? $legacy['pass'],
            database : $merged['database'] ?? $legacy['database'],
            options  : $merged['options']  ?? $legacy['options'],
            driver   : 'redis',
            profile  : $profile
        );
    }

    /**
     * 🧩 **Parse `redis://` DSN format**
     *
     * Supports formats like:
     * ```
     * redis://pass@127.0.0.1:6379/2
     * redis://password@host:port
     * redis://127.0.0.1:6379
     * ```
     *
     * Extracts:
     * - host
     * - port
     * - password
     * - database index
     *
     * @param string $dsn Redis DSN string
     *
     * @return array<string, string|null> Parsed values
     */
    private function parseRedisDsn(string $dsn): array
    {
        $url = parse_url($dsn);

        return [
            'host' => $url['host'] ?? null,
            'port' => $url['port'] ?? null,
            'pass' => $url['pass'] ?? null,
            'db'   => isset($url['path']) ? ltrim($url['path'], '/') : null,
        ];
    }
}
