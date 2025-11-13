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
use Maatify\DataAdapters\Adapters\MongoAdapter;
use Maatify\DataAdapters\Core\EnvironmentConfig;

final class MongoDsnAdapterTest extends TestCase
{
    protected function setUp(): void
    {
        $_ENV = [
            'APP_ENV'            => 'testing',
            'MONGO_ACTIVITY_DSN' => 'mongodb://10.0.0.9:27017/activity',
        ];
    }

    public function testMongoReadsDsn(): void
    {
        $adapter = new MongoAdapter(new EnvironmentConfig(__DIR__), 'activity');
        $cfg = $adapter->debugConfig();

        $this->assertSame('mongodb://10.0.0.9:27017/activity', $cfg->dsn);
    }
}
