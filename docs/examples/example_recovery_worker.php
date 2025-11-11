<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-11 19:43
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Enums\AdapterTypeEnum;
use Maatify\DataAdapters\Fallback\RecoveryWorker;
use Maatify\PsrLogger\LoggerFactory;

require __DIR__ . '/../../vendor/autoload.php';

// 🧱 Load configuration
$config = new EnvironmentConfig(__DIR__ . '/../../');

// 🧩 Resolve Redis adapter
$resolver = new DatabaseResolver($config);
$redis = $resolver->resolve(AdapterTypeEnum::REDIS, autoConnect: true);

// 🧾 Optional: setup PSR logger (maatify/psr-logger)
$logger = (new LoggerFactory())->create('recovery');

// 🔁 Start recovery worker
echo "🕓 Starting RecoveryWorker (retry every 10 seconds)...\n";
$worker = new RecoveryWorker($redis, $logger, retrySeconds: 10);
$worker->run(); // Keeps running forever