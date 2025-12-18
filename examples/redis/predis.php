<?php

declare(strict_types=1);

use Maatify\DataAdapters\Adapters\Redis\RedisPredisAdapter;
use Predis\Client;

// Create Predis Client explicitly
$client = new Client([
    'scheme' => 'tcp',
    'host'   => '127.0.0.1',
    'port'   => 6379,
]);

// Wrap in adapter
$adapter = new RedisPredisAdapter($client);

// Retrieve and use
$driver = $adapter->getDriver();
$driver->ping();
