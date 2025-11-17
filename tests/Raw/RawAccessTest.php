<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-17 10:15
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Raw;

use PHPUnit\Framework\TestCase;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Core\EnvironmentConfig;

final class RawAccessTest extends TestCase
{
    private DatabaseResolver $resolver;

    protected function setUp(): void
    {
        $this->resolver = new DatabaseResolver(
            new EnvironmentConfig(dirname(__DIR__, 2))
        );
    }

    public function testMysqlPdoRaw(): void
    {
        putenv("MYSQL_MAIN_DSN=mysql:host=127.0.0.1;dbname=test;port=3306");
        putenv("MYSQL_MAIN_USER=root");
        putenv("MYSQL_MAIN_PASS=root");

        $mysql = $this->resolver->resolve('mysql.main');
        $raw = $mysql->getDriver();

        $this->assertInstanceOf(\PDO::class, $raw);
    }

    public function testMysqlDbalRaw(): void
    {
        $dsn = "mysql://root:password@127.0.0.1:3306/test";
        putenv("MYSQL_LOGS_DSN={$dsn}");
        putenv("MYSQL_LOGS_DRIVER=dbal");

        $mysql = $this->resolver->resolve('mysql.logs');
        $raw = $mysql->getDriver();

        $this->assertInstanceOf(\Doctrine\DBAL\Connection::class, $raw);
    }

    public function testMongoRaw(): void
    {
        putenv("MONGO_MAIN_DSN=mongodb://127.0.0.1:27017/test");
        $mongo = $this->resolver->resolve('mongo.main');

        $raw = $mongo->getDriver();
        $this->assertInstanceOf(\MongoDB\Database::class, $raw);
    }

    public function testRedisRaw(): void
    {
        putenv("REDIS_MAIN_DSN=redis://127.0.0.1:6379");

        $redis = $this->resolver->resolve('redis.main');
        $raw = $redis->getDriver();

        $this->assertTrue(
            $raw instanceof \Redis || $raw instanceof \Predis\Client
        );
    }
}
