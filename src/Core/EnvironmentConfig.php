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

/**
 * 🧩 **Class EnvironmentConfig**
 *
 * 🎯 **Purpose:**
 * Centralized configuration handler responsible for safely loading
 * and accessing environment variables (`.env`) for all Maatify libraries.
 *
 * 🧠 **Core Behavior:**
 * - Automatically loads `.env` only if environment variables are not yet initialized.
 * - Acts as a consistent accessor layer for configuration values across libraries.
 * - Provides safe retrieval methods (`get`, `has`, `all`) to prevent missing variable issues.
 *
 * ✅ **Example Usage:**
 * ```php
 * use Maatify\DataAdapters\Core\EnvironmentConfig;
 *
 * $env = new EnvironmentConfig(__DIR__ . '/../');
 * $dbHost = $env->get('DB_HOST', '127.0.0.1');
 * if ($env->has('REDIS_URL')) {
 *     echo "Redis connected via: " . $env->get('REDIS_URL');
 * }
 * ```
 *
 * 🧩 **Typical Use Cases:**
 * - When bootstrapping database, cache, or API adapters.
 * - To safely read environment configuration in CLI or testing contexts.
 */
final readonly class EnvironmentConfig
{
    /**
     * 🧱 **Constructor**
     *
     * Initializes the environment configuration.
     * Automatically triggers the environment loader only once per process,
     * avoiding redundant `.env` parsing.
     *
     * @param   string  $root  Root directory of the application or project (where `.env` resides).
     *
     * @throws Exception
     */
    public function __construct(private string $root)
    {
        // 🧠 Initialize only once and only if not already loaded
        if (empty($_ENV['APP_ENV'])) {
            $loader = new EnvironmentLoader($this->root);
            $loader->load();
        }
    }

    /**
     * 🔍 **Retrieve a variable safely**
     *
     * Fetches the value of an environment variable with optional fallback.
     * Checks both `$_ENV` and `getenv()` to ensure compatibility across systems.
     *
     * @param string      $key      Environment variable name.
     * @param string|null $default  Default value if not found.
     *
     * @return string|null Returns the variable value, or `$default` if missing.
     *
     * ✅ **Example:**
     * ```php
     * $env->get('DB_PASSWORD', 'secret');
     * ```
     */
    public function get(string $key, ?string $default = null): ?string
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    /**
     * ✅ **Check if a variable exists**
     *
     * Verifies whether the specified environment key exists in either
     * the current process environment or system-level configuration.
     *
     * @param string $key Environment variable key.
     *
     * @return bool Returns `true` if the variable exists, otherwise `false`.
     *
     * ✅ **Example:**
     * ```php
     * if ($env->has('APP_DEBUG')) {
     *     echo "Debug mode enabled";
     * }
     * ```
     */
    public function has(string $key): bool
    {
        return isset($_ENV[$key]) || getenv($key) !== false;
    }

    /**
     * 🧾 **Retrieve all environment variables**
     *
     * Returns the current environment map for debugging or inspection.
     * Should not be exposed in production environments.
     *
     * @return array<string, string> Returns all environment variables.
     *
     * ✅ **Example:**
     * ```php
     * print_r($env->all());
     * ```
     */
    public function all(): array
    {
        return $_ENV;
    }
}
