<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-11 18:20
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Fallback;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Diagnostics\AdapterFailoverLog;
use Maatify\DataAdapters\Enums\AdapterTypeEnum;
use Psr\Log\LoggerInterface;
use Throwable;

final class FallbackManager
{
    private ?LoggerInterface $logger;
    private DatabaseResolver $resolver;

    /** @var array<string, string>  Stores temporary active fallback adapters */
    private array $activeFallbacks = [];

    public function __construct(DatabaseResolver $resolver, ?LoggerInterface $logger = null)
    {
        $this->resolver = $resolver;
        $this->logger   = $logger;
    }

    public function checkHealth(AdapterInterface $adapter): bool
    {
        try {
            return $adapter->healthCheck();
        } catch (Throwable $e) {
            $this->log(get_class($adapter), 'Health check failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * 🔄 Activate fallback for a given primary adapter.
     */
    public function activateFallback(string $primary, string $fallback): void
    {
        AdapterFailoverLog::record($primary, "Fallback activated → {$fallback}");
        $this->log($primary, "⚠️ Fallback activated → {$fallback}");
        $this->activeFallbacks[$primary] = $fallback;
    }

    /**
     * ♻️ Restore original primary adapter.
     */
    public function restorePrimary(string $adapterName): void
    {
        AdapterFailoverLog::record($adapterName, "Primary adapter restored");
        $this->log($adapterName, "✅ Primary adapter restored");
        unset($this->activeFallbacks[$adapterName]);
    }

    /**
     * 🧩 Resolve the currently active adapter (primary or fallback)
     */
    public function resolveActive(AdapterTypeEnum $type, bool $autoConnect = true): AdapterInterface
    {
        $name = $type->name;

        // if fallback exists → resolve fallback adapter instead
        if (isset($this->activeFallbacks[$name])) {
            $fallback = $this->activeFallbacks[$name];
            $this->log($name, "🔁 Resolving fallback adapter {$fallback}");
            return $this->resolver->resolve(AdapterTypeEnum::from(strtoupper($fallback)), $autoConnect);
        }

        // otherwise → normal resolution
        return $this->resolver->resolve($type, $autoConnect);
    }

    private function log(string $adapter, string $msg): void
    {
        $this->logger?->info("[{$adapter}] {$msg}");
    }
}
