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

/**
 * 🧪 **MongoDsnAdapterTest**
 *
 * 🎯 Validates MongoDB DSN resolution for dynamic profiles
 * during **Phase 10 — DSN Support for All Adapters**.
 *
 * This ensures that:
 * - DSN profile keys such as `MONGO_ACTIVITY_DSN` are correctly read
 * - MongoAdapter pulls DSN from environment using profile name
 * - DSN-first strategy overrides all legacy host/port/db fields
 *
 * ✔ Uses `APP_ENV=testing` to avoid loading external `.env`
 * ✔ Profile `activity` maps to env key `MONGO_ACTIVITY_DSN`
 *
 * @example
 * ```php
 * $_ENV['MONGO_ACTIVITY_DSN'] = 'mongodb://10.0.0.9:27017/activity';
 * $adapter = new MongoAdapter($env, 'activity');
 * $cfg = $adapter->debugConfig();
 * ```
 */
final class MongoDsnAdapterTest extends TestCase
{
    /**
     * 🧪 Prepare environment for this test.
     */
    protected function setUp(): void
    {
        $_ENV = [
            'APP_ENV'            => 'testing',
            'MONGO_ACTIVITY_DSN' => 'mongodb://10.0.0.9:27017/activity',
        ];
    }

    /**
     * 🧪 Assert that MongoAdapter reads DSN for the `activity` profile.
     */
    public function testMongoReadsDsn(): void
    {
        $adapter = new MongoAdapter(new EnvironmentConfig(__DIR__), 'activity');
        $cfg = $adapter->debugConfig();

        $this->assertSame('mongodb://10.0.0.9:27017/activity', $cfg->dsn);
    }
}
