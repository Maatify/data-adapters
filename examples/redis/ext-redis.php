<?php

declare(strict_types=1);

use Maatify\DataAdapters\Adapters\Redis\RedisAdapter;

// Create Redis explicitly
$redis = new Redis();
$redis->connect('127.0.0.1');

// Wrap in adapter
$adapter = new RedisAdapter($redis);

// Retrieve and use
$driver = $adapter->getDriver();
$driver->ping();
