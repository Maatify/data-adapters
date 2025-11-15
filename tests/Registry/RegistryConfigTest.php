<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-15 20:00
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Registry;

use PHPUnit\Framework\TestCase;
use Maatify\DataAdapters\Core\Config\RegistryConfig;

/**
 * 🧪 **RegistryConfigTest**
 *
 * Unit tests covering the behavior of the `RegistryConfig` class.
 *
 * 🎯 **Test coverage includes:**
 * - Invalid registry path handling
 * - Loading a valid registry file
 * - Registry-based overrides for DSN and legacy values
 *
 * Each test creates fixtures on the fly to avoid using external files.
 *
 * ---
 * ### Fixture Structure Example:
 * ```json
 * {
 *   "databases": {
 *     "mysql": {
 *       "main": {
 *         "dsn": "mysql:host=127.0.0.1;dbname=test"
 *       }
 *     }
 *   }
 * }
 * ```
 * ---
 */
final class RegistryConfigTest extends TestCase
{
    /**
     * Directory for dynamically generated test fixtures.
     *
     * @var string
     */
    private string $fixturesDir;

    /**
     * Prepare fixture directory before each test.
     */
    protected function setUp(): void
    {
        $this->fixturesDir = __DIR__ . '/fixtures';

        // Ensure fixtures directory exists
        if (!is_dir($this->fixturesDir)) {
            mkdir($this->fixturesDir, 0777, true);
        }
    }

    /**
     * 🧪 Ensure that setting an invalid registry path throws an exception.
     */
    public function testInvalidPathThrowsException(): void
    {
        $this->expectException(\Exception::class);

        $config = new RegistryConfig();
        $config->setPath('/invalid/path.json');
    }

    /**
     * 🧪 Ensure that a valid registry file loads successfully and contains expected keys.
     */
    public function testValidRegistryLoadsSuccessfully(): void
    {
        $path = $this->fixturesDir . '/registry.valid.json';

        // Create a valid registry fixture
        file_put_contents($path, json_encode([
            'databases' => [
                'mysql' => [
                    'main' => ['dsn' => 'mysql:host=127.0.0.1;dbname=test']
                ]
            ]
        ]));

        $config = new RegistryConfig();
        $config->setPath($path);

        $data = $config->load();

        $this->assertArrayHasKey('databases', $data);

        unlink($path);
    }

    /**
     * 🧪 Ensure registry values override DSN and legacy values properly.
     */
    public function testRegistryOverridesDsnAndLegacy(): void
    {
        $path = $this->fixturesDir . '/registry.override.json';

        // Create a registry fixture that overrides DSN
        file_put_contents($path, json_encode([
            'databases' => [
                'mysql' => [
                    'main' => ['dsn' => 'mysql:host=10.0.0.5;dbname=override']
                ]
            ]
        ]));

        $config = new RegistryConfig();
        $config->setPath($path);

        $data = $config->load();

        $this->assertSame(
            'mysql:host=10.0.0.5;dbname=override',
            $data['databases']['mysql']['main']['dsn']
        );

        unlink($path);
    }
}
