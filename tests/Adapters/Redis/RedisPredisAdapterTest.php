<?php

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Adapters\Redis;

use Maatify\DataAdapters\Adapters\Redis\RedisPredisAdapter;
use Maatify\DataAdapters\Contracts\Adapter\AdapterInterface;
use PHPUnit\Framework\TestCase;
use Predis\Client;

class RedisPredisAdapterTest extends TestCase
{
    public function testConstructorAcceptsClientAndGetDriverReturnsIt(): void
    {
        $client = $this->createMock(Client::class);
        $adapter = new RedisPredisAdapter($client);

        $this->assertInstanceOf(AdapterInterface::class, $adapter);
        $this->assertSame($client, $adapter->getDriver());
    }
}
