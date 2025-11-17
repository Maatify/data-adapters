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
        // ---------------------------------------------
        // 🧹 Normalize DSN (remove query params)
        // Support DSNs like:
        // mysql://root:root@127.0.0.1:3306/db?charset=utf8mb4&serverVersion=8.0
        // ---------------------------------------------
        $dsn = preg_replace('/\?.*$/', '', $dsn);

        // ---------------------------------------------
        // 🔵 Doctrine DSN format:
        // mysql://user:pass@host:port/db
        // ---------------------------------------------
        if (str_starts_with($dsn, 'mysql://')) {

            if (!preg_match(
                '#^mysql://(?P<user>[^:]+):(?P<pass>[^@]+)@(?P<host>[^:]+):(?P<port>[0-9]+)/(?P<db>[A-Za-z0-9_\-]+)$#',
                $dsn,
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
        // 🟢 PDO DSN format:
        // mysql:host=127.0.0.1;port=3306;dbname=test;charset=utf8mb4
        // ---------------------------------------------
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

    /*public static function parse(string $dsn): array
    {
        // -------------------------------------------------
        // Doctrine: mysql://user:pass@host:port/db
        // -------------------------------------------------
        if (str_starts_with($dsn, 'mysql://')) {

            // Regex parsing safe password with symbols
            if (!preg_match(
                '#^mysql://(?P<user>[^:]+):(?P<pass>[^@]+)@(?P<host>[^:]+):(?P<port>[0-9]+)/(?P<db>[A-Za-z0-9_\-]+)$#',
                $dsn,
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

        // -------------------------------------------------
        // PDO DSN
        // -------------------------------------------------
        $clean = str_replace('mysql:', '', $dsn);
        $pairs = explode(';', $clean);

        $out = [];
        foreach ($pairs as $pair) {
            if (!str_contains($pair, '=')) continue;
            [$key, $value] = explode('=', $pair, 2);
            $out[strtolower(trim($key))] = trim($value);
        }

        return [
            'host'     => $out['host']   ?? null,
            'port'     => $out['port']   ?? null,
            'database' => $out['dbname'] ?? null,
        ];
    }*/
}