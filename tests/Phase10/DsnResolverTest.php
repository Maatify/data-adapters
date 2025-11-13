<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-13 19:11
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Phase10;

use PHPUnit\Framework\TestCase;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Core\EnvironmentConfig;

final class DsnResolverTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV = [
            'APP_ENV' => 'testing',

            // DSN Profiles
            'MYSQL_MAIN_DSN' => 'mysql:host=1.1.1.1;dbname=main',
            'MONGO_LOGS_DSN' => 'mongodb://2.2.2.2:27017/logs',
            'REDIS_CACHE_DSN' => 'redis://3.3.3.3:6379',

            // Legacy
            'MYSQL_HOST' => '127.0.0.1',
            'MYSQL_PORT' => '3306',
            'MYSQL_DB'   => 'test',
        ];
    }

    public function testParseStringRouteProfile(): void
    {
        $resolver = new DatabaseResolver(new EnvironmentConfig(__DIR__));

        $method = new \ReflectionMethod(DatabaseResolver::class, 'parseStringRoute');
        $method->setAccessible(true);
        [$type, $profile] = $method->invoke($resolver, 'mysql.main');

        $this->assertSame('mysql', $type);
        $this->assertSame('main', $profile);
    }

    public function testParseStringRouteNoProfile(): void
    {
        $resolver = new DatabaseResolver(new EnvironmentConfig(__DIR__));

        $method = new \ReflectionMethod(DatabaseResolver::class, 'parseStringRoute');
        $method->setAccessible(true);
        [$type, $profile] = $method->invoke($resolver, 'redis');

        $this->assertSame('redis', $type);
        $this->assertNull($profile);
    }

    public function testMysqlMainProfileUsesDSN(): void
    {
        $resolver = new DatabaseResolver(new EnvironmentConfig(__DIR__));
        $adapter  = $resolver->resolve('mysql.main');

        $cfg = $adapter->debugConfig(); // Temporary invisible debug accessor

        $this->assertSame('mysql:host=1.1.1.1;dbname=main', $cfg->dsn);
    }

    public function testMongoLogsProfileUsesDSN(): void
    {
        $resolver = new DatabaseResolver(new EnvironmentConfig(__DIR__));
        $adapter  = $resolver->resolve('mongo.logs');

        $cfg = $adapter->debugConfig();

        $this->assertSame('mongodb://2.2.2.2:27017/logs', $cfg->dsn);
    }

    public function testRedisCacheProfileUsesDsn(): void
    {
        $resolver = new DatabaseResolver(new EnvironmentConfig(__DIR__));
        $adapter  = $resolver->resolve('redis.cache');

        $cfg = $adapter->debugConfig();

        $this->assertSame('redis://3.3.3.3:6379', $cfg->dsn);
    }
}

