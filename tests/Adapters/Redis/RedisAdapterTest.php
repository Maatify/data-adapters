<?php

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Adapters\Redis;

use Maatify\DataAdapters\Adapters\Redis\RedisAdapter;
use Maatify\DataAdapters\Contracts\Adapter\AdapterInterface;
use PHPUnit\Framework\TestCase;
use Redis;

class RedisAdapterTest extends TestCase
{
    public function testConstructorAcceptsRedisAndGetDriverReturnsIt(): void
    {
        if (!class_exists(Redis::class)) {
            $this->markTestSkipped('Redis extension not loaded.');
        }

        $redis = $this->createMock(Redis::class);
        $adapter = new RedisAdapter($redis);

        $this->assertInstanceOf(AdapterInterface::class, $adapter);
        $this->assertSame($redis, $adapter->getDriver());
    }
}
