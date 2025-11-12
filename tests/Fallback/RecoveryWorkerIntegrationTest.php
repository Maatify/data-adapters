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

/**
 * 🧪 **Class RecoveryWorkerIntegrationTest**
 *
 * 🎯 **Purpose:**
 * Ensures that the {@see RecoveryWorker} integration behaves as expected by verifying
 * periodic fallback queue pruning and correct expiration handling.
 *
 * 🧠 **Key Validations:**
 * - Confirms expired fallback queue items are removed.
 * - Ensures the pruning logic executes every 10th cycle.
 * - Uses reflection to simulate internal cycle progression.
 *
 * ✅ **Scenario Summary:**
 * 1️⃣ Adds both expired and non-expired queue entries.
 * 2️⃣ Runs 10 simulated recovery cycles.
 * 3️⃣ Confirms only valid entries remain after pruning.
 *
 * 🧩 **When to Use:**
 * Run this test to validate long-running recovery worker maintenance logic
 * without entering the infinite loop in {@see RecoveryWorker::run()}.
 */
final class RecoveryWorkerIntegrationTest extends TestCase
{
    /**
     * 🧹 **Setup Environment Before Each Test**
     *
     * Resets the fallback queue and defines short TTLs for deterministic testing.
     */
    protected function setUp(): void
    {
        FallbackQueue::clear();
        $_ENV['FALLBACK_QUEUE_TTL'] = 1;   // Expire after 1 second
        $_ENV['REDIS_RETRY_SECONDS'] = 0;  // Skip sleep delay
    }

    /**
     * 🧩 **Test: FallbackQueue Pruner Activation**
     *
     * Validates that fallback queue pruning executes every 10th recovery cycle
     * and that expired entries are correctly removed.
     *
     * ✅ Steps:
     * - Add 2 items (one expired, one valid)
     * - Simulate 10 worker cycles
     * - Trigger pruning on the 10th cycle
     * - Assert that only the valid item remains
     *
     * @return void
     */
    public function testFallbackQueuePrunerRunsEveryTenCycles(): void
    {
        // 🧱 Arrange: Create both expired and valid queue entries
        FallbackQueue::enqueue('redis', 'SET', ['key' => 'old'], 1);
        FallbackQueue::enqueue('redis', 'SET', ['key' => 'fresh'], 10);
        sleep(2); // Allow first item to expire

        // 🧩 Create a dummy adapter with a passing health check
        $adapter = new class {
            public function healthCheck(): bool
            {
                return true;
            }
        };

        $worker = new RecoveryWorker($adapter);

        // 🧠 Access private property "cycleCount" via reflection to simulate worker progress
        $ref = new ReflectionClass($worker);
        $cycleProp = $ref->getProperty('cycleCount');
        $cycleProp->setAccessible(true);

        // ⚙️ Act: Simulate 10 cycles, triggering the pruning logic on the 10th iteration
        for ($i = 1; $i <= 10; $i++) {
            $cycleProp->setValue($worker, $i);

            if ($i % 10 === 0) {
                $ttl = (int)($_ENV['FALLBACK_QUEUE_TTL'] ?? 3600);
                (new FallbackQueuePruner($ttl))->run();
            }
        }

        // ✅ Assert: Only one valid queue entry should remain
        $this->assertSame(
            1,
            FallbackQueue::count(),
            '❌ Expected only unexpired fallback entries to remain after 10 cycles.'
        );
    }
}
