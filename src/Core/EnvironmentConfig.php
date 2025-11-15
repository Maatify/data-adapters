<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim
 * @since       2025-11-08
 * @link        https://github.com/Maatify/data-adapters
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Core;

use Exception;
use Maatify\Bootstrap\Core\EnvironmentLoader;
use Maatify\Common\DTO\ConnectionConfigDTO;
use Maatify\DataAdapters\Core\Config\MySqlConfigBuilder;

/**
 * 🧩 **Class EnvironmentConfig**
 *
 * 🎯 Provides a smart environment loader for the entire Maatify Data-Adapters
 * ecosystem. This class acts as a thin abstraction around `$_ENV` with
 * additional logic that ensures environment variables are loaded exactly once
 * and in the correct context (Bootstrap, tests, external library usage).
 *
 * ### 🧠 Smart Loading Behavior:
 * - If **Bootstrap already loaded the environment**, skip loading.
 * - If **running under PHPUnit tests**, never load `.env`.
 * - If **library used standalone** (no bootstrap), automatically load `.env`.
 *
 * ✔ Guarantees consistent and predictable environment access
 * ✔ Avoids duplicate loading between Maatify Bootstrap / external apps / CLI
 * ✔ Supports dynamic profile-based MySQL resolution (Phase 11)
 *
 * @example Basic usage:
 * ```php
 * $env = new EnvironmentConfig(__DIR__);
 * $host = $env->get('MYSQL_HOST');
 * ```
 */
final readonly class EnvironmentConfig
{
    /**
     * @param string $root Project root directory passed to EnvironmentLoader
     *
     * @throws Exception
     */
    public function __construct(private string $root)
    {
        /**
         * 🔒 Smart Env Loader Logic:
         *
         * - If bootstrap already loaded → `APP_ENV` exists → skip loading
         * - If running tests → `APP_ENV=testing` → skip loading
         * - If external project using the library → no `APP_ENV` → this class loads `.env`
         */

        $appEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: null;

        // 🧠 Bootstrap has already loaded environment → do nothing
        if ($appEnv && $appEnv !== 'testing') {
            return;
        }

        // 🧪 Testing mode → NEVER load `.env`
        if ($appEnv === 'testing') {
            return;
        }

        // 🟢 No environment loaded yet → load now through Bootstrap loader
        $loader = new EnvironmentLoader($this->root);
        $loader->load();
    }

    /**
     * 🎯 **Get environment variable**
     *
     * Wrapper that checks:
     * - Direct `$_ENV`
     * - Fallback to `getenv()`
     * - Fallback to default value
     *
     * @param string      $key     Environment variable name
     * @param string|null $default Default value if not found
     *
     * @return string|null
     */
    public function get(string $key, ?string $default = null): ?string
    {
        // --------------------------------------------------------
        // 🟦 MOCK MODE (used in resolver tests, builder tests, etc)
        // --------------------------------------------------------
        if (!empty($_ENV['MAATIFY_FAKE_ENV'])) {
            // In mock tests: ignore getenv(), ignore system env, ignore .env.example
            return $_ENV[$key] ?? $default;
        }

        // --------------------------------------------------------
        // 🟩 TEST MODE (Real Integration)
        // Only read putenv() + CI env, NOT .env files
        // --------------------------------------------------------
        if (($_ENV['APP_ENV'] ?? null) === 'testing') {
            // priority:
            // 1) putenv()/getenv()
            // 2) $_ENV
            // 3) default
            $val = getenv($key);
            if ($val !== false) {
                return $val;
            }
            return $_ENV[$key] ?? $default;
        }

        // --------------------------------------------------------
        // 🟢 NORMAL MODE (.env or .env.local)
        // --------------------------------------------------------
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        $val = getenv($key);
        if ($val !== false) {
            return $val;
        }

        return $default;
    }

    /**
     * 🧪 **Check if environment key exists**
     *
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($_ENV[$key]) || getenv($key) !== false;
    }

    /**
     * 📦 **Return all loaded environment variables**
     *
     * @return array<string,string>
     */
    public function all(): array
    {
        return $_ENV;
    }

    /**
     * ------------------------------------------------------------
     * 🧩 PHASE 11 — Unified MySQL Profile Resolution Entry Point
     * ------------------------------------------------------------
     *
     * Provides a single call that returns a DSN-aware MySQL configuration DTO
     * for any profile (`main`, `billing`, `logs`, etc.)
     *
     * @param string|null $profile MySQL profile name
     *
     * @return ConnectionConfigDTO Parsed configuration DTO
     */
    public function getMySQLConfig(?string $profile): ConnectionConfigDTO
    {
        $builder = new MySqlConfigBuilder($this);
        return $builder->build($profile ?? 'main');
    }
}
