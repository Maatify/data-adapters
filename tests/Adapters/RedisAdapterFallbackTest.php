<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-11 19:26
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Adapters;

use Maatify\DataAdapters\Adapters\RedisAdapter;
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Core\Exceptions\ConnectionException;
use PHPUnit\Framework\TestCase;

final class RedisAdapterFallbackTest extends TestCase
{
    public function testFallbackHandlesConnectionFailureGracefully(): void
    {
        putenv('ADAPTER_FALLBACK_ENABLED=true');

        $config  = new EnvironmentConfig(__DIR__ . '/../../');
        $adapter = new RedisAdapter($config);

        try {
            $adapter->connect(); // expected to fail gracefully if Redis is down
        } catch (ConnectionException) {
            $this->fail('ConnectionException should not be thrown when fallback is enabled.');
        }

        $this->assertFalse($adapter->isConnected());
    }
}


