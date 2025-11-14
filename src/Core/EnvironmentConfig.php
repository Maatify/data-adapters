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


final readonly class EnvironmentConfig
{

    /**
     * @throws Exception
     */
    public function __construct(private string $root)
    {
        /**
         * 🔒 Smart Env Loader Logic:
         *
         * - إذا bootstrap حمّل env → APP_ENV موجود → لا تعمل load
         * - إذا tests تشغّل → APP_ENV=testing → لا تعمل load
         * - إذا مشروع خارجي يستخدم المكتبة بدون bootstrap → APP_ENV غير موجود → المكتبة تحمل env
         */

        $appEnv = $_ENV['APP_ENV'] ?? getenv('APP_ENV') ?: null;

        // 🧠 Bootstrap already loaded → skip
        if ($appEnv && $appEnv !== 'testing') {
            return; // Already loaded externally
        }

        // 🧪 Testing → NEVER load .env
        if ($appEnv === 'testing') {
            return;
        }

        // 🟢 No environment loaded → load now
        $loader = new EnvironmentLoader($this->root);
        $loader->load();
    }

    public function get(string $key, ?string $default = null): ?string
    {
        return $_ENV[$key] ?? getenv($key) ?: $default;
    }

    public function has(string $key): bool
    {
        return isset($_ENV[$key]) || getenv($key) !== false;
    }

    public function all(): array
    {
        return $_ENV;
    }

    /**
     * ------------------------------------------------------
     * PHASE 11 — Unified MySQL Profile Resolution Entry Point
     * ------------------------------------------------------
     */
    public function getMySQLConfig(?string $profile): ConnectionConfigDTO
    {
        $builder = new MySqlConfigBuilder($this);
        return $builder->build($profile ?? 'main');
    }
}