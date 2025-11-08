<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-11-08
 * Time: 20:56
 * Project: data-adapters
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Adapters;

use Maatify\DataAdapters\Adapters\MySQLAdapter;
use Maatify\DataAdapters\Core\EnvironmentConfig;
use PHPUnit\Framework\TestCase;

/**
 * 🧪 Ensures MySQLAdapter syntax and autoload correctness.
 * Does not perform real connection to avoid environment dependency.
 */
final class MySQLAdapterTest extends TestCase
{
    public function testMySQLAdapterClassLoads(): void
    {
        $config = new EnvironmentConfig(dirname(__DIR__, 3));
        $adapter = new MySQLAdapter($config);

        $this->assertInstanceOf(MySQLAdapter::class, $adapter);
        $this->assertTrue(method_exists($adapter, 'connect'));
        $this->assertTrue(method_exists($adapter, 'healthCheck'));
    }
}