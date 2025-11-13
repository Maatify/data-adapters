<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-13 19:18
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Phase10;

use PHPUnit\Framework\TestCase;
use Maatify\DataAdapters\Adapters\PredisAdapter;
use Maatify\DataAdapters\Core\EnvironmentConfig;

final class PredisDsnAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV = [
            'APP_ENV'         => 'testing',
            'REDIS_QUEUE_DSN' => 'redis://11.11.11.11:6380',
        ];
    }

    public function testPredisReadsDsn(): void
    {
        $adapter = new PredisAdapter(new EnvironmentConfig(__DIR__), 'queue');
        $cfg = $adapter->debugConfig();

        $this->assertSame('redis://11.11.11.11:6380', $cfg->dsn);
    }
}
