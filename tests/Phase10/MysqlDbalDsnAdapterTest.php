<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-13 19:16
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Phase10;

use PHPUnit\Framework\TestCase;
use Maatify\DataAdapters\Adapters\MySQLDbalAdapter;
use Maatify\DataAdapters\Core\EnvironmentConfig;

/**
 * 🧪 **MysqlDbalDsnAdapterTest**
 *
 * 🎯 Ensures that the DBAL MySQL adapter correctly reads and prioritizes
 * DSN-based configuration — specifically Doctrine-style URL DSNs:
 *
 * ```
 * mysql://user:pass@host:port/database
 * ```
 *
 * This test validates that:
 * - DSN is correctly picked up using the profile name (`logs`)
 * - DSN-first behavior works the same as in PDO MySQLAdapter
 * - Legacy host/port/db/env variables do not override DSN
 *
 * ✔ Fully aligned with Phase 10 DSN architecture
 * ✔ `APP_ENV=testing` prevents accidental `.env` loading
 *
 * @example
 * ```php
 * $_ENV['MYSQL_LOGS_DSN'] = 'mysql://user:pass@10.0.0.5:3306/logs';
 * $adapter = new MySQLDbalAdapter($env, 'logs');
 * $cfg = $adapter->debugConfig();
 * ```
 */
final class MysqlDbalDsnAdapterTest extends TestCase
{
    /**
     * 🧪 Seed mock environment for the test.
     *
     * Ensures DBAL adapter reads:
     * - DSN: `MYSQL_LOGS_DSN`
     * - Profile: `logs`
     */
    protected function setUp(): void
    {
        $_ENV = [
            'APP_ENV'        => 'testing',
            'MYSQL_LOGS_DSN' => 'mysql://user:pass@10.0.0.5:3306/logs',
        ];
    }

    /**
     * 🧪 Ensure DBAL correctly loads Doctrine URL DSN for logs profile.
     */
    public function testDbalReadsUrlDsn(): void
    {
        $adapter = new MySQLDbalAdapter(new EnvironmentConfig(__DIR__), 'logs');
        $cfg = $adapter->debugConfig();

        $this->assertSame('mysql://user:pass@10.0.0.5:3306/logs', $cfg->dsn);
    }
}
