<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-15 00:02
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Core\Config;

use Maatify\Common\DTO\ConnectionConfigDTO;
use Maatify\DataAdapters\Core\EnvironmentConfig;

final readonly class MongoConfigBuilder
{
    public function __construct(
        private EnvironmentConfig $config
    ) {}

    /**
     * Build full MongoDB configuration for a given profile.
     */
    public function build(string $profile): ConnectionConfigDTO
    {
        $upper = strtoupper($profile);

        // ---------------------------------------------------------
        // (1) DSN (primary source)
        // ---------------------------------------------------------
        $dsnKey = "MONGO_{$upper}_DSN";
        $dsn    = $this->config->get($dsnKey);
        $dsnData = $dsn ? $this->parseMongoDsn($dsn) : [];

        // ---------------------------------------------------------
        // (2) Legacy fallback values
        // ---------------------------------------------------------
        $legacy = [
            'dsn'      => null,
            'host'     => $this->config->get("MONGO_{$upper}_HOST"),
            'port'     => (string) $this->config->get("MONGO_{$upper}_PORT"),
            'user'     => $this->config->get("MONGO_{$upper}_USER"),
            'pass'     => $this->config->get("MONGO_{$upper}_PASS"),
            'database' => $this->config->get("MONGO_{$upper}_DB"),
        ];

        // Options JSON
        $optionsJson = $this->config->get("MONGO_{$upper}_OPTIONS");
        $legacy['options'] = $optionsJson
            ? (json_decode($optionsJson, true) ?: [])
            : [];

        // ---------------------------------------------------------
        // (3) Registry → DSN → Legacy merge
        // ---------------------------------------------------------
        $merged = $this->config->mergeWithRegistry(
            type    : 'mongo',
            profile : $profile,
            dsn     : array_merge(['dsn' => $dsn], $dsnData),
            legacy  : $legacy
        );

        // ---------------------------------------------------------
        // (4) Build full DTO exactly like MySQLBuilder rules
        // ---------------------------------------------------------
        return new ConnectionConfigDTO(
            dsn      : $merged['dsn']      ?? $dsn,
            host     : $merged['host']     ?? $legacy['host'],
            port     : isset($merged['port']) ? (string)$merged['port'] : $legacy['port'],
            user     : $merged['user']     ?? $legacy['user'],
            pass     : $merged['pass']     ?? $legacy['pass'],
            database : $merged['database'] ?? $legacy['database'],
            options  : $merged['options']  ?? $legacy['options'],
            driver   : 'mongo',
            profile  : $profile
        );
    }

    /**
     * Parse MongoDB DSN format.
     */
    private function parseMongoDsn(string $dsn): array
    {
        $url = parse_url($dsn);

        return [
            'host'     => $url['host'] ?? null,
            'port'     => isset($url['port']) ? (string)$url['port'] : null,
            'user'     => $url['user'] ?? null,
            'pass'     => $url['pass'] ?? null,
            'database' => isset($url['path']) ? ltrim($url['path'], '/') : null,
        ];
    }
}
