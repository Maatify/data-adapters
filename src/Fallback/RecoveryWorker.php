<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-11 18:21
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Fallback;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataAdapters\Diagnostics\AdapterFailoverLog;
use Psr\Log\LoggerInterface;
use Throwable;


/**
 * ⚙️ Class RecoveryWorker
 *
 * 🎯 Purpose:
 * Periodically monitors the health of fallback-enabled adapters
 * and replays any queued operations once the primary adapter recovers.
 *
 * ✅ Features:
 * - Runs as long-running process or cron job.
 * - Checks adapter health periodically.
 * - Replays queued operations via FallbackQueue.
 * - Logs replay success or failure with PSR logger and AdapterFailoverLog.
 */
final readonly class RecoveryWorker
{
    public function __construct(
        private AdapterInterface $adapter,
        private ?LoggerInterface $logger = null,
        private int $retrySeconds = 10
    ) {}

    /**
     * 🚀 Run the worker loop.
     *
     * Checks adapter health periodically and triggers queue replay.
     */
    public function run(): void
    {
        $adapterName = get_class($this->adapter);

        $this->logger?->info("🟢 RecoveryWorker started for {$adapterName}");

        while (true) {
            try {
                if ($this->adapter->healthCheck()) {
                    $this->replayQueue($adapterName);
                } else {
                    $this->logger?->warning("⚠️ {$adapterName} still unavailable...");
                }
            } catch (Throwable $e) {
                $this->logger?->error("RecoveryWorker loop error: {$e->getMessage()}");
            }

            sleep($this->retrySeconds);
        }
    }

    /**
     * ♻️ Replays queued operations for the specified adapter.
     */
    private function replayQueue(string $adapterName): void
    {
        $items = FallbackQueue::drain($adapterName);
        if (empty($items)) {
            $this->logger?->info("🕓 No queued items for {$adapterName}");
            return;
        }

        $this->logger?->info("🔁 Replaying " . count($items) . " queued operations for {$adapterName}");

        foreach ($items as $item) {
            try {
                $callback = $item['payload']['callback'] ?? null;

                if (is_callable($callback)) {
                    $callback();
                    AdapterFailoverLog::record($adapterName, "✅ Replayed {$item['operation']}");
                    $this->logger?->info("✅ Replayed {$item['operation']} successfully");
                } else {
                    AdapterFailoverLog::record($adapterName, "⚠️ Missing callback for {$item['operation']}");
                    $this->logger?->warning("⚠️ Missing callback for {$item['operation']}");
                }
            } catch (Throwable $e) {
                AdapterFailoverLog::record($adapterName, "❌ Replay failed: {$e->getMessage()}");
                $this->logger?->error("❌ Replay failed: {$e->getMessage()}");
            }
        }
    }
}
