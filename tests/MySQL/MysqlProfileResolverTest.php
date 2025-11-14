<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-14 20:47
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\MySQL;

use Maatify\Common\DTO\ConnectionConfigDTO;
use Maatify\Common\Enums\ConnectionTypeEnum;
use Maatify\DataAdapters\Adapters\MySQLAdapter;
use Maatify\DataAdapters\Adapters\MySQLDbalAdapter;
use Maatify\DataAdapters\Core\EnvironmentConfig;
use PHPUnit\Framework\TestCase;

/**
 * 🧪 MysqlProfileResolverTest
 *
 * Ensures correct behavior of:
 * - DSN priority
 * - Dynamic profile support
 * - Legacy fallback mode
 * - Builder merge with BaseAdapter data
 * - Profile routing from DatabaseResolver architecture
 */
final class MysqlProfileResolverTest extends TestCase
{
    private EnvironmentConfig $env;

    protected function setUp(): void
    {
        parent::setUp();

        // Isolate environment for testing
        $_ENV = [];

        $_ENV['APP_ENV'] = 'testing';

        $this->env = new EnvironmentConfig(__DIR__);
    }

    private function makeAdapter(?string $profile = null): MySQLAdapter
    {
        return new MySQLAdapter($this->env, $profile);
    }

    private function makeDbalAdapter(?string $profile = null): MySQLDbalAdapter
    {
        return new MySQLDbalAdapter($this->env, $profile);
    }

    // -------------------------------------------------------------
    // 1) DSN priority
    // -------------------------------------------------------------
    public function testDsnPriorityOverridesLegacy(): void
    {
        $_ENV['MYSQL_MAIN_DSN'] = 'mysql:host=1.2.3.4;dbname=testdb;port=3309';
        $_ENV['MYSQL_MAIN_DB']  = 'legacydb';
        $_ENV['MYSQL_MAIN_HOST'] = '99.99.99.99';

        $adapter = $this->makeAdapter('main');
        $cfg = $adapter->debugConfig();

        $this->assertEquals('mysql:host=1.2.3.4;dbname=testdb;port=3309', $cfg->dsn);
        $this->assertEquals('1.2.3.4', $cfg->host);
        $this->assertEquals('3309', $cfg->port);
        $this->assertEquals('testdb', $cfg->database);
    }

    // -------------------------------------------------------------
    // 2) Dynamic profiles
    // -------------------------------------------------------------
    public function testDynamicProfileResolution(): void
    {
        $_ENV['MYSQL_REPORTING_HOST'] = '10.0.0.44';
        $_ENV['MYSQL_REPORTING_PORT'] = '9999';
        $_ENV['MYSQL_REPORTING_DB']   = 'reports';

        $adapter = $this->makeAdapter('reporting');
        $cfg = $adapter->debugConfig();

        $this->assertEquals('10.0.0.44', $cfg->host);
        $this->assertEquals('9999', $cfg->port);
        $this->assertEquals('reports', $cfg->database);
    }

    // -------------------------------------------------------------
    // 3) DSN Doctrine URL
    // -------------------------------------------------------------
    public function testDoctrineUrlDsnParsing(): void
    {
        $_ENV['MYSQL_LOGS_DSN'] = 'mysql://user:pass@192.168.20.5:3307/logdb';

        $adapter = $this->makeAdapter('logs');
        $cfg = $adapter->debugConfig();

        $this->assertEquals('192.168.20.5', $cfg->host);
        $this->assertEquals('3307', $cfg->port);
        $this->assertEquals('logdb', $cfg->database);
    }

    // -------------------------------------------------------------
    // 4) Legacy fallback mode
    // -------------------------------------------------------------
    public function testLegacyModeWorksWithoutDsn(): void
    {
        $_ENV['MYSQL_MAIN_HOST'] = '127.0.0.55';
        $_ENV['MYSQL_MAIN_PORT'] = '3306';
        $_ENV['MYSQL_MAIN_DB']   = 'legacy_app';

        $adapter = $this->makeAdapter('main');
        $cfg = $adapter->debugConfig();

        $this->assertNull($cfg->dsn);
        $this->assertEquals('127.0.0.55', $cfg->host);
        $this->assertEquals('3306', $cfg->port);
        $this->assertEquals('legacy_app', $cfg->database);
    }

    // -------------------------------------------------------------
    // 5) Merge logic: builder overrides base values
    // -------------------------------------------------------------
    public function testBuilderOverridesBaseAdapterConfig(): void
    {
        // BaseAdapter keys
        $_ENV['MYSQL_MAIN_HOST'] = 'base-host';
        $_ENV['MYSQL_MAIN_DB']   = 'base-db';

        // Builder keys override all
        $_ENV['MYSQL_MAIN_DSN'] = 'mysql:host=builder-host;dbname=builderdb;port=3310';

        $adapter = $this->makeAdapter('main');
        $cfg = $adapter->debugConfig();

        $this->assertEquals('builder-host', $cfg->host);
        $this->assertEquals('builderdb', $cfg->database);
        $this->assertEquals('3310', $cfg->port);
    }

    // -------------------------------------------------------------
    // 6) DBAL adapter also uses builder
    // -------------------------------------------------------------
    public function testDbalAdapterUsesBuilder(): void
    {
        $_ENV['MYSQL_ANALYTICS_DSN'] = 'mysql:host=8.8.8.8;dbname=ana;port=8888';

        $adapter = $this->makeDbalAdapter('analytics');
        $cfg = $adapter->debugConfig();

        $this->assertEquals('8.8.8.8', $cfg->host);
        $this->assertEquals('8888', $cfg->port);
        $this->assertEquals('ana', $cfg->database);
        $this->assertEquals('analytics', $cfg->profile);
    }

    // -------------------------------------------------------------
    // 7) Unknown profile → still supported (dynamic)
    // -------------------------------------------------------------
    public function testUnknownProfileIsSupported(): void
    {
        $_ENV['MYSQL_BILLING_HOST'] = '100.200.100.200';
        $_ENV['MYSQL_BILLING_DB'] = 'billing_db';

        $adapter = $this->makeAdapter('billing');
        $cfg = $adapter->debugConfig();

        $this->assertEquals('100.200.100.200', $cfg->host);
        $this->assertEquals('billing_db', $cfg->database);
        $this->assertEquals('billing', $cfg->profile);
    }
}