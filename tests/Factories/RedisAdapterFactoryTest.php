<?php

declare(strict_types=1);

namespace {
    if (!class_exists('Redis')) {
        class Redis {}
    }
}

namespace Maatify\DataAdapters\Tests\Factories {

    use Maatify\DataAdapters\Adapters\Redis\RedisAdapter;
    use Maatify\DataAdapters\Adapters\Redis\RedisPredisAdapter;
    use Maatify\DataAdapters\Exceptions\AdapterCreationException;
    use Maatify\DataAdapters\Factories\RedisAdapterFactory;
    use PHPUnit\Framework\TestCase;
    use Predis\Client;
    use Redis;
    use RuntimeException;

    class RedisAdapterFactoryTest extends TestCase
    {
        public function testFromRedisCreatesAdapter(): void
        {
            $redis = $this->createMock(Redis::class);
            $adapter = RedisAdapterFactory::fromRedis($redis);

            $this->assertInstanceOf(RedisAdapter::class, $adapter);
            $this->assertSame($redis, $adapter->getDriver());
        }

        public function testFromPredisCreatesAdapter(): void
        {
            $client = $this->createMock(Client::class);
            $adapter = RedisAdapterFactory::fromPredis($client);

            $this->assertInstanceOf(RedisPredisAdapter::class, $adapter);
            $this->assertSame($client, $adapter->getDriver());
        }

        public function testFromRedisFactoryCreatesAdapterWhenCallableSucceeds(): void
        {
            $redis = $this->createMock(Redis::class);
            $factory = fn () => $redis;

            $adapter = RedisAdapterFactory::fromRedisFactory($factory);

            $this->assertInstanceOf(RedisAdapter::class, $adapter);
            $this->assertSame($redis, $adapter->getDriver());
        }

        public function testFromRedisFactoryThrowsAdapterCreationExceptionWhenCallableFails(): void
        {
            $originalException = new RuntimeException('fail');
            $factory = fn () => throw $originalException;

            try {
                RedisAdapterFactory::fromRedisFactory($factory);
                $this->fail('AdapterCreationException was not thrown');
            } catch (AdapterCreationException $e) {
                $this->assertSame('Failed to create ext-redis adapter', $e->getMessage());
                $this->assertSame($originalException, $e->getPrevious());
            }
        }
    }
}
