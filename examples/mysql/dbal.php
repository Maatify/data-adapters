<?php

declare(strict_types=1);

require __DIR__ . '/../../vendor/autoload.php';

use Doctrine\DBAL\DriverManager;
use Maatify\DataAdapters\Adapters\MySQL\MySQLDBALAdapter;

// Create DBAL Connection explicitly
$connection = DriverManager::getConnection([
    'dbname' => 'test_db',
    'user' => 'user',
    'password' => 'password',
    'host' => '127.0.0.1',
    'driver' => 'pdo_mysql',
]);

// Wrap in adapter
$adapter = new MySQLDBALAdapter($connection);

// Retrieve and use
$driver = $adapter->getDriver();
$driver->executeQuery('SELECT 1');
