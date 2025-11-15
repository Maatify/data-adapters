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
 * 🧩 RedisConfigBuilder (Phase 13 Unified)
 *
 * ✔ Registry → DSN → Legacy fallback
 * ✔ DSN-first with redis:// parsing
 * ✔ Full configuration returned (host, port, pass, database)
 * ✔ Same behavior rules as MySQL & Mongo builders
 */
final readonly class RedisConfigBuilder
{
    public function __construct(
        private EnvironmentConfig $config
    ) {}

    /**
     * Build Redis configuration for a profile.
     *
     * @throws JsonException
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
        // (4) Build full DTO (same behavior as MySQL/Mongo)
        // ---------------------------------------------------------
        return new ConnectionConfigDTO(
            dsn      : $merged['dsn']      ?? $dsnVal,
            host     : $merged['host']     ?? $legacy['host'],
            port     : isset($merged['port']) ? (string)$merged['port'] : $legacy['port'],
            user     : null, // Redis does not support username in our architecture
            pass     : $merged['pass']     ?? $legacy['pass'],
            database : $merged['database'] ?? $legacy['database'],
            options  : $merged['options']  ?? $legacy['options'],
            driver   : 'redis',
            profile  : $profile
        );
    }

    /**
     * Parse redis://pass@host:port/db
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
