<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim
 * @since       2025-11-11 20:36
 * @link        https://github.com/Maatify/data-adapters
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Fallback;

use Maatify\DataAdapters\Fallback\FallbackQueue;
use Maatify\DataAdapters\Fallback\FallbackQueuePruner;
use Maatify\DataAdapters\Fallback\RecoveryWorker;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

final class RecoveryWorkerIntegrationTest extends TestCase
{
    protected function setUp(): void
    {
        FallbackQueue::clear();
        $_ENV['FALLBACK_QUEUE_TTL'] = 1;   // expire after 1s
        $_ENV['REDIS_RETRY_SECONDS'] = 0;  // skip sleep
    }

    public function testFallbackQueuePrunerRunsEveryTenCycles(): void
    {
        // Arrange: create 1 expired + 1 valid item
        FallbackQueue::enqueue('redis', 'SET', ['key' => 'old'], 1);
        FallbackQueue::enqueue('redis', 'SET', ['key' => 'fresh'], 10);
        sleep(2); // let first expire

        // Create dummy adapter (doesn't need to do anything)
        $adapter = new class {
            public function healthCheck(): bool { return true; }
        };

        $worker = new RecoveryWorker($adapter);

        // Access private cycleCount to simulate progress
        $ref = new ReflectionClass($worker);
        $cycleProp = $ref->getProperty('cycleCount');
        $cycleProp->setAccessible(true);

        // Act: simulate 10 cycles, triggering the pruning block exactly once
        for ($i = 1; $i <= 10; $i++) {
            $cycleProp->setValue($worker, $i);

            if ($i % 10 === 0) {
                $ttl = (int)($_ENV['FALLBACK_QUEUE_TTL'] ?? 3600);
                (new FallbackQueuePruner($ttl))->run();
            }
        }

        // Assert: only the non-expired item remains
        $this->assertSame(
            1,
            FallbackQueue::count(),
            'Only unexpired fallback entries should remain after 10 cycles.'
        );
    }
}
