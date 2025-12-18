<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-18 03:15
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-adapters view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Factories;

use Doctrine\DBAL\Connection;
use Maatify\DataAdapters\Adapters\MySQL\MySQLDBALAdapter;
use Maatify\DataAdapters\Adapters\MySQL\MySQLPDOAdapter;
use Maatify\DataAdapters\Exceptions\AdapterCreationException;
use PDO;
use Throwable;

final class MySQLAdapterFactory
{
    public static function fromPDO(PDO $pdo): MySQLPDOAdapter
    {
        return new MySQLPDOAdapter($pdo);
    }

    public static function fromDBAL(Connection $connection): MySQLDBALAdapter
    {
        return new MySQLDBALAdapter($connection);
    }

    /**
     * ⚠️ Explicit construction only
     * No env, no auto-detection
     *
     * @phpstan-param callable():PDO $pdoFactory
     */
    public static function fromPDOFactory(callable $pdoFactory): MySQLPDOAdapter
    {
        try {
            $pdo = $pdoFactory();

            return new MySQLPDOAdapter($pdo);
        } catch (Throwable $e) {
            throw new AdapterCreationException(
                'Failed to create PDO-based MySQL adapter',
                $e
            );
        }
    }
}
