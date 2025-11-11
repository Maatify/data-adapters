<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-11 19:42
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Enums\AdapterTypeEnum;
use Maatify\DataAdapters\Fallback\FallbackManager;

require __DIR__ . '/../../vendor/autoload.php';

// 🧱 Load environment configuration
$config = new EnvironmentConfig(__DIR__ . '/../../');

// 🧩 Resolve the Redis adapter (auto-connect)
$resolver = new DatabaseResolver($config);
$redis = $resolver->resolve(AdapterTypeEnum::REDIS, autoConnect: true);

// 🧠 Initialize Fallback Manager
$fallback = new FallbackManager($resolver);

// 🩺 Check adapter health and activate fallback if necessary
if (! $fallback->checkHealth($redis)) {
    echo "⚠️  Redis adapter unhealthy — activating Predis fallback...\n";
    $fallback->activateFallback('RedisAdapter', 'PredisAdapter');
} else {
    echo "✅  Redis adapter healthy.\n";
}

// 🧪 Simulate a failed write — will be automatically queued
try {
    echo "➡️  Performing SET operation...\n";
    $redis->getConnection()->set('demo:key', 'value');
    echo "✅  Operation successful.\n";
} catch (Throwable $e) {
    echo "⚠️  Operation failed — automatically queued by BaseAdapter::handleFailure()\n";
}
