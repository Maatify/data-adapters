<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-11 14:54
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Integration;

use Exception;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Enums\AdapterTypeEnum;
use PHPUnit\Framework\TestCase;

/**
 * 🧪 Class RealMysqlDualConnectionTest
 *
 * ✅ Purpose:
 * Dynamically verifies both PDO and DBAL MySQL connections
 * without changing the base `.env` file.
 *
 * Each test case overrides `MYSQL_DRIVER` at runtime,
 * ensuring that both adapters function correctly in a unified suite.
 *
 * ⚙️ Example Run:
 * ```bash
 * vendor/bin/phpunit --filter RealMysqlDualConnectionTest
 * ```
 */
final class RealMysqlDualConnectionTest extends TestCase
{
    /**
     * @dataProvider provideDrivers
     * @throws Exception
     */
    public function testMysqlConnection(string $driver): void
    {
        // 🔄 Dynamically switch between PDO and DBAL
        putenv("MYSQL_DRIVER={$driver}");

        $config = new EnvironmentConfig(dirname(__DIR__, 2));
        $resolver = new DatabaseResolver($config);
        $adapter = $resolver->resolve(AdapterTypeEnum::MYSQL);

        $adapter->connect();
        $this->assertTrue(
            $adapter->healthCheck(),
            "MySQLAdapter ({$driver}) health check must return true."
        );
    }

    public static function provideDrivers(): array
    {
        return [
            ['pdo'],
            ['dbal'],
        ];
    }
}
