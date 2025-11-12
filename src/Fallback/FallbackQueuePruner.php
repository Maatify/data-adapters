<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-11 20:16
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Fallback;

/**
 * ⚙️ **Class FallbackQueuePruner**
 *
 * 🧩 **Purpose:**
 * Performs periodic cleanup of expired fallback queue items
 * based on their configured TTL (Time-To-Live). Intended to
 * be triggered by cron jobs, background workers, or scheduler services.
 *
 * ✅ **Features:**
 * - Cleans stale fallback queue entries exceeding TTL.
 * - Supports TTL override for manual cleanup runs.
 * - Lightweight and fully static — no dependencies required.
 *
 * ✅ **Example Usage:**
 * ```php
 * // Run automatic cleanup (using default TTL from .env)
 * (new FallbackQueuePruner())->run();
 *
 * // Run cleanup with custom TTL (e.g., 10 minutes)
 * (new FallbackQueuePruner(600))->run();
 * ```
 */
final readonly class FallbackQueuePruner
{
    /**
     * @param int|null $ttlSeconds Optional TTL override (in seconds).
     */
    public function __construct(private ?int $ttlSeconds = null)
    {
    }

    /**
     * 🧹 **Run Queue Pruning**
     *
     * Invokes the static cleanup process to remove all expired
     * fallback queue items. Can be safely called multiple times.
     *
     * @return void
     */
    public function run(): void
    {
        FallbackQueue::purgeExpired($this->ttlSeconds);
    }
}
