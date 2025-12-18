<?php

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Factories;

use Maatify\DataAdapters\Adapters\Mongo\MongoDatabaseAdapter;
use Maatify\DataAdapters\Exceptions\AdapterCreationException;
use Maatify\DataAdapters\Factories\MongoAdapterFactory;
use MongoDB\Database;
use PHPUnit\Framework\TestCase;
use RuntimeException;

class MongoAdapterFactoryTest extends TestCase
{
    public function testFromDatabaseCreatesAdapter(): void
    {
        $database = $this->createMock(Database::class);
        $adapter = MongoAdapterFactory::fromDatabase($database);

        $this->assertInstanceOf(MongoDatabaseAdapter::class, $adapter);
        $this->assertSame($database, $adapter->getDriver());
    }

    public function testFromDatabaseFactoryCreatesAdapterWhenCallableSucceeds(): void
    {
        $database = $this->createMock(Database::class);
        $factory = fn () => $database;

        $adapter = MongoAdapterFactory::fromDatabaseFactory($factory);

        $this->assertInstanceOf(MongoDatabaseAdapter::class, $adapter);
        $this->assertSame($database, $adapter->getDriver());
    }

    public function testFromDatabaseFactoryThrowsAdapterCreationExceptionWhenCallableFails(): void
    {
        $originalException = new RuntimeException('fail');
        $factory = fn () => throw $originalException;

        try {
            MongoAdapterFactory::fromDatabaseFactory($factory);
            $this->fail('AdapterCreationException was not thrown');
        } catch (AdapterCreationException $e) {
            $this->assertSame('Failed to create MongoDB adapter', $e->getMessage());
            $this->assertSame($originalException, $e->getPrevious());
        }
    }
}
