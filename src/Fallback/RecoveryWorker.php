<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-11 18:21
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Fallback;

use Psr\Log\LoggerInterface;
use Throwable;

/**
 * ⚙️ **Class RecoveryWorker**
 *
 * 🧩 **Purpose:**
 * Continuously monitors fallback-enabled adapters (like Redis, Mongo, MySQL),
 * detects when the primary connection is restored, and automatically replays
 * any queued operations from {@see FallbackQueue}.
 *
 * ✅ **Features:**
 * - Designed for long-running CLI workers or scheduled cron jobs.
 * - Performs continuous health monitoring of the adapter.
 * - Replays queued operations once the adapter becomes healthy again.
 * - Periodically purges expired queue entries to prevent memory bloat.
 * - Fully instrumented with PSR-3 logging for observability.
 *
 * ✅ **Example Usage:**
 * ```php
 * $worker = new RecoveryWorker($redisAdapter, $logger);
 * $worker->run(); // Continuous monitoring loop
 * ```
 */
final class RecoveryWorker
{
    /** 🔁 Counter for completed monitoring cycles. */
    private int $cycleCount = 0;

    /** ⏱ Delay between health checks (seconds). */
    private int $retrySeconds;

    /**
     * 🧩 Constructor
     *
     * @param object               $adapter The fallback-enabled adapter instance.
     * @param LoggerInterface|null $logger  Optional PSR logger for diagnostic output.
     */
    public function __construct(
        private readonly object $adapter,
        private readonly ?LoggerInterface $logger = null
    ) {
        $this->retrySeconds = (int) ($_ENV['REDIS_RETRY_SECONDS'] ?? 10);
    }

    /**
     * 🚀 **Main Execution Loop**
     *
     * Runs an infinite cycle that:
     * - Performs periodic health checks.
     * - Replays queued fallback operations when possible.
     * - Prunes expired queue items every N cycles.
     *
     * @return void
     */
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

            // 🧹 Every 10 cycles → prune expired queue entries
            if ($this->cycleCount % 10 === 0) {
                $ttl = (int) ($_ENV['FALLBACK_QUEUE_TTL'] ?? 3600);
                (new FallbackQueuePruner($ttl))->run();
                $this->logger?->info("🧹 FallbackQueue pruned (TTL={$ttl}s)");
            }

            sleep($this->retrySeconds);
        }
    }

    /**
     * 🔁 **Replay Queued Operations**
     *
     * When the adapter becomes healthy again, this method replays all
     * previously failed operations that were queued during fallback mode.
     *
     * @param string $adapterName Adapter identifier for queued items.
     *
     * @return void
     */
    private function replayQueue(string $adapterName): void
    {
        $items = FallbackQueue::drain($adapterName);

        foreach ($items as $entry) {
            try {
                $this->logger?->info("🔁 Replaying queued operation for {$adapterName}: {$entry['operation']}");
                // 🧠 Future enhancement: execute callback once replay logic is implemented.
                // ($entry['payload']['callback'])();
            } catch (Throwable $e) {
                $this->logger?->error("❌ Replay failed for {$adapterName}: {$e->getMessage()}");
                // 🔁 Re-enqueue failed replay to ensure no operation is lost.
                FallbackQueue::enqueue($adapterName, $entry['operation'], $entry['payload']);
            }
        }
    }
}
