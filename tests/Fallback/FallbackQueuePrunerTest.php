<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-11 20:19
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Fallback;

use PHPUnit\Framework\TestCase;
use Maatify\DataAdapters\Fallback\FallbackQueue;
use Maatify\DataAdapters\Fallback\FallbackQueuePruner;

final class FallbackQueuePrunerTest extends TestCase
{
    protected function setUp(): void
    {
        FallbackQueue::clear();
    }

    public function testExpiredItemsAreRemoved(): void
    {
        FallbackQueue::enqueue('redis', 'SET', ['key' => 'x'], 1);
        FallbackQueue::enqueue('mysql', 'QUERY', ['sql' => 'SELECT 1'], 60);

        sleep(2);
        (new FallbackQueuePruner( ))->run();

        $this->assertSame(1, FallbackQueue::count(), 'Expired entries should be pruned');
    }
}
