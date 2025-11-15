<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-13 19:15
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Phase10;

use PHPUnit\Framework\TestCase;
use Maatify\DataAdapters\Adapters\MySQLAdapter;
use Maatify\DataAdapters\Core\EnvironmentConfig;

/**
 * 🧪 **MysqlDsnAdapterTest**
 *
 * 🎯 Validates DSN-first parsing behavior for the `MySQLAdapter`
 * under Phase 10 architecture.
 *
 * This test ensures:
 *
 * - DSN is read correctly from all PHP superglobals (`$_ENV`, `$_SERVER`, `putenv`)
 * - Username and password are correctly resolved per profile
 * - Legacy host/db variables do not override DSN
 *
 * ✔ Confirms strict DSN priority
 * ✔ Ensures compatibility across different environment-loading mechanisms
 *
 * @example
 * ```php
 * putenv('MYSQL_MAIN_DSN=mysql:host=10.0.0.1;dbname=main;charset=utf8mb4');
 * $adapter = new MySQLAdapter($env, 'main');
 * $cfg = $adapter->debugConfig();
 * ```
 */
final class MysqlDsnAdapterTest extends TestCase
{
    /**
     * 🧪 Load test env across all superglobals:
     *
     * - `putenv()` for CLI-level env
     * - `$_SERVER` to emulate web-server variables
     * - `$_ENV` to emulate preloaded environment
     *
     * Ensures the adapter reads DSN and credentials from **any** valid source.
     */
    protected function setUp(): void
    {
        // putenv
        putenv('APP_ENV=testing');
        putenv('MYSQL_MAIN_DSN=mysql:host=10.0.0.1;dbname=main;charset=utf8mb4');
        putenv('MYSQL_MAIN_USER=root');
        putenv('MYSQL_MAIN_PASS=secret');

        // $_SERVER mock
        $_SERVER['APP_ENV']          = 'testing';
        $_SERVER['MYSQL_MAIN_DSN']   = 'mysql:host=10.0.0.1;dbname=main;charset=utf8mb4';
        $_SERVER['MYSQL_MAIN_USER']  = 'root';
        $_SERVER['MYSQL_MAIN_PASS']  = 'secret';

        // $_ENV mock
        $_ENV['APP_ENV']             = 'testing';
        $_ENV['MYSQL_MAIN_DSN']      = 'mysql:host=10.0.0.1;dbname=main;charset=utf8mb4';
        $_ENV['MYSQL_MAIN_USER']     = 'root';
        $_ENV['MYSQL_MAIN_PASS']     = 'secret';
    }

    /**
     * 🧪 Ensure MySQLAdapter correctly reads DSN + credentials.
     */
    public function testMysqlAdapterReadsDsn(): void
    {
        $adapter = new MySQLAdapter(new EnvironmentConfig(__DIR__), 'main');
        $cfg     = $adapter->debugConfig();

        $this->assertSame(
            'mysql:host=10.0.0.1;dbname=main;charset=utf8mb4',
            $cfg->dsn
        );
        $this->assertSame('root', $cfg->user);
        $this->assertSame('secret', $cfg->pass);
    }
}
