<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-11-08
 * Time: 20:55
 * Project: maatify:data-adapters
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Adapters;

use Maatify\DataAdapters\Adapters\MongoAdapter;
use Maatify\DataAdapters\Core\EnvironmentConfig;
use PHPUnit\Framework\TestCase;

/**
 * 🧪 Class MongoAdapterTest
 *
 * 🧩 Purpose:
 * Ensures that the {@see MongoAdapter} can be successfully instantiated and
 * exposes the required methods defined by the {@see \Maatify\DataAdapters\Contracts\AdapterInterface}.
 *
 * ⚙️ Behavior:
 * - This test **does not** perform a live MongoDB connection.
 * - It validates structural integrity and method availability only.
 *
 * ✅ What It Tests:
 * - {@see MongoAdapter} class loads correctly.
 * - Required methods (`connect`, `healthCheck`) exist.
 *
 * ⚙️ Example Execution:
 * ```bash
 * ./vendor/bin/phpunit --filter MongoAdapterTest
 * ```
 *
 * @package Maatify\DataAdapters\Tests\Adapters
 */
final class MongoAdapterTest extends TestCase
{
    /**
     * 🎯 Verifies that MongoAdapter class is loadable and exposes expected methods.
     *
     * Checks:
     * - Proper instantiation of the adapter with a valid configuration.
     * - Existence of key methods: `connect()` and `healthCheck()`.
     *
     * ✅ Expected:
     * - Adapter instance of {@see MongoAdapter}.
     * - Both methods available.
     */
    public function testMongoAdapterClassLoads(): void
    {
        // 🧩 Load environment configuration
        $config = new EnvironmentConfig(dirname(__DIR__, 3));

        // ⚙️ Instantiate MongoAdapter
        $adapter = new MongoAdapter($config);

        // ✅ Verify adapter structure and interface compliance
        $this->assertInstanceOf(MongoAdapter::class, $adapter);
        $this->assertTrue(method_exists($adapter, 'connect'), 'MongoAdapter must implement connect()');
        $this->assertTrue(method_exists($adapter, 'healthCheck'), 'MongoAdapter must implement healthCheck()');
    }
}
