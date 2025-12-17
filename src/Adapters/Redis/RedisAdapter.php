<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-17 18:45
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-adapters view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Adapters\Redis;

use Maatify\DataAdapters\Contracts\Adapter\AdapterInterface;
use Redis;

/**
 * @implements AdapterInterface<Redis>
 */
final class RedisAdapter implements AdapterInterface
{
    public function __construct(
        private readonly Redis $driver
    ) {
    }

    public function getDriver(): object
    {
        return $this->driver;
    }
}
