<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-15 00:11
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Mongo;

use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Core\EnvironmentConfig;
use PHPUnit\Framework\TestCase;
use ReflectionException;
use ReflectionMethod;

/**
 * 🧪 **MongoProfileResolverTest**
 *
 * 🎯 Tests the MongoDB profile-based resolution logic implemented inside
 * `DatabaseResolver` + `MongoConfigBuilder`.
 *
 * Ensures:
 * - DSN per profile is correctly detected
 * - Database name is parsed correctly from DSN
 * - Profiles (`main`, `logs`) produce expected results
 *
 * ✔ Uses `APP_ENV=testing` to prevent automatic `.env` loading
 * ✔ Manually populates `$_ENV` to simulate environment settings
 *
 * @example
 * ```php
 * $resolver = new DatabaseResolver(new EnvironmentConfig(__DIR__));
 * $adapter  = $resolver->resolve('mongo.logs');
 * ```
 */
final class MongoProfileResolverTest extends TestCase
{
    /**
     * 🧪 Prepare test environment before each test.
     *
     * - Forces `APP_ENV=testing` so no `.env` file is loaded
     * - Seeds fake Mongo DSNs for `main` and `logs` profiles
     */
    protected function setUp(): void
    {
        $_ENV['APP_ENV'] = 'testing';

        $_ENV['MONGO_MAIN_DSN'] = 'mongodb://localhost:27017/main';
        $_ENV['MONGO_LOGS_DSN'] = 'mongodb://localhost:27017/logs';
    }

    /**
     * 🧪 **Test resolution for profile: main**
     *
     * Ensures:
     * - Mongo DSN → `mongodb://localhost:27017/main`
     * - Database parsed should equal `"main"`
     */
    public function testMainProfileResolution(): void
    {
        $resolver = new DatabaseResolver(new EnvironmentConfig(__DIR__));
        $adapter  = $resolver->resolve('mongo.main');
        $cfg      = $this->callProtected($adapter, 'resolveConfig');

        $this->assertSame('main', $cfg->database);
    }

    /**
     * 🧪 **Test resolution for profile: logs**
     *
     * Ensures:
     * - Mongo DSN → `mongodb://localhost:27017/logs`
     * - Database parsed should equal `"logs"`
     */
    public function testLogsProfileResolution(): void
    {
        $resolver = new DatabaseResolver(new EnvironmentConfig(__DIR__));
        $adapter  = $resolver->resolve('mongo.logs');
        $cfg      = $this->callProtected($adapter, 'resolveConfig');

        $this->assertSame('logs', $cfg->database);
    }

    /**
     * 🧩 **Reflection helper for accessing protected methods**
     *
     * Allows invoking protected/private methods inside tests without
     * modifying production code.
     *
     * @param   object  $obj     Target object
     * @param   string  $method  Method name to access
     *
     * @return mixed Result of invoked method
     * @throws ReflectionException
     */
    private function callProtected(object $obj, string $method)
    {
        $ref = new ReflectionMethod($obj, $method);
        $ref->setAccessible(true);

        return $ref->invoke($obj, \Maatify\Common\Enums\ConnectionTypeEnum::MONGO);
    }
}
