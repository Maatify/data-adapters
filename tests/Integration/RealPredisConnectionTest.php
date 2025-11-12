<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm)
 * @since       2025-11-11 17:35
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Integration;

use Maatify\DataAdapters\Adapters\PredisAdapter;
use Maatify\DataAdapters\Core\EnvironmentConfig;
use PHPUnit\Framework\TestCase;
use Predis\Client;

/**
 * 🧪 **Class RealPredisConnectionTest**
 *
 * 🎯 **Purpose:**
 * Validates a real Redis connection using {@see PredisAdapter}, confirming connectivity,
 * command execution, and overall adapter health using environment-based configuration.
 *
 * 🧠 **Key Verifications:**
 * - Confirms that a Predis client can connect to Redis.
 * - Executes core Redis commands (`PING`, `SET`, `GET`) successfully.
 * - Ensures `PredisAdapter::healthCheck()` returns `true`.
 *
 * 🧩 **Requirements:**
 * A running Redis instance with accessible credentials defined in `.env.testing` or `.env.local`:
 * ```
 * REDIS_HOST=127.0.0.1
 * REDIS_PORT=6379
 * REDIS_PASSWORD=
 * ```
 *
 * ✅ **Example Run:**
 * ```bash
 * APP_ENV=testing vendor/bin/phpunit --filter RealPredisConnectionTest
 * ```
 */
final class RealPredisConnectionTest extends TestCase
{
    /**
     * 🧩 **Test: Real Redis Connection via Predis**
     *
     * Establishes a live Redis connection through {@see PredisAdapter},
     * validates connection health, and performs basic read/write operations.
     *
     * ⚙️ **Validation Steps:**
     * 1️⃣ Load environment configuration.
     * 2️⃣ Initialize {@see PredisAdapter} and connect.
     * 3️⃣ Verify PING, SET, and GET operations.
     * 4️⃣ Clean up any test data created.
     *
     * @return void
     */
    public function testPredisRealConnection(): void
    {
        // 🧱 Arrange: Load environment and initialize adapter
        $config = new EnvironmentConfig(dirname(__DIR__, 2));
        $adapter = new PredisAdapter($config);

        // ⚙️ Act: Connect to Redis
        $adapter->connect();
        $connection = $adapter->getConnection();

        // ✅ Assert: Ensure valid Predis client
        $this->assertInstanceOf(
            Client::class,
            $connection,
            '❌ Expected Predis\Client instance for Redis connection.'
        );

        // 🩺 Health Check
        $pong = $connection->ping();
        $this->assertSame('PONG', (string)$pong, '❌ Predis should respond with PONG.');

        $this->assertTrue(
            $adapter->healthCheck(),
            '❌ PredisAdapter health check must return true.'
        );

        // 🧪 Perform SET/GET round-trip
        $connection->set('maatify:test', 'connected');
        $this->assertSame(
            'connected',
            $connection->get('maatify:test'),
            '❌ Expected "connected" value mismatch from Redis SET/GET round-trip.'
        );

        // 🧹 Cleanup: remove test key
        $connection->del(['maatify:test']);
    }
}
