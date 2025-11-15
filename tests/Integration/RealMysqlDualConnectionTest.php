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

namespace Maatify\DataAdapters\Tests\Integration;

use PHPUnit\Framework\TestCase;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Core\EnvironmentConfig;

final class RealMysqlDualConnectionTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV['MAATIFY_FAKE_ENV'] = 1;
    }
    #[\PHPUnit\Framework\Attributes\DataProvider('provideDrivers')]
    public function testMysqlConnection(string $driver, string $dsnEnvVar): void
    {
        if (! getenv('CI')) {
            $this->markTestSkipped('Skipped offline MySQL integration test.');
        }
        // -----------------------------
        // 1) Read real .env values first
        // -----------------------------
        $configLoader = new EnvironmentConfig(dirname(__DIR__, 2));

        $host = $configLoader->get('MYSQL_HOST');
        $port = $configLoader->get('MYSQL_PORT');
        $db   = $configLoader->get('MYSQL_DB');
        $user = $configLoader->get('MYSQL_USER');
        $pass = $configLoader->get('MYSQL_PASS');

        // -----------------------------
        // 2) Override env vars with putenv() BEFORE reloading config
        // -----------------------------
        putenv("MYSQL_DSN");
        putenv("MYSQL_MAIN_DSN");
        putenv("MYSQL_DEFAULT_DSN");

        putenv("{$dsnEnvVar}=mysql:host={$host};port={$port};dbname={$db}");

        putenv("MYSQL_USER={$user}");
        putenv("MYSQL_PASS={$pass}");

        // -----------------------------
        // 3) Now reload config (important!)
        // -----------------------------
        $config   = new EnvironmentConfig(dirname(__DIR__, 2));
        $resolver = new DatabaseResolver($config);

        // -----------------------------
        // 4) Connect
        // -----------------------------
        $adapter = $resolver->resolve("mysql");
        $adapter->connect();

        $this->assertTrue(
            $adapter->healthCheck(),
            "❌ MySQL {$driver} health check failed."
        );
    }



    /**
     * 📦 Data Provider: DSN-based driver selector
     */
    public static function provideDrivers(): array
    {
        return [
            ['pdo',  'MYSQL_DSN'],         // → MySQLAdapter (PDO)
            ['dbal', 'MYSQL_MAIN_DSN'],    // → MySQLDbalAdapter (DBAL)
        ];
    }
}
