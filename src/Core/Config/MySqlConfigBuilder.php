<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-14 15:37
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Core\Config;

use Maatify\Common\DTO\ConnectionConfigDTO;
use Maatify\DataAdapters\Core\EnvironmentConfig;

/**
 * 🧩 **Class MySqlConfigBuilder**
 *
 * 🎯 Responsible for building DSN-based MySQL configuration for a specific
 * connection profile (e.g., `main`, `logs`, `reporting`).
 *
 * ✔ Supports DSN formats:
 * - **Doctrine URL DSN:**
 *   `mysql://user:pass@127.0.0.1:3306/mydb`
 * - **PDO DSN:**
 *   `mysql:host=127.0.0.1;port=3306;dbname=mydb`
 *
 * ✔ DSN-first override logic:
 * If DSN exists → **return DSN-specific config only**
 * If DSN missing → return **empty DTO** so BaseAdapter falls back to legacy ENV.
 *
 * @example Usage:
 * ```php
 * $builder = new MySqlConfigBuilder($env);
 * $config  = $builder->build('main');
 * ```
 */
final readonly class MySqlConfigBuilder
{
    /**
     * @param EnvironmentConfig $config The environment configuration loader
     */
    public function __construct(private EnvironmentConfig $config)
    {
    }

    /**
     * 🎯 **Build DSN-based configuration for the given MySQL profile**
     *
     * Looks for:
     * ```
     * MYSQL_{PROFILE}_DSN
     * ```
     *
     * Example:
     * ```
     * MYSQL_MAIN_DSN="mysql://user:pass@127.0.0.1:3306/dbname"
     * ```
     *
     * ✔ If DSN is missing → returns *blank* ConnectionConfigDTO
     * ✔ If DSN exists → returns DSN-specific fields only (host, port, database)
     *
     * @param string $profile The profile name ("main", "logs", "reporting", etc.)
     *
     * @return ConnectionConfigDTO Parsed connection data for the profile
     */
    public function build(string $profile): ConnectionConfigDTO
    {
        $key = sprintf('MYSQL_%s_DSN', strtoupper($profile));
        $dsn = $this->config->get($key);

        // ---------------------------------------------------------
        // 1) ❌ NO DSN → DO NOT override BaseAdapter legacy logic
        // ---------------------------------------------------------
        if (empty($dsn)) {
            return new ConnectionConfigDTO();
        }

        // ---------------------------------------------------------
        // 2) ✅ DSN FOUND → parse DSN fields only
        // ---------------------------------------------------------
        $parsed = $this->parseMysqlDsn($dsn);

        return new ConnectionConfigDTO(
            dsn     : $dsn,
            host    : $parsed['host'] ?? null,
            port    : (string)($parsed['port'] ?? null),
            database: $parsed['database'] ?? null,
            driver  : 'pdo',      // user/pass/options come from BaseAdapter
            profile : $profile
        );
    }

    /**
     * 🧠 **Parse a MySQL DSN**
     *
     * Supports two formats:
     * 1. `mysql://user:pass@host:port/dbname` (Doctrine)
     * 2. `mysql:host=...;dbname=...;port=...` (PDO)
     *
     * @param string $dsn Raw DSN string from environment
     *
     * @return array Parsed fields: `host`, `port`, `database`
     */
    private function parseMysqlDsn(string $dsn): array
    {
        // ---------------------------------------------------------
        // 📌 Doctrine style DSN: mysql://user:pass@host:port/db
        // ---------------------------------------------------------
        if (str_starts_with($dsn, 'mysql://')) {
            $url = parse_url($dsn);

            return [
                'host'     => $url['host'] ?? null,
                'port'     => (string)($url['port'] ?? null),
                'database' => ltrim($url['path'] ?? '', '/'),
            ];
        }

        // ---------------------------------------------------------
        // 📌 PDO DSN style: mysql:host=1;dbname=2;port=3
        // ---------------------------------------------------------
        $clean = str_replace('mysql:', '', $dsn);
        $pairs = explode(';', $clean);

        $out = [];
        foreach ($pairs as $pair) {
            // Skip incomplete tokens
            if (! str_contains($pair, '=')) {
                continue;
            }

            [$k, $v] = explode('=', $pair, 2);
            $out[trim($k)] = trim($v);
        }

        return [
            'host'     => $out['host'] ?? null,
            'port'     => (string)($out['port'] ?? null),
            'database' => $out['dbname'] ?? null,
        ];
    }
}
