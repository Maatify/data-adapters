<?php

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Adapters\MySQL;

use Maatify\DataAdapters\Adapters\MySQL\MySQLPDOAdapter;
use Maatify\DataAdapters\Contracts\Adapter\AdapterInterface;
use PDO;
use PHPUnit\Framework\TestCase;

class MySQLPDOAdapterTest extends TestCase
{
    public function testConstructorAcceptsPDOAndGetDriverReturnsIt(): void
    {
        $pdo = $this->createMock(PDO::class);
        $adapter = new MySQLPDOAdapter($pdo);

        $this->assertInstanceOf(AdapterInterface::class, $adapter);
        $this->assertSame($pdo, $adapter->getDriver());
    }
}
