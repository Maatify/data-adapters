<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Maatify\DataAdapters\Adapters\Mongo\MongoDatabaseAdapter;
use MongoDB\Client;

// Create MongoDB Database explicitly
$client = new Client('mongodb://127.0.0.1:27017');
$database = $client->selectDatabase('test_db');

// Wrap in adapter
$adapter = new MongoDatabaseAdapter($database);

// Retrieve and use
$driver = $adapter->getDriver();
$driver->listCollections();
