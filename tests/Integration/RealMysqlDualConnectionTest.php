<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
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
 * 🧪 **Class RealMysqlDualConnectionTest**
 *
 * 🎯 **Purpose:**
 * Validates both PDO-based and Doctrine DBAL-based MySQL adapters
 * by dynamically switching the `MYSQL_DRIVER` environment variable
 * at runtime — without modifying the base `.env` file.
 *
 * 🧠 **Key Verifications:**
 * - Confirms both `pdo` and `dbal` MySQL drivers connect successfully.
 * - Ensures `healthCheck()` works consistently across driver types.
 * - Guarantees interoperability for systems using either driver configuration.
 *
 * 🧩 **Use Case:**
 * This test is part of adapter reliability verification, ensuring
 * that `maatify/data-adapters` maintains multi-driver compatibility
 * across various MySQL setups.
 *
 * ✅ **Example Run:**
 * ```bash
 * vendor/bin/phpunit --filter RealMysqlDualConnectionTest
 * ```
 */
final class RealMysqlDualConnectionTest extends TestCase
{
    /**
     * 🧪 **Test Dual MySQL Driver Connectivity**
     *
     * Executes connection and health check logic for both
     * PDO and DBAL adapters dynamically via environment switching.
     *
     * @param string $driver The database driver to test (`pdo` or `dbal`).
     *
     * @throws Exception If environment loading or connection fails.
     *
     * @return void
     *
     * ✅ **Example:**
     * ```php
     * $this->testMysqlConnection('pdo');
     * $this->testMysqlConnection('dbal');
     * ```
     */
    #[\PHPUnit\Framework\Attributes\DataProvider('provideDrivers')]
    public function testMysqlConnection(string $driver): void
    {
        // 🔄 Dynamically switch driver for runtime testing
        putenv("MYSQL_DRIVER={$driver}");

        // ⚙️ Initialize configuration and resolve adapter
        $config = new EnvironmentConfig(dirname(__DIR__, 2));
        $resolver = new DatabaseResolver($config);
        $adapter = $resolver->resolve(AdapterTypeEnum::MYSQL);

        // 🚀 Attempt connection and validate health
        $adapter->connect();
        $this->assertTrue(
            $adapter->healthCheck(),
            "❌ MySQLAdapter ({$driver}) health check must return true."
        );
    }

    /**
     * 📦 **Data Provider for MySQL Driver Types**
     *
     * Supplies the list of driver types to test sequentially.
     *
     * @return array<int, array<int, string>> Driver list (`pdo`, `dbal`).
     */
    public static function provideDrivers(): array
    {
        return [
            ['pdo'],
            ['dbal'],
        ];
    }
}
