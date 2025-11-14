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

final readonly class MySqlConfigBuilder
{
    public function __construct(private EnvironmentConfig $config)
    {
    }

    public function build(string $profile): ConnectionConfigDTO
    {
        $key = sprintf('MYSQL_%s_DSN', strtoupper($profile));
        $dsn = $this->config->get($key);

        // ---------------------------------------------------------
        // 1) NO DSN → return EMPTY DTO (DO NOT override legacy env)
        // ---------------------------------------------------------
        if (empty($dsn)) {
            return new ConnectionConfigDTO();
        }

        // ---------------------------------------------------------
        // 2) DSN FOUND → parse and return DSN-only fields
        // ---------------------------------------------------------
        $parsed = $this->parseMysqlDsn($dsn);

        return new ConnectionConfigDTO(
            dsn     : $dsn,
            host    : $parsed['host'] ?? null,
            port    : (string)$parsed['port'] ?? null,
            database: $parsed['database'] ?? null,
            // user/pass/options come from BaseAdapter
            driver  : 'pdo',
            profile : $profile
        );
    }

    /**
     * Parse PDO or Doctrine style DSN.
     */
    private function parseMysqlDsn(string $dsn): array
    {
        // Doctrine: mysql://user:pass@host:port/db
        if (str_starts_with($dsn, 'mysql://')) {
            $url = parse_url($dsn);

            return [
                'host'     => $url['host'] ?? null,
                'port'     => (string)$url['port'] ?? null,
                'database' => ltrim($url['path'] ?? '', '/'),
            ];
        }

        // PDO DSN: mysql:host=1;dbname=2;port=3
        $clean = str_replace('mysql:', '', $dsn);
        $pairs = explode(';', $clean);

        $out = [];
        foreach ($pairs as $pair) {
            if (! str_contains($pair, '=')) {
                continue;
            }
            [$k, $v] = explode('=', $pair, 2);
            $out[trim($k)] = trim($v);
        }

        return [
            'host'     => $out['host'] ?? null,
            'port'     => (string)$out['port'] ?? null,
            'database' => $out['dbname'] ?? null,
        ];
    }
}
