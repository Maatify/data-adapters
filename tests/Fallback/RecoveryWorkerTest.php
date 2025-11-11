<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
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

final class RecoveryWorkerTest extends TestCase
{
    public function testReplayQueueReplaysSuccessfully(): void
    {
        $mockAdapter = $this->createMock(AdapterInterface::class);
        $mockAdapter->method('healthCheck')->willReturn(true);

        FallbackQueue::clear();
        FallbackQueue::enqueue($mockAdapter::class, 'mockOp', ['callback' => fn() => true]);

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->atLeastOnce())->method('info');

        $worker = new RecoveryWorker($mockAdapter, $logger, 0);

        // ✅ invoke private replayQueue via Reflection
        $method = new \ReflectionMethod($worker, 'replayQueue');
        $method->setAccessible(true);
        $method->invoke($worker, $mockAdapter::class);

        $this->assertSame(0, FallbackQueue::count());
    }
}
