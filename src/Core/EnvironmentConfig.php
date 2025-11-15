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
use Maatify\DataAdapters\Core\Config\RegistryConfig;

final readonly class EnvironmentConfig
{
    private RegistryConfig $registry;
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
            $this->registry = new RegistryConfig();
            $this->initializeRegistry();
            return;
        }

        // 🧪 Testing mode → NEVER load `.env`
        if ($appEnv === 'testing') {
            $this->registry = new RegistryConfig();
            $this->initializeRegistry();
            return;
        }

        // 🟢 No environment loaded yet → load now through Bootstrap loader
        $loader = new EnvironmentLoader($this->root);
        $loader->load();

        // Initialize registry after loading ENV
        $this->registry = new RegistryConfig();
        $this->initializeRegistry();
    }

    /**
     * 🔧 Phase 13 — Initialize registry path from ENV if provided
     */
    private function initializeRegistry(): void
    {
        $envPath = $_ENV['DB_REGISTRY_PATH'] ?? getenv('DB_REGISTRY_PATH') ?: null;

        if ($envPath) {
            try {
                $this->registry->setPath($envPath);
            } catch (Exception) {
                // silent fail — registry is optional by design
            }
        }
    }

    public function get(string $key, ?string $default = null): ?string
    {
        if (!empty($_ENV) && array_key_exists('APP_ENV', $_ENV) && $_ENV['APP_ENV'] === 'testing') {
            // In tests: ONLY use $_ENV
            return $_ENV[$key] ?? $default;
        }

        // Highest priority → $_ENV (test overrides, runtime overrides)
        if (array_key_exists($key, $_ENV)) {
            return $_ENV[$key];
        }

        // Second priority → system environment (CI, Docker, OS)
        $val = getenv($key);
        if ($val !== false) {
            return $val;
        }

        // Fallback
        return $default;
    }

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

    public function getMySQLConfig(?string $profile): ConnectionConfigDTO
    {
        $profile = $profile ?: 'main'; // 🔥 enforce profile=main always
        $builder = new MySqlConfigBuilder($this);
        return $builder->build($profile);
    }

    public function setRegistryPath(string $path): void
    {
        $this->registry->setPath($path);
    }

    public function getRegistryPath(): ?string
    {
        return $this->registry->getPath();
    }

    public function loadRegistry(): array
    {
        return $this->registry->load();
    }

    public function reloadRegistry(): void
    {
        $this->registry->reload();
    }

    public function mergeWithRegistry(string $type, string $profile, array $dsn, array $legacy): array
    {
        $registry = $this->loadRegistry();
        $reg = $registry['databases'][$type][$profile] ?? [];

        return array_merge($legacy, $dsn, $reg);
    }
}
