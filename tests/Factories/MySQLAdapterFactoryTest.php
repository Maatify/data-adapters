<?php

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Factories;

use Doctrine\DBAL\Connection;
use Maatify\DataAdapters\Adapters\MySQL\MySQLDBALAdapter;
use Maatify\DataAdapters\Adapters\MySQL\MySQLPDOAdapter;
use Maatify\DataAdapters\Exceptions\AdapterCreationException;
use Maatify\DataAdapters\Factories\MySQLAdapterFactory;
use PDO;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MySQLAdapterFactoryTest extends TestCase
{
    public function testFromPDOCreatesAdapter(): void
    {
        $pdo = $this->createMock(PDO::class);
        $adapter = MySQLAdapterFactory::fromPDO($pdo);

        $this->assertInstanceOf(MySQLPDOAdapter::class, $adapter);
        $this->assertSame($pdo, $adapter->getDriver());
    }

    public function testFromDBALCreatesAdapter(): void
    {
        $connection = $this->createMock(Connection::class);
        $adapter = MySQLAdapterFactory::fromDBAL($connection);

        $this->assertInstanceOf(MySQLDBALAdapter::class, $adapter);
        $this->assertSame($connection, $adapter->getDriver());
    }

    public function testFromPDOFactoryCreatesAdapterWhenCallableSucceeds(): void
    {
        $pdo = $this->createMock(PDO::class);
        $factory = fn () => $pdo;

        $adapter = MySQLAdapterFactory::fromPDOFactory($factory);

        $this->assertInstanceOf(MySQLPDOAdapter::class, $adapter);
        $this->assertSame($pdo, $adapter->getDriver());
    }

    public function testFromPDOFactoryThrowsAdapterCreationExceptionWhenCallableFails(): void
    {
        $originalException = new RuntimeException('fail');
        $factory = fn () => throw $originalException;

        $this->expectException(AdapterCreationException::class);
        $this->expectExceptionMessage('Failed to create PDO-based MySQL adapter');

        try {
            MySQLAdapterFactory::fromPDOFactory($factory);
        } catch (AdapterCreationException $e) {
            $this->assertSame($originalException, $e->getPrevious());
            throw $e;
        }
    }
}
