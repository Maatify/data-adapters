<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-11-08
 * Time: 21:15
 * Project: data-adapters
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Diagnostics;

use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Diagnostics\DiagnosticService;
use Maatify\DataAdapters\Enums\AdapterTypeEnum;
use PHPUnit\Framework\TestCase;

/**
 * Basic verification that DiagnosticService returns valid array structure.
 */
final class DiagnosticServiceTest extends TestCase
{
    public function testDiagnosticsReturnsArray(): void
    {
        $config   = new EnvironmentConfig(dirname(__DIR__, 3));
        $resolver = new DatabaseResolver($config);

        $service = new DiagnosticService($config, $resolver);
        $service->register([AdapterTypeEnum::Redis, AdapterTypeEnum::Mongo, AdapterTypeEnum::MySQL]);

        $result = $service->collect();

        $this->assertIsArray($result);
        $this->assertArrayHasKey('adapter', $result[0]);
    }
}
