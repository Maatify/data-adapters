<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim
 * @since       2025-11-15
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Core\Config;

use JsonException;
use Maatify\Common\DTO\ConnectionConfigDTO;
use Maatify\DataAdapters\Core\EnvironmentConfig;

/**
 * 🧩 MySqlConfigBuilder (Phase 13 — Final)
 *
 * ✔ Always returns a FULL configuration:
 *    host, port, database, user, pass, options
 *
 * ✔ DSN-first (parse + override)
 * ✔ Legacy fallback
 * ✔ Registry (highest priority)
 *
 * Ensures all tests pass:
 * - Dynamic profiles
 * - Legacy mode
 * - Unknown profiles
 * - DSN parsing (PDO + Doctrine)
 */
final readonly class MySqlConfigBuilder
{
    public function __construct(
        private EnvironmentConfig $config
    ) {}

    /**
     * Build a fully resolved MySQL config for a profile.
     *
     * @throws JsonException
     */
    public function build(?string $profile): ConnectionConfigDTO
    {
        if ($profile === null) {
            return new ConnectionConfigDTO();
        }

        $upper = strtoupper($profile);

        // ---------------------------------------------------------
        // (1) Read DSN
        // ---------------------------------------------------------
        $dsnKey  = "MYSQL_{$upper}_DSN";
        $dsn     = $this->config->get($dsnKey);
        $dsnData = $dsn ? $this->parseMysqlDsn($dsn) : [];

        // ---------------------------------------------------------
        // (2) Legacy ENV fallback values
        // ---------------------------------------------------------
        $legacy = [
            'dsn'      => null,
            'host'     => $this->config->get("MYSQL_{$upper}_HOST"),
            'port'     => $this->config->get("MYSQL_{$upper}_PORT"),
            'user'     => $this->config->get("MYSQL_{$upper}_USER"),
            'pass'     => $this->config->get("MYSQL_{$upper}_PASS"),
            'database' => $this->config->get("MYSQL_{$upper}_DB"),
        ];

        // Legacy options JSON
        $optionsJson = $this->config->get("MYSQL_{$upper}_OPTIONS");
        $legacy['options'] = !empty($optionsJson)
            ? (json_decode($optionsJson, true) ?: [])
            : [];

        // ---------------------------------------------------------
        // (3) Registry → DSN → Legacy
        // ---------------------------------------------------------
        $merged = $this->config->mergeWithRegistry(
            type    : 'mysql',
            profile : $profile,
            dsn     : array_merge(['dsn' => $dsn], $dsnData),
            legacy  : $legacy
        );

        // ---------------------------------------------------------
        // (4) Build final full DTO
        // ---------------------------------------------------------
        return new ConnectionConfigDTO(
            dsn      : $merged['dsn']      ?? $dsn,
            host     : $merged['host']     ?? $legacy['host'],
            port     : isset($merged['port']) ? (string)$merged['port'] : $legacy['port'],
            user     : $merged['user']     ?? $legacy['user'],
            pass     : $merged['pass']     ?? $legacy['pass'],
            database : $merged['database'] ?? $legacy['database'],
            options  : $merged['options']  ?? $legacy['options'],
            driver   : $merged['driver']   ?? 'pdo',
            profile  : $profile
        );
    }

    /**
     * Parse MySQL DSN (PDO + Doctrine)
     */
    private function parseMysqlDsn(string $dsn): array
    {
        // Doctrine: mysql://user:pass@host:port/db
        if (str_starts_with($dsn, 'mysql://')) {
            $url = parse_url($dsn);

            return [
                'host'     => $url['host'] ?? null,
                'port'     => $url['port'] ?? null,
                'user'     => $url['user'] ?? null,
                'pass'     => $url['pass'] ?? null,
                'database' => isset($url['path']) ? ltrim($url['path'], '/') : null,
            ];
        }

        // PDO: mysql:host=127;port=3306;dbname=test
        $clean = str_replace('mysql:', '', $dsn);
        $pairs = explode(';', $clean);

        $out = [];
        foreach ($pairs as $pair) {
            if (!str_contains($pair, '=')) continue;
            [$k, $v] = explode('=', $pair, 2);
            $out[strtolower(trim($k))] = trim($v);
        }

        return [
            'host'     => $out['host']   ?? null,
            'port'     => $out['port']   ?? null,
            'database' => $out['dbname'] ?? null,
        ];
    }
}
