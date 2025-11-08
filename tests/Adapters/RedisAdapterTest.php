<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-11-08
 * Time: 20:52
 * Project: maatify:data-adapters
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Adapters;

use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Enums\AdapterTypeEnum;
use PHPUnit\Framework\TestCase;

/**
 * 🧪 Class RedisAdapterTest
 *
 * 🧩 Purpose:
 * Ensures that the {@see DatabaseResolver} correctly resolves and instantiates
 * the appropriate Redis adapter implementation (either native Redis or Predis).
 *
 * ✅ What It Verifies:
 * - That adapter resolution for `AdapterTypeEnum::Redis` works as expected.
 * - The returned adapter instance exposes the `connect()` method as required.
 *
 * ⚙️ Example Execution:
 * ```bash
 * ./vendor/bin/phpunit --filter RedisAdapterTest
 * ```
 *
 * @package Maatify\DataAdapters\Tests\Adapters
 */
final class RedisAdapterTest extends TestCase
{
    /**
     * 🎯 Test that Redis adapter resolution works correctly.
     *
     * Validates that {@see DatabaseResolver} returns a valid Redis-compatible
     * adapter (either RedisAdapter or PredisAdapter) which implements the
     * required `connect()` method from {@see \Maatify\DataAdapters\Contracts\AdapterInterface}.
     *
     * ✅ Expected Result:
     * - The resolved adapter must have a `connect` method.
     */
    public function testRedisFallbackResolution(): void
    {
        // 🧩 Initialize the environment configuration and resolver
        $resolver = new DatabaseResolver(new EnvironmentConfig(dirname(__DIR__, 3)));

        // ⚙️ Resolve Redis adapter (auto-selects Redis or Predis)
        $adapter = $resolver->resolve(AdapterTypeEnum::Redis);

        // ✅ Assert that the resolved adapter exposes the expected method
        $this->assertTrue(
            method_exists($adapter, 'connect'),
            'Resolved Redis adapter must implement the connect() method.'
        );
    }
}
