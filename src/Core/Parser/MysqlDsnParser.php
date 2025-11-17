<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-17 10:30
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Core\Parser;

final class MysqlDsnParser
{
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

    public static function parse(string $dsn): array
    {
        // Remove query string (?charset=..)
        $clean = preg_replace('#/([A-Za-z0-9_\-]+)\?.*$#', '/$1', $dsn);
        // ---------------------------------------------
        // Doctrine: mysql://user:pass@host:port/db
        // SPECIAL FIX → Allow ANY password symbols safely
        // ---------------------------------------------
        if (str_starts_with($clean, 'mysql://')) {

            // Universal safe regex for ANY password symbols
            if (!preg_match(
                '#^mysql://(?P<user>[^:/]+):(?P<pass>.+)@(?P<host>[^:/]+):(?P<port>[0-9]+)/(?P<db>[A-Za-z0-9_\-]+)$#',
                $clean,
                $m
            )) {
                return [];
            }

            return [
                'host'     => $m['host'],
                'port'     => $m['port'],
                'user'     => $m['user'],
                'pass'     => $m['pass'],
                'database' => $m['db'],
            ];
        }

        // ---------------------------------------------
        // PDO DSN: mysql:host=...;port=...;dbname=...
        // ---------------------------------------------
        $clean = str_replace('mysql:', '', $clean);
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