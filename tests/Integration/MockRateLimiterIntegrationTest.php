<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm)
 * @since       2025-11-09 00:13
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Integration;

use Exception;
use PHPUnit\Framework\TestCase;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Enums\AdapterTypeEnum;

/**
 * 🧪 **Class MockRateLimiterIntegrationTest**
 *
 * 🎯 **Purpose:**
 * Performs a lightweight integration test to verify that the Redis adapter
 * can be correctly resolved through {@see DatabaseResolver} and exposes the
 * expected core methods used in the rate limiter subsystem.
 *
 * 🧠 **Key Checks:**
 * - Ensures `connect()` and `healthCheck()` methods exist on the resolved adapter.
 * - Validates class resolution without requiring a live Redis connection.
 * - Serves as a mock-level integration test for CI/CD validation.
 *
 * 🧩 **Use Case:**
 * This test provides a non-destructive validation of Redis integration
 * for systems like `maatify/rate-limiter` that rely on adapter availability.
 *
 * ✅ **Example Run:**
 * ```bash
 * APP_ENV=testing vendor/bin/phpunit --filter MockRateLimiterIntegrationTest
 * ```
 */
final class MockRateLimiterIntegrationTest extends TestCase
{
    /**
     * 🧩 **Test Redis Mock Integration**
     *
     * Ensures that the Redis adapter can be resolved and exposes
     * the required methods for rate-limiting behavior.
     *
     * @throws Exception
     *
     * @return void
     */
    public function testRedisMockIntegration(): void
    {
        // ⚙️ Initialize configuration and resolve Redis adapter
        $config = new EnvironmentConfig(__DIR__ . '/../../');
        $resolver = new DatabaseResolver($config);
        $redis = $resolver->resolve(AdapterTypeEnum::REDIS);

        // ✅ Verify existence of core adapter methods
        $this->assertTrue(
            method_exists($redis, 'connect'),
            '❌ Expected method connect() not found on Redis adapter.'
        );

        $this->assertTrue(
            method_exists($redis, 'healthCheck'),
            '❌ Expected method healthCheck() not found on Redis adapter.'
        );
    }
}
