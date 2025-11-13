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

final class MysqlDsnAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        putenv('APP_ENV=testing');
        putenv('MYSQL_MAIN_DSN=mysql:host=10.0.0.1;dbname=main;charset=utf8mb4');
        putenv('MYSQL_MAIN_USER=root');
        putenv('MYSQL_MAIN_PASS=secret');

        $_SERVER['APP_ENV'] = 'testing';
        $_SERVER['MYSQL_MAIN_DSN'] = 'mysql:host=10.0.0.1;dbname=main;charset=utf8mb4';
        $_SERVER['MYSQL_MAIN_USER'] = 'root';
        $_SERVER['MYSQL_MAIN_PASS'] = 'secret';

        $_ENV['APP_ENV'] = 'testing';
        $_ENV['MYSQL_MAIN_DSN'] = 'mysql:host=10.0.0.1;dbname=main;charset=utf8mb4';
        $_ENV['MYSQL_MAIN_USER'] = 'root';
        $_ENV['MYSQL_MAIN_PASS'] = 'secret';
    }

    public function testMysqlAdapterReadsDsn(): void
    {
        $adapter = new MySQLAdapter(new EnvironmentConfig(__DIR__), 'main');

        $cfg = $adapter->debugConfig();

        $this->assertSame(
            'mysql:host=10.0.0.1;dbname=main;charset=utf8mb4',
            $cfg->dsn
        );
        $this->assertSame('root', $cfg->user);
        $this->assertSame('secret', $cfg->pass);
    }
}
