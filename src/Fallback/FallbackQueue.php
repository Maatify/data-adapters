<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm)
 * @since       2025-11-11 18:21
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Fallback;

/**
 * ⚙️ **Class FallbackQueue**
 *
 * 🎯 **Purpose:**
 * Implements an in-memory, lightweight queue for temporarily storing failed adapter
 * operations during fallback activation. Supports time-to-live (TTL) for automatic
 * expiration and cleanup.
 *
 * 🧠 **Core Features:**
 * - Enqueues failed operations for retry or replay once primary adapters recover.
 * - TTL-based expiry system to auto-purge stale items.
 * - Designed for easy extension to persistent backends (e.g., SQLite, MySQL).
 * - Thread-safe and ideal for transient in-memory diagnostics or fallback operations.
 *
 * ✅ **Example Usage:**
 * ```php
 * FallbackQueue::enqueue('RedisAdapter', 'connect', ['callback' => $callback]);
 *
 * // Process queued operations
 * $pending = FallbackQueue::drain('RedisAdapter');
 * foreach ($pending as $item) {
 *     ($item['payload']['callback'])();
 * }
 *
 * // Purge expired items periodically
 * FallbackQueue::purgeExpired();
 * ```
 */
final class FallbackQueue
{
    /**
     * 🧩 In-memory queue storage for fallback operations.
     *
     * @var array<int, array{
     *     adapter: string,
     *     operation: string,
     *     payload: array,
     *     timestamp: int,
     *     ttl: int
     * }>
     */
    private static array $queue = [];

    /**
     * ➕ **Enqueue a Failed Operation**
     *
     * Adds a new operation to the queue, tagged with its adapter name,
     * operation identifier, and an optional TTL (default: 1 hour).
     *
     * @param string   $adapter   Adapter identifier (e.g., "RedisAdapter").
     * @param string   $operation The operation name that failed.
     * @param array    $payload   Arbitrary operation payload or metadata.
     * @param int|null $ttl       Optional time-to-live in seconds (defaults to `FALLBACK_QUEUE_TTL` or 3600).
     *
     * @return void
     */
    public static function enqueue(string $adapter, string $operation, array $payload, ?int $ttl = null): void
    {
        $ttlSeconds = $ttl ?? (int) ($_ENV['FALLBACK_QUEUE_TTL'] ?? 3600);

        self::$queue[] = [
            'adapter'   => $adapter,
            'operation' => $operation,
            'payload'   => $payload,
            'timestamp' => time(),
            'ttl'       => $ttlSeconds,
        ];
    }

    /**
     * 🔁 **Drain Items for a Specific Adapter**
     *
     * Retrieves and removes all queued items belonging to a given adapter.
     * Typically used when restoring or replaying failed operations.
     *
     * @param string $adapter Adapter identifier.
     *
     * @return array<int, array> List of drained queue items.
     */
    public static function drain(string $adapter): array
    {
        // 🧩 Filter and extract matching items
        $items = array_filter(self::$queue, fn($item) => $item['adapter'] === $adapter);

        // 🧹 Remove them from the main queue
        self::$queue = array_filter(self::$queue, fn($item) => $item['adapter'] !== $adapter);

        return array_values($items);
    }

    /**
     * 🧹 **Purge Expired Items**
     *
     * Removes all queue entries that have exceeded their TTL limit.
     * Can be executed periodically by a scheduler or background worker.
     *
     * @param int|null $ttlOverride Optional TTL override for testing or custom purge runs.
     *
     * @return void
     */
    public static function purgeExpired(?int $ttlOverride = null): void
    {
        $now = time();

        self::$queue = array_filter(self::$queue, static function (array $item) use ($now, $ttlOverride) {
            $ttl = $item['ttl'] ?? $ttlOverride ?? (int) ($_ENV['FALLBACK_QUEUE_TTL'] ?? 3600);
            return ($now - $item['timestamp']) < $ttl;
        });
    }

    /**
     * 🔢 **Get Total Queued Items**
     *
     * Returns the total number of queued fallback operations.
     *
     * @return int The current queue size.
     */
    public static function count(): int
    {
        return count(self::$queue);
    }

    /**
     * 🧾 **Clear All Queue Entries**
     *
     * Empties the queue completely — typically used in test environments.
     *
     * @return void
     */
    public static function clear(): void
    {
        self::$queue = [];
    }
}
