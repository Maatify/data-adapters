<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm)
 * @since       2025-11-09 00:14
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
 * 🧪 **Class MockSecurityGuardIntegrationTest**
 *
 * 🎯 **Purpose:**
 * Validates the integration between the {@see DatabaseResolver} and
 * the MySQL adapter in a controlled or mock testing environment.
 *
 * 🧠 **Core Verifications:**
 * - Confirms that the `MySQL` adapter can be properly resolved via the resolver.
 * - Ensures that essential methods (`connect`, `getConnection`) exist and are callable.
 * - Provides a lightweight integration baseline without performing a live DB connection.
 *
 * 🧩 **When to Use:**
 * Ideal for CI/CD pipelines and environment validation where a real database connection
 * might not be available but adapter structure and interface compliance need verification.
 *
 * ✅ **Example Run:**
 * ```bash
 * APP_ENV=testing vendor/bin/phpunit --filter MockSecurityGuardIntegrationTest
 * ```
 */
final class MockSecurityGuardIntegrationTest extends TestCase
{
    /**
     * 🧩 **Test MySQL Mock Integration**
     *
     * Ensures that the MySQL adapter is resolvable and exposes the expected
     * connection interface without requiring a real database backend.
     *
     * @throws Exception
     *
     * @return void
     */
    public function testMySQLMockIntegration(): void
    {
        // ⚙️ Initialize environment configuration and adapter resolver
        $config = new EnvironmentConfig(__DIR__ . '/../../');
        $resolver = new DatabaseResolver($config);

        // 🧠 Resolve MySQL adapter (no auto-connect)
        $mysql = $resolver->resolve(AdapterTypeEnum::MYSQL);

        // ✅ Verify adapter structure and method presence
        $this->assertTrue(
            method_exists($mysql, 'connect'),
            '❌ Expected method connect() not found on MySQL adapter.'
        );

        $this->assertTrue(
            method_exists($mysql, 'getConnection'),
            '❌ Expected method getConnection() not found on MySQL adapter.'
        );
    }
}
