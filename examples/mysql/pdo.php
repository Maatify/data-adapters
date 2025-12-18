<?php

declare(strict_types=1);

use Maatify\DataAdapters\Adapters\MySQL\MySQLPDOAdapter;

// Create PDO explicitly
$pdo = new PDO('mysql:host=127.0.0.1;dbname=test_db', 'user', 'password');

// Wrap in adapter
$adapter = new MySQLPDOAdapter($pdo);

// Retrieve and use
$driver = $adapter->getDriver();
$stmt = $driver->query('SELECT 1');
