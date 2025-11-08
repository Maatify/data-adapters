<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-11-08
 * Time: 20:34
 * Project: data-adapters
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Core;

use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Core\DatabaseResolver;
use PHPUnit\Framework\TestCase;

final class CoreStructureTest extends TestCase
{
    public function testEnvironmentConfigLoadsVariables(): void
    {
        $env = new EnvironmentConfig(dirname(__DIR__, 3));
        $this->assertNotNull($env->get('APP_ENV'));
    }

    public function testDatabaseResolverExists(): void
    {
        $resolver = new DatabaseResolver(new EnvironmentConfig(dirname(__DIR__, 3)));
        $this->assertInstanceOf(DatabaseResolver::class, $resolver);
    }
}
