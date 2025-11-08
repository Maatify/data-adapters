<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-11-08
 * Time: 20:30
 * Project: maatify:data-adapters
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Core;

use Dotenv\Dotenv;

/**
 * ⚙️ Class EnvironmentConfig
 *
 * 🧩 Purpose:
 * Provides a centralized and safe way to access environment variables across
 * all data adapters (Redis, MySQL, MongoDB, etc.), ensuring consistency in configuration loading.
 *
 * ✅ Features:
 * - Automatically loads `.env` file if available.
 * - Supports fallback to system environment variables.
 * - Prevents missing configuration issues during adapter initialization.
 *
 * ⚙️ Example Usage:
 * ```php
 * $env = new EnvironmentConfig(__DIR__ . '/../');
 * $host = $env->get('REDIS_HOST', '127.0.0.1');
 * if ($env->has('REDIS_PASSWORD')) {
 *     echo "Password is set.";
 * }
 * ```
 *
 * @package Maatify\DataAdapters\Core
 */
final readonly class EnvironmentConfig
{
    /**
     * 🧠 Constructor
     *
     * Automatically loads environment variables from the given project root.
     *
     * @param string $root The base directory path containing the `.env` file.
     */
    public function __construct(private string $root)
    {
        // 🔹 Load environment variables if `.env` file exists
        if (file_exists($this->root . '/.env')) {
            Dotenv::createImmutable($this->root)->load();
        }
    }

    /**
     * 🔍 Retrieve the value of a specific environment variable.
     *
     * Checks first in `$_ENV`, then in system environment variables.
     * Returns the provided default value if not found.
     *
     * @param string $key     The name of the environment variable.
     * @param string|null $default Optional default value if the key does not exist.
     *
     * @return string|null The variable’s value or the default.
     */
    public function get(string $key, ?string $default = null): ?string
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    /**
     * ✅ Check if an environment variable is defined.
     *
     * @param string $key The name of the environment variable.
     * @return bool True if the variable exists, false otherwise.
     */
    public function has(string $key): bool
    {
        return isset($_ENV[$key]) || getenv($key) !== false;
    }
}
