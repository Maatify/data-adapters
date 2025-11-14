<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-15 00:11
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */


declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Mongo;

use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Core\EnvironmentConfig;
use PHPUnit\Framework\TestCase;

final class MongoProfileResolverTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV['APP_ENV'] = 'testing';

        $_ENV['MONGO_MAIN_DSN'] = 'mongodb://localhost:27017/main';
        $_ENV['MONGO_LOGS_DSN'] = 'mongodb://localhost:27017/logs';
    }

    public function testMainProfileResolution(): void
    {
        $resolver = new DatabaseResolver(new EnvironmentConfig(__DIR__));
        $adapter  = $resolver->resolve('mongo.main');
        $cfg      = $this->callProtected($adapter, 'resolveConfig');

        $this->assertSame('main', $cfg->database);
    }

    public function testLogsProfileResolution(): void
    {
        $resolver = new DatabaseResolver(new EnvironmentConfig(__DIR__));
        $adapter  = $resolver->resolve('mongo.logs');
        $cfg      = $this->callProtected($adapter, 'resolveConfig');

        $this->assertSame('logs', $cfg->database);
    }

    /**
     * Helper to access protected methods.
     */
    private function callProtected(object $obj, string $method)
    {
        $ref = new \ReflectionMethod($obj, $method);
        $ref->setAccessible(true);
        return $ref->invoke($obj, \Maatify\Common\Enums\ConnectionTypeEnum::MONGO);
    }
}


