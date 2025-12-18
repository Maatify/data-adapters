<?php

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Contract;

use Maatify\DataAdapters\Contracts\Adapter\AdapterInterface;
use Maatify\DataAdapters\TestDoubles\DummyAdapter;
use PHPUnit\Framework\TestCase;

class AdapterInterfaceTest extends TestCase
{
    public function testGetDriverExistsAndReturnsObject(): void
    {
        $driver = new \stdClass();
        $adapter = new DummyAdapter($driver);

        $this->assertInstanceOf(AdapterInterface::class, $adapter);
        $this->assertTrue(method_exists($adapter, 'getDriver'));

        $returnedDriver = $adapter->getDriver();
        $this->assertIsObject($returnedDriver);
        $this->assertSame($driver, $returnedDriver);
    }
}
