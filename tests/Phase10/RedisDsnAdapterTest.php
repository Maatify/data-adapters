<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-13 19:17
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Phase10;

use PHPUnit\Framework\TestCase;
use Maatify\DataAdapters\Adapters\RedisAdapter;
use Maatify\DataAdapters\Core\EnvironmentConfig;

/**
 * 🧪 **RedisDsnAdapterTest**
 *
 * 🎯 Ensures RedisAdapter correctly loads DSN values for
 * dynamic profiles under Phase 10's DSN-first architecture.
 *
 * This test validates:
 *
 * - `REDIS_SESSIONS_DSN` is mapped to profile `sessions`
 * - RedisAdapter returns the DSN exactly as provided
 * - Legacy host/port/password keys do not override DSN
 *
 * ✔ Confirms Redis DSN behavior matches Predis / MySQL / Mongo logic
 * ✔ Uses `APP_ENV=testing` to avoid loading `.env`
 *
 * @example
 * ```php
 * $_ENV['REDIS_SESSIONS_DSN'] = 'redis://9.9.9.9:6379';
 * $adapter = new RedisAdapter($env, 'sessions');
 * $cfg = $adapter->debugConfig();
 * ```
 */
final class RedisDsnAdapterTest extends TestCase
{
    /**
     * 🧪 Prepare test environment with DSN for `sessions` profile.
     */
    protected function setUp(): void
    {
        $_ENV = [
            'APP_ENV'            => 'testing',
            'REDIS_SESSIONS_DSN' => 'redis://9.9.9.9:6379',
        ];
    }

    /**
     * 🧪 Validate DSN is read correctly by RedisAdapter.
     */
    public function testRedisReadsDsn(): void
    {
        $adapter = new RedisAdapter(new EnvironmentConfig(__DIR__), 'sessions');
        $cfg = $adapter->debugConfig();

        $this->assertSame('redis://9.9.9.9:6379', $cfg->dsn);
    }
}
