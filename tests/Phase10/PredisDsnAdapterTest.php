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

/**
 * 🧪 **PredisDsnAdapterTest**
 *
 * 🎯 Verifies PredisAdapter’s DSN-first parsing logic introduced in Phase 10.
 *
 * Ensures:
 * - DSN profile `REDIS_QUEUE_DSN` is correctly mapped to profile `queue`
 * - PredisAdapter loads DSN directly without falling back to legacy host/port
 * - DSN is preserved exactly as defined in the environment
 *
 * ✔ Uses `APP_ENV=testing` to bypass `.env` loading
 * ✔ Confirms redis DSN behavior matches MySQL/Mongo logic
 *
 * @example
 * ```php
 * $_ENV['REDIS_QUEUE_DSN'] = 'redis://11.11.11.11:6380';
 * $adapter = new PredisAdapter($env, 'queue');
 * $cfg = $adapter->debugConfig();
 * ```
 */
final class PredisDsnAdapterTest extends TestCase
{
    /**
     * 🧪 Mock environment before each test.
     */
    protected function setUp(): void
    {
        $_ENV = [
            'APP_ENV'         => 'testing',
            'REDIS_QUEUE_DSN' => 'redis://11.11.11.11:6380',
        ];
    }

    /**
     * 🧪 Ensure PredisAdapter reads DSN for queue profile.
     */
    public function testPredisReadsDsn(): void
    {
        $adapter = new PredisAdapter(new EnvironmentConfig(__DIR__), 'queue');
        $cfg = $adapter->debugConfig();

        $this->assertSame('redis://11.11.11.11:6380', $cfg->dsn);
    }
}
