<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-11 19:27
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Fallback;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataAdapters\Fallback\FallbackQueue;
use Maatify\DataAdapters\Fallback\RecoveryWorker;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use ReflectionException;
use ReflectionMethod;

/**
 * 🧪 **Class RecoveryWorkerTest**
 *
 * 🎯 **Purpose:**
 * Verifies the internal replay behavior of {@see RecoveryWorker}
 * ensuring that queued fallback operations are replayed successfully
 * once the adapter becomes healthy again.
 *
 * 🧠 **Key Validations:**
 * - Ensures that replayed operations are properly drained from the queue.
 * - Confirms that replay logs are emitted through {@see LoggerInterface}.
 * - Uses reflection to safely invoke the private `replayQueue()` method.
 *
 * ✅ **Scenario Summary:**
 * 1️⃣ Create a mock adapter reporting healthy status.
 * 2️⃣ Enqueue a single mock operation.
 * 3️⃣ Trigger replay logic manually.
 * 4️⃣ Verify that queue is empty and logger was called.
 */
final class RecoveryWorkerTest extends TestCase
{
    /**
     * 🧩 **Test Replay Queue Behavior**
     *
     * Ensures that queued operations are successfully replayed and removed
     * from {@see FallbackQueue} once the recovery process executes.
     *
     * ✅ Steps:
     * - Mock a healthy adapter.
     * - Enqueue a simulated failed operation.
     * - Invoke replay queue logic through reflection.
     * - Assert the queue is fully cleared afterward.
     *
     * @return void
     * @throws ReflectionException
     */
    public function testReplayQueueReplaysSuccessfully(): void
    {
        // 🧠 Arrange: Create a healthy mock adapter
        $mockAdapter = $this->createMock(AdapterInterface::class);
        $mockAdapter->method('healthCheck')->willReturn(true);

        // 🧹 Ensure queue is empty before test
        FallbackQueue::clear();

        // 🧾 Enqueue a simulated failed operation
        FallbackQueue::enqueue($mockAdapter::class, 'mockOp', ['callback' => fn() => true]);

        // 🧰 Prepare mock logger (expecting at least one info log)
        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atLeastOnce())->method('info');

        // ⚙️ Instantiate RecoveryWorker with mock dependencies
        $worker = new RecoveryWorker($mockAdapter, $logger);

        // 🔍 Use reflection to invoke private replayQueue() method
        $method = new ReflectionMethod($worker, 'replayQueue');
        $method->setAccessible(true);
        $method->invoke($worker, $mockAdapter::class);

        // ✅ Assert that all operations have been replayed and cleared
        $this->assertSame(
            0,
            FallbackQueue::count(),
            '❌ Expected all queued operations to be replayed and cleared from queue.'
        );
    }
}
