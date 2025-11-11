<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm)
 * @since       2025-11-11 18:21
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Fallback;

/**
 * Simple in-memory queue (extendable to SQLite/MySQL) with TTL support.
 */
final class FallbackQueue
{
    private static array $queue = [];

    /**
     * Enqueue failed operation with TTL metadata.
     */
    public static function enqueue(string $adapter, string $operation, array $payload, ?int $ttl = null): void
    {
        $ttlSeconds = $ttl ?? (int)($_ENV['FALLBACK_QUEUE_TTL'] ?? 3600);

        self::$queue[] = [
            'adapter'   => $adapter,
            'operation' => $operation,
            'payload'   => $payload,
            'timestamp' => time(),
            'ttl'       => $ttlSeconds,
        ];
    }



    /**
     * Drain all queued items for the given adapter.
     */
    public static function drain(string $adapter): array
    {
        $items = array_filter(self::$queue, fn($item) => $item['adapter'] === $adapter);
        self::$queue = array_filter(self::$queue, fn($item) => $item['adapter'] !== $adapter);

        return array_values($items);
    }

    /**
     * Purge expired items based on TTL.
     */

    public static function purgeExpired(?int $ttlOverride = null): void
    {
        $now = time();
        self::$queue = array_filter(self::$queue, static function (array $item) use ($now, $ttlOverride) {
            $ttl = $item['ttl'] ?? $ttlOverride ?? (int)($_ENV['FALLBACK_QUEUE_TTL'] ?? 3600);
            return ($now - $item['timestamp']) < $ttl;
        });
    }

    /**
     * Return total items currently in queue.
     */
    public static function count(): int
    {
        return count(self::$queue);
    }

    /**
     * 🧾 Clear all queued items (used mainly for testing).
     */
    public static function clear(): void
    {
        self::$queue = [];
    }
}
