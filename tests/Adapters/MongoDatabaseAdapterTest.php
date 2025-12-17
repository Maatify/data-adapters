<?php

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Adapters;

use Maatify\DataAdapters\Adapters\Mongo\MongoDatabaseAdapter;
use Maatify\DataAdapters\Contracts\Adapter\AdapterInterface;
use PHPUnit\Framework\TestCase;

class MongoDatabaseAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        if (! class_exists('MongoDB\Database')) {
            $this->markTestSkipped('MongoDB\Database class is not available.');
        }
    }

    public function test_it_implements_adapter_interface(): void
    {
        $driver = $this->createStub('MongoDB\Database');
        $adapter = new MongoDatabaseAdapter($driver);

        $this->assertInstanceOf(AdapterInterface::class, $adapter);
    }

    public function test_construction_and_get_driver(): void
    {
        $driver = $this->createStub('MongoDB\Database');
        $adapter = new MongoDatabaseAdapter($driver);

        $this->assertSame($driver, $adapter->getDriver());
    }

    public function test_get_driver_returns_correct_type(): void
    {
        $driver = $this->createStub('MongoDB\Database');
        $adapter = new MongoDatabaseAdapter($driver);

        $this->assertInstanceOf('MongoDB\Database', $adapter->getDriver());
    }

    public function test_repeated_calls_return_same_instance(): void
    {
        $driver = $this->createStub('MongoDB\Database');
        $adapter = new MongoDatabaseAdapter($driver);

        $first = $adapter->getDriver();
        $second = $adapter->getDriver();

        $this->assertSame($first, $second);
    }
}
