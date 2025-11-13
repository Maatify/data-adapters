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

final class RealPredisConnectionTest extends TestCase
{
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
