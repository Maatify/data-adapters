<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-11 20:19
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Fallback;

use Maatify\DataAdapters\Fallback\FallbackQueue;
use Maatify\DataAdapters\Fallback\FallbackQueuePruner;
use PHPUnit\Framework\TestCase;

/**
 * 🧪 **Class FallbackQueuePrunerTest**
 *
 * 🎯 **Purpose:**
 * Ensures that {@see FallbackQueuePruner} correctly purges expired
 * entries from the fallback queue while retaining valid ones.
 *
 * ✅ **Verifications:**
 * - Expired entries (past TTL) are removed.
 * - Non-expired entries remain in the queue.
 */
final class FallbackQueuePrunerTest extends TestCase
{
    /**
     * 🧹 Reset queue state before each test.
     */
    protected function setUp(): void
    {
        FallbackQueue::clear();
    }

    /**
     * 🧩 **Test Expiration Logic**
     *
     * Verifies that expired items are pruned while valid ones persist.
     *
     * ✅ Scenario:
     * - Item 1 → TTL = 1 second → should expire.
     * - Item 2 → TTL = 60 seconds → should remain.
     *
     * @return void
     */
    public function testExpiredItemsAreRemoved(): void
    {
        // Arrange: Add two items with different TTLs
        FallbackQueue::enqueue('redis', 'SET', ['key' => 'x'], 1);
        FallbackQueue::enqueue('mysql', 'QUERY', ['sql' => 'SELECT 1'], 60);

        // Wait for the first item to expire
        sleep(2);

        // Act: Run the pruner
        (new FallbackQueuePruner())->run();

        // Assert: Only one valid entry should remain
        $this->assertSame(
            1,
            FallbackQueue::count(),
            '❌ Expired entries should be pruned, leaving only valid ones.'
        );
    }
}
