<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm)
 * @since       2025-11-09 00:15
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
 * 🧪 **Class MockMongoActivityIntegrationTest**
 *
 * 🎯 **Purpose:**
 * Performs a lightweight mock integration test for the Mongo adapter,
 * verifying structural and interface-level integrity without requiring
 * a live MongoDB instance.
 *
 * 🧠 **Core Verifications:**
 * - Ensures that the {@see DatabaseResolver} can successfully resolve
 *   a Mongo adapter instance.
 * - Confirms that the adapter exposes critical methods used across
 *   the Maatify ecosystem — namely `connect()` and `healthCheck()`.
 * - Useful for CI/CD pipelines to guarantee adapter readiness in isolation.
 *
 * 🧩 **When to Use:**
 * Run this test as part of automated validation for adapters or
 * integration boundaries that rely on Mongo connectivity logic.
 *
 * ✅ **Example Run:**
 * ```bash
 * APP_ENV=testing vendor/bin/phpunit --filter MockMongoActivityIntegrationTest
 * ```
 */
final class MockMongoActivityIntegrationTest extends TestCase
{
    /**
     * 🧩 **Test Mongo Mock Integration**
     *
     * Ensures that the Mongo adapter can be resolved and exposes
     * the required methods for connection and health validation.
     *
     * @throws Exception
     *
     * @return void
     */
    public function testMongoMockIntegration(): void
    {
        // ⚙️ Initialize environment configuration and resolve adapter
        $config = new EnvironmentConfig(__DIR__ . '/../../');
        $resolver = new DatabaseResolver($config);
        $mongo = $resolver->resolve(AdapterTypeEnum::MONGO);

        // ✅ Confirm presence of expected methods
        $this->assertTrue(
            method_exists($mongo, 'connect'),
            '❌ Expected method connect() not found on Mongo adapter.'
        );

        $this->assertTrue(
            method_exists($mongo, 'healthCheck'),
            '❌ Expected method healthCheck() not found on Mongo adapter.'
        );
    }
}
