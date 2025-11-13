<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-11 13:50
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Integration;

use Exception;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Core\EnvironmentConfig;
use PHPUnit\Framework\TestCase;
use Redis;

/**
 * 🧪 **Class RealRedisConnectionTest**
 *
 * 🎯 **Purpose:**
 * Provides an integration test that validates real Redis connection behavior
 * within the Maatify Data Adapters system. Ensures that environment variables,
 * connection setup, and health checks are functioning correctly.
 *
 * 🧠 **Key Verifications:**
 * - Confirms that {@see DatabaseResolver} successfully resolves a Redis adapter.
 * - Ensures that the adapter can connect to a real Redis server.
 * - Validates that the underlying connection is an instance of {@see Redis}.
 * - Confirms that `healthCheck()` and `ping()` produce valid responses.
 *
 * ⚙️ **Usage Instructions:**
 * Duplicate this template for other adapters (MySQL, Mongo, etc.) by replacing:
 * - `AdapterTypeEnum::Redis` → with appropriate adapter type.
 * - `Redis::class` → with expected connection class.
 * - Optional `ping()` logic → with read/write or query checks.
 *
 * ✅ **Example Run:**
 * ```bash
 * APP_ENV=testing vendor/bin/phpunit --filter RealRedisConnectionTest
 * ```
 */
final class RealRedisConnectionTest extends TestCase
{
    /**
     * 🧩 **Test Redis Real Connection**
     *
     * Ensures that Redis adapter successfully connects and passes health verification.
     *
     * @return void
     * @throws Exception
     */
    public function testRedisRealConnection(): void
    {
        // ⚙️ Initialize configuration and resolve adapter
        $config = new EnvironmentConfig(dirname(__DIR__, 2));
        $resolver = new DatabaseResolver($config);
        $adapter = $resolver->resolve("redis");

        // 🧠 Establish real connection
        $adapter->connect();
        $connection = $adapter->getConnection();

        // ✅ Verify Redis connection instance
        $this->assertInstanceOf(
            Redis::class,
            $connection,
            'Expected active Redis connection instance.'
        );

        // ✅ Ensure adapter health check returns true
        $this->assertTrue(
            $adapter->healthCheck(),
            'RedisAdapter health check must return true.'
        );

        // 🔍 Optional direct I/O test: verify ping response
        if (method_exists($connection, 'ping')) {
            $pong = $connection->ping();

            // 🧠 Some Redis drivers return `true`, others "PONG" or "+PONG"
            $this->assertContains(
                $pong,
                ['PONG', '+PONG', true],
                sprintf('Unexpected Redis PING response: %s', var_export($pong, true))
            );
        }
    }
}
