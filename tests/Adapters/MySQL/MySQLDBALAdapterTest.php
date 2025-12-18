<?php

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Adapters\MySQL;

use Doctrine\DBAL\Connection;
use Maatify\DataAdapters\Adapters\MySQL\MySQLDBALAdapter;
use Maatify\DataAdapters\Contracts\Adapter\AdapterInterface;
use PHPUnit\Framework\TestCase;

class MySQLDBALAdapterTest extends TestCase
{
    public function testConstructorAcceptsConnectionAndGetDriverReturnsIt(): void
    {
        $connection = $this->createMock(Connection::class);
        $adapter = new MySQLDBALAdapter($connection);

        $this->assertInstanceOf(AdapterInterface::class, $adapter);
        $this->assertSame($connection, $adapter->getDriver());
    }
}
