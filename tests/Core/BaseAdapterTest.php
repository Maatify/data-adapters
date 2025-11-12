<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-11 19:21
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Core;

use Maatify\DataAdapters\Core\BaseAdapter;
use Maatify\DataAdapters\Core\EnvironmentConfig;
use PHPUnit\Framework\TestCase;
use ReflectionMethod;
use RuntimeException;

final class BaseAdapterTest extends TestCase
{
    public function testEnvironmentConfigLoadsProperly(): void
    {
        $config = new EnvironmentConfig(__DIR__ . '/../../');
        $this->assertInstanceOf(EnvironmentConfig::class, $config);
    }

    public function testHandleFailureRaisesException(): void
    {
        $config = new EnvironmentConfig(__DIR__ . '/../../');
        $adapter = $this->getMockForAbstractClass(BaseAdapter::class, [$config]);

        $error = new RuntimeException('Simulated failure');
        $ref = new ReflectionMethod($adapter, 'handleFailure');
        $ref->setAccessible(true);

        $this->expectException(RuntimeException::class);
        $ref->invoke($adapter, $error, 'testOperation', fn() => true);
    }

}
