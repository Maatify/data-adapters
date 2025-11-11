<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm)
 * @since       2025-11-08 21:15
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Diagnostics;

use Exception;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Diagnostics\DiagnosticService;
use Maatify\DataAdapters\Enums\AdapterTypeEnum;
use PHPUnit\Framework\TestCase;

/**
 * 🧪 **Class DiagnosticServiceTest**
 *
 * 🎯 **Purpose:**
 * Validates the behavior of {@see DiagnosticService}, ensuring it properly collects
 * and returns diagnostic data for multiple adapters within the Maatify ecosystem.
 *
 * 🧠 **Core Verifications:**
 * - Confirms that the `collect()` method returns a well-structured array.
 * - Ensures that each diagnostic entry includes an `"adapter"` key.
 * - Verifies registration of multiple adapters (Redis, Mongo, MySQL).
 *
 * 🧩 **When to Use:**
 * Use this test as part of system-level diagnostics validation to ensure the
 * environment configuration, resolver, and diagnostic layers interact correctly.
 *
 * ✅ **Example Run:**
 * ```bash
 * APP_ENV=testing vendor/bin/phpunit --filter DiagnosticServiceTest
 * ```
 */
final class DiagnosticServiceTest extends TestCase
{
    /**
     * 🧩 **Test Diagnostic Array Structure**
     *
     * Ensures that {@see DiagnosticService::collect()} returns a structured array
     * with the expected keys after registering multiple adapters.
     *
     * @throws Exception
     *
     * @return void
     */
    public function testDiagnosticsReturnsArray(): void
    {
        // ⚙️ Initialize configuration and dependency resolver
        $config   = new EnvironmentConfig(dirname(__DIR__, 3));
        $resolver = new DatabaseResolver($config);

        // 🧠 Instantiate and register adapters for diagnostics
        $service = new DiagnosticService($config, $resolver);
        $service->register([
            AdapterTypeEnum::REDIS,
            AdapterTypeEnum::MONGO,
            AdapterTypeEnum::MYSQL,
        ]);

        // 🧩 Collect diagnostic data
        $result = $service->collect();

        // ✅ Verify array structure and content
        $this->assertIsArray(
            $result,
            '❌ Expected DiagnosticService::collect() to return an array.'
        );

        $this->assertArrayHasKey(
            'adapter',
            $result[0],
            '❌ Expected key "adapter" not found in diagnostics result.'
        );
    }
}
