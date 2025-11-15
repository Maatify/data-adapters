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
 * 🧩 **MySqlConfigBuilder (Phase 13 — Final)**
 *
 * 🎯 Responsible for producing a **fully resolved MySQL configuration**
 * using the standardized priority chain:
 *
 * ### 🔥 Resolution Priority
 * 1️⃣ **Registry overrides** (`.maatify.registry.json`)
 * 2️⃣ **DSN mode** (PDO or Doctrine-style DSN)
 * 3️⃣ **Legacy environment variables** (`MYSQL_*_HOST`, etc.)
 *
 * ✔ Always returns a **complete** `ConnectionConfigDTO`, including:
 * - host
 * - port
 * - database
 * - user
 * - pass
 * - options
 * - driver
 * - profile
 *
 * 📌 Fully compatible with:
 * - Dynamic profiles
 * - Legacy-only setups
 * - DSN-only setups
 * - Unknown/empty profiles
 *
 * ---
 * ### Example
 * ```php
 * $builder = new MySqlConfigBuilder($envConfig);
 * $config  = $builder->build('main');
 *
 * echo $config->dsn;       // mysql://...
 * echo $config->host;      // 127.0.0.1
 * echo $config->database;  // mydb
 * ```
 * ---
 */
final readonly class MySqlConfigBuilder
{
    /**
     * @param EnvironmentConfig $config The unified environment configuration loader.
     */
    public function __construct(
        private EnvironmentConfig $config
    ) {}

    /**
     * 🧠 **Build a fully resolved MySQL profile configuration**
     *
     * Produces a complete and normalized configuration DTO by merging:
     *
     * - DSN values (if provided)
     * - Parsed DSN fields (host, port, user, pass, db)
     * - Legacy values (MYSQL_MAIN_HOST, etc.)
     * - Registry overrides (highest priority)
     *
     * @param string|null $profile The MySQL profile name (e.g., `main`, `logs`).
     *
     * @return ConnectionConfigDTO
     * @throws JsonException When options JSON is malformed.
     */
    public function build(?string $profile): ConnectionConfigDTO
    {
        // ⛔ Null profile → return empty DTO (used internally by Resolver)
        if ($profile === null) {
            return new ConnectionConfigDTO();
        }

        $upper = strtoupper($profile);

        // ---------------------------------------------------------
        // (1) DSN (PDO or Doctrine-style)
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

        // Parse legacy OPTIONS JSON
        $optionsJson = $this->config->get("MYSQL_{$upper}_OPTIONS");
        $legacy['options'] = !empty($optionsJson)
            ? (json_decode($optionsJson, true) ?: [])
            : [];

        // ---------------------------------------------------------
        // (3) Registry → DSN → Legacy (priority merge)
        // ---------------------------------------------------------
        $merged = $this->config->mergeWithRegistry(
            type    : 'mysql',
            profile : $profile,
            dsn     : array_merge(['dsn' => $dsn], $dsnData),
            legacy  : $legacy
        );

        // ---------------------------------------------------------
        // (4) Build final DTO (never returns partial state)
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
     * 🧠 **Parse MySQL DSN (PDO or Doctrine-style)**
     *
     * Supports:
     * - Doctrine DSN:
     *   `mysql://user:pass@host:3306/dbname`
     *
     * - PDO DSN:
     *   `mysql:host=127.0.0.1;port=3306;dbname=test`
     *
     * @param string $dsn Raw DSN string.
     *
     * @return array<string, string|null> Parsed DSN parts.
     */
    private function parseMysqlDsn(string $dsn): array
    {
        // -------------------------------------------------
        // Doctrine: mysql://user:pass@host:port/dbname
        // -------------------------------------------------
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

        // -------------------------------------------------
        // PDO: mysql:host=127;port=3306;dbname=test
        // -------------------------------------------------
        $clean = str_replace('mysql:', '', $dsn);
        $pairs = explode(';', $clean);

        $out = [];
        foreach ($pairs as $pair) {
            if (!str_contains($pair, '=')) {
                continue;
            }

            [$key, $value] = explode('=', $pair, 2);
            $out[strtolower(trim($key))] = trim($value);
        }

        return [
            'host'     => $out['host']   ?? null,
            'port'     => $out['port']   ?? null,
            'database' => $out['dbname'] ?? null,
        ];
    }
}
