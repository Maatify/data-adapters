<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-18 03:17
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-adapters view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Factories;

use Maatify\DataAdapters\Adapters\Mongo\MongoDatabaseAdapter;
use Maatify\DataAdapters\Exceptions\AdapterCreationException;
use MongoDB\Database;

final class MongoAdapterFactory
{
    public static function fromDatabase(Database $database): MongoDatabaseAdapter
    {
        return new MongoDatabaseAdapter($database);
    }

    /**
     *
     * @phpstan-param callable():Database $databaseFactory
     * /
     */
    public static function fromDatabaseFactory(callable $databaseFactory): MongoDatabaseAdapter
    {
        try {
            $database = $databaseFactory();

            return new MongoDatabaseAdapter($database);
        } catch (\Throwable $e) {
            throw new AdapterCreationException(
                'Failed to create MongoDB adapter',
                $e
            );
        }
    }
}
