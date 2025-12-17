<?php

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Adapters;

use Maatify\DataAdapters\Adapters\Redis\RedisPredisAdapter;
use Maatify\DataAdapters\Contracts\Adapter\AdapterInterface;
use PHPUnit\Framework\TestCase;

class RedisPredisAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        if (! class_exists('Predis\Client')) {
            $this->markTestSkipped('Predis\Client class is not available.');
        }
    }

    public function test_it_implements_adapter_interface(): void
    {
        $driver = $this->createStub('Predis\Client');
        $adapter = new RedisPredisAdapter($driver);

        $this->assertInstanceOf(AdapterInterface::class, $adapter);
    }

    public function test_construction_and_get_driver(): void
    {
        $driver = $this->createStub('Predis\Client');
        $adapter = new RedisPredisAdapter($driver);

        $this->assertSame($driver, $adapter->getDriver());
    }

    public function test_get_driver_returns_correct_type(): void
    {
        $driver = $this->createStub('Predis\Client');
        $adapter = new RedisPredisAdapter($driver);

        $this->assertInstanceOf('Predis\Client', $adapter->getDriver());
    }

    public function test_repeated_calls_return_same_instance(): void
    {
        $driver = $this->createStub('Predis\Client');
        $adapter = new RedisPredisAdapter($driver);

        $first = $adapter->getDriver();
        $second = $adapter->getDriver();

        $this->assertSame($first, $second);
    }
}
