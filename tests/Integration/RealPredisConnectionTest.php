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
 * 🧩 **Purpose:**
 * Validate a real Redis connection through {@see PredisAdapter} using
 * environment credentials defined in `.env.testing` or `.env.local`.
 *
 * ✅ **Verifies:**
 * - Connection establishment to Redis using Predis client.
 * - Execution of basic Redis commands (`PING`, `SET`, `GET`).
 * - Successful `healthCheck()` response.
 *
 * ⚙️ **Example Run:**
 * ```bash
 * APP_ENV=testing vendor/bin/phpunit --filter RealPredisConnectionTest
 * ```
 *
 * ⚠️ **Requires:**
 * A running Redis instance and valid credentials in your `.env.testing` file:
 * ```
 * REDIS_HOST=127.0.0.1
 * REDIS_PORT=6379
 * REDIS_PASSWORD=
 * ```
 */
final class RealPredisConnectionTest extends TestCase
{
    /**
     * 🎯 **Test real Redis connection and ping command.**
     */
    public function testPredisRealConnection(): void
    {
        $config = new EnvironmentConfig(dirname(__DIR__, 2));

        $adapter = new PredisAdapter($config);
        $adapter->connect();

        $connection = $adapter->getConnection();

        // ✅ Ensure connection object is valid
        $this->assertInstanceOf(Client::class, $connection);

        // 🩺 Verify connectivity using Redis PING
        $pong = $connection->ping();
        $this->assertSame('PONG', (string) $pong, 'Predis should respond with PONG');

        // ⚙️ Check adapter-level health status
        $this->assertTrue($adapter->healthCheck(), 'PredisAdapter health check must return true');

        // 🧪 Optional basic SET/GET round-trip
        $connection->set('maatify:test', 'connected');
        $this->assertSame('connected', $connection->get('maatify:test'));

        // 🧹 Clean up
        $connection->del(['maatify:test']);
    }
}
