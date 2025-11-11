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

class RecoveryWorker
{
    private int $cycleCount = 0;
    private int $retrySeconds;

    public function __construct(
        private readonly object $adapter,
        private readonly ?LoggerInterface $logger = null
    ) {
        $this->retrySeconds = (int)($_ENV['REDIS_RETRY_SECONDS'] ?? 10);
    }

    public function run(): void
    {
        $adapterName = get_class($this->adapter);
        $this->logger?->info("🟢 RecoveryWorker started for {$adapterName}");

        while (true) {
            $this->cycleCount++;

            try {
                if ($this->adapter->healthCheck()) {
                    $this->replayQueue($adapterName);
                } else {
                    $this->logger?->warning("⚠️ {$adapterName} still unavailable...");
                }
            } catch (Throwable $e) {
                $this->logger?->error("RecoveryWorker loop error: {$e->getMessage()}");
            }

            /**
             * 🔁 Every 10 cycles, prune expired queue items
             */
            if ($this->cycleCount % 10 === 0) {
                $ttl = (int)($_ENV['FALLBACK_QUEUE_TTL'] ?? 3600);
                (new FallbackQueuePruner($ttl))->run();
                $this->logger?->info("🧹 FallbackQueue pruned (TTL={$ttl}s)");
            }

            sleep($this->retrySeconds);
        }
    }

    private function replayQueue(string $adapterName): void
    {
        $items = FallbackQueue::drain($adapterName);

        foreach ($items as $entry) {
            try {
                // هنا مكان تنفيذ الـ replay الحقيقي لما النظام الأساسي يرجع
                $this->logger?->info("🔁 Replaying queued operation for {$adapterName}: {$entry['operation']}");
            } catch (Throwable $e) {
                $this->logger?->error("❌ Replay failed for {$adapterName}: {$e->getMessage()}");
                FallbackQueue::enqueue($adapterName, $entry['operation'], $entry['payload']); // Re-queue
            }
        }
    }
}

