<?php

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Adapters;

use Maatify\DataAdapters\Adapters\MySQL\MySQLDBALAdapter;
use Maatify\DataAdapters\Contracts\Adapter\AdapterInterface;
use PHPUnit\Framework\TestCase;

class MySQLDBALAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        if (! class_exists('Doctrine\DBAL\Connection')) {
            $this->markTestSkipped('Doctrine DBAL Connection class is not available.');
        }
    }

    public function test_it_implements_adapter_interface(): void
    {
        $driver = $this->createStub('Doctrine\DBAL\Connection');
        $adapter = new MySQLDBALAdapter($driver);

        $this->assertInstanceOf(AdapterInterface::class, $adapter);
    }

    public function test_construction_and_get_driver(): void
    {
        $driver = $this->createStub('Doctrine\DBAL\Connection');
        $adapter = new MySQLDBALAdapter($driver);

        $this->assertSame($driver, $adapter->getDriver());
    }

    public function test_get_driver_returns_correct_type(): void
    {
        $driver = $this->createStub('Doctrine\DBAL\Connection');
        $adapter = new MySQLDBALAdapter($driver);

        $this->assertInstanceOf('Doctrine\DBAL\Connection', $adapter->getDriver());
    }

    public function test_repeated_calls_return_same_instance(): void
    {
        $driver = $this->createStub('Doctrine\DBAL\Connection');
        $adapter = new MySQLDBALAdapter($driver);

        $first = $adapter->getDriver();
        $second = $adapter->getDriver();

        $this->assertSame($first, $second);
    }
}
