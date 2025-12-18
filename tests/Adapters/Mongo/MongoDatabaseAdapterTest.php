<?php

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Adapters\Mongo;

use Maatify\DataAdapters\Adapters\Mongo\MongoDatabaseAdapter;
use Maatify\DataAdapters\Contracts\Adapter\AdapterInterface;
use MongoDB\Database;
use PHPUnit\Framework\TestCase;

class MongoDatabaseAdapterTest extends TestCase
{
    public function testConstructorAcceptsDatabaseAndGetDriverReturnsIt(): void
    {
        $database = $this->createMock(Database::class);
        $adapter = new MongoDatabaseAdapter($database);

        $this->assertInstanceOf(AdapterInterface::class, $adapter);
        $this->assertSame($database, $adapter->getDriver());
    }
}
