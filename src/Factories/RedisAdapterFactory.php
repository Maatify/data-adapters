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

use Maatify\DataAdapters\Adapters\Redis\RedisAdapter;
use Maatify\DataAdapters\Adapters\Redis\RedisPredisAdapter;
use Maatify\DataAdapters\Exceptions\AdapterCreationException;
use Predis\Client;
use Redis;

final class RedisAdapterFactory
{
    public static function fromRedis(Redis $redis): RedisAdapter
    {
        return new RedisAdapter($redis);
    }

    public static function fromPredis(Client $client): RedisPredisAdapter
    {
        return new RedisPredisAdapter($client);
    }

    /**
     * @phpstan-param callable():Redis $redisFactory
     */
    public static function fromRedisFactory(callable $redisFactory): RedisAdapter
    {
        try {
            $redis = $redisFactory();

            return new RedisAdapter($redis);
        } catch (\Throwable $e) {
            throw new AdapterCreationException(
                'Failed to create ext-redis adapter',
                $e
            );
        }
    }
}
