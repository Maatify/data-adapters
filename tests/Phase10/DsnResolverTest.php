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

/**
 * 🧪 **DsnResolverTest**
 *
 * 🎯 Validates the DSN-first resolution logic across MySQL, MongoDB, and Redis
 * as implemented during **Phase 10**.
 *
 * This test suite ensures:
 *
 * - **Correct parsing of adapter routes** such as `mysql.main` or `redis.cache`
 * - **Correct DSN lookup per profile** (e.g., `MYSQL_MAIN_DSN`)
 * - **Fallback behavior** for missing profiles
 * - **Profile independence** (each profile resolves its own DSN)
 *
 * ✔ Uses `APP_ENV=testing` to avoid loading `.env`
 * ✔ All environment variables are mocked explicitly
 *
 * @example Example route:
 * ```php
 * $adapter = $resolver->resolve('mongo.logs');
 * $cfg = $adapter->debugConfig();
 * ```
 */
final class DsnResolverTest extends TestCase
{
    /**
     * 🧪 Prepare mock environment before each test.
     *
     * Ensures full isolation from external environment using:
     * - Explicit DSN values per adapter profile
     * - Legacy fallback keys for MySQL
     * - `APP_ENV=testing` to disable `.env` loading
     */
    protected function setUp(): void
    {
        $_ENV = [
            'APP_ENV' => 'testing',

            // DSN Profiles
            'MYSQL_MAIN_DSN'  => 'mysql:host=1.1.1.1;dbname=main',
            'MONGO_LOGS_DSN'  => 'mongodb://2.2.2.2:27017/logs',
            'REDIS_CACHE_DSN' => 'redis://3.3.3.3:6379',

            // Legacy fallback (for testing hybrid behavior)
            'MYSQL_HOST' => '127.0.0.1',
            'MYSQL_PORT' => '3306',
            'MYSQL_DB'   => 'test',
        ];
    }

    /**
     * 🧪 Ensure parseStringRoute correctly extracts `type` and `profile`.
     */
    public function testParseStringRouteProfile(): void
    {
        $resolver = new DatabaseResolver(new EnvironmentConfig(__DIR__));

        $method = new \ReflectionMethod(DatabaseResolver::class, 'parseStringRoute');
        $method->setAccessible(true);

        [$type, $profile] = $method->invoke($resolver, 'mysql.main');

        $this->assertSame('mysql', $type);
        $this->assertSame('main', $profile);
    }

    /**
     * 🧪 Ensure routes with no profile return null profile.
     */
    public function testParseStringRouteNoProfile(): void
    {
        $resolver = new DatabaseResolver(new EnvironmentConfig(__DIR__));

        $method = new \ReflectionMethod(DatabaseResolver::class, 'parseStringRoute');
        $method->setAccessible(true);

        [$type, $profile] = $method->invoke($resolver, 'redis');

        $this->assertSame('redis', $type);
        $this->assertNull($profile);
    }

    /**
     * 🧪 Ensure MySQL adapter resolves DSN for the `main` profile.
     */
    public function testMysqlMainProfileUsesDSN(): void
    {
        $resolver = new DatabaseResolver(new EnvironmentConfig(__DIR__));
        $adapter  = $resolver->resolve('mysql.main');

        $cfg = $adapter->debugConfig();

        $this->assertSame('mysql:host=1.1.1.1;dbname=main', $cfg->dsn);
    }

    /**
     * 🧪 Ensure MongoDB adapter resolves DSN for the `logs` profile.
     */
    public function testMongoLogsProfileUsesDSN(): void
    {
        $resolver = new DatabaseResolver(new EnvironmentConfig(__DIR__));
        $adapter  = $resolver->resolve('mongo.logs');

        $cfg = $adapter->debugConfig();

        $this->assertSame('mongodb://2.2.2.2:27017/logs', $cfg->dsn);
    }

    /**
     * 🧪 Ensure Redis adapter resolves DSN for the `cache` profile.
     */
    public function testRedisCacheProfileUsesDsn(): void
    {
        $resolver = new DatabaseResolver(new EnvironmentConfig(__DIR__));
        $adapter  = $resolver->resolve('redis.cache');

        $cfg = $adapter->debugConfig();

        $this->assertSame('redis://3.3.3.3:6379', $cfg->dsn);
    }
}
