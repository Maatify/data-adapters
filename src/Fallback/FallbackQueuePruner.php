<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
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
 * Handles periodic cleanup of expired fallback queue items.
 */
final readonly class FallbackQueuePruner
{
    public function __construct(private ?int $ttlSeconds = null)
    {
    }

    public function run(): void
    {
        FallbackQueue::purgeExpired($this->ttlSeconds);
    }
}
