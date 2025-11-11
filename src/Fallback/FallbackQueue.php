<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-11 18:21
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Fallback;

/**
 * Simple in-memory queue (extendable to SQLite/MySQL).
 */
final class FallbackQueue
{
    private static array $queue = [];

    public static function enqueue(string $adapter, string $operation, array $payload): void
    {
        self::$queue[] = [
            'adapter'   => $adapter,
            'operation' => $operation,
            'payload'   => $payload,
            'timestamp' => time(),
        ];
    }

    public static function drain(string $adapter): array
    {
        $items = array_filter(self::$queue, fn($item) => $item['adapter'] === $adapter);
        self::$queue = array_filter(self::$queue, fn($item) => $item['adapter'] !== $adapter);

        return array_values($items);
    }

    public static function purgeExpired(int $ttl): void
    {
        $threshold = time() - $ttl;
        self::$queue = array_filter(self::$queue, fn($item) => $item['timestamp'] > $threshold);
    }

    public static function count(): int
    {
        return count(self::$queue);
    }

    /**
     * 🧾 Clear all queued items (used mainly for testing).
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$queue = [];
    }
}
