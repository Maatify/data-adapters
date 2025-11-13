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

final class MysqlDbalDsnAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV = [
            'APP_ENV'        => 'testing',
            'MYSQL_LOGS_DSN' => 'mysql://user:pass@10.0.0.5:3306/logs',
        ];
    }

    public function testDbalReadsUrlDsn(): void
    {
        $adapter = new MySQLDbalAdapter(new EnvironmentConfig(__DIR__), 'logs');
        $cfg = $adapter->debugConfig();

        $this->assertSame('mysql://user:pass@10.0.0.5:3306/logs', $cfg->dsn);
    }
}
