<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm)
 * @since       2025-11-08 21:12
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Diagnostics;

use Maatify\Common\Contracts\Adapter\AdapterInterface;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Core\EnvironmentConfig;

final class DiagnosticService
{
    /**
     * @var array<string, AdapterInterface>
     */
    private array $adapters = [];

    public function __construct(
        private readonly EnvironmentConfig $config,
        private readonly DatabaseResolver  $resolver
    ) {}

    /**
     * Register adapters for diagnostics.
     *
     * @param array<int, string> $types
     * Example:
     *   ["mysql", "mysql.main", "redis", "mongo.logs"]
     */
    public function register(array $types): void
    {
        foreach ($types as $type) {

            // 👉 Normalize (Phase 10 requirement)
            $key = strtolower(trim($type));

            // 👉 String-based resolution (Phase 10 routing)
            $this->adapters[$key] = $this->resolver->resolve($key);
        }
    }

    /**
     * Run health checks on all registered adapters.
     */
    public function collect(): array
    {
        $results = [];

        foreach ($this->adapters as $key => $adapter) {

            $connected = false;
            $error     = null;

            try {
                // Attempt connection
                $adapter->connect();
                $connected = $adapter->healthCheck();
            } catch (\Throwable $e) {
                $error = $e->getMessage();

                // 🔥 Old logging system (static)
                AdapterFailoverLog::record($key, $error);
            } finally {
                $adapter->disconnect();
            }

            $results[] = [
                'adapter'   => $key,
                'connected' => $connected,
                'error'     => $error,
                'timestamp' => date('Y-m-d H:i:s'),
            ];
        }

        return $results;
    }

    /**
     * Return diagnostics as JSON.
     */
    public function toJson(): string
    {
        return json_encode(
            ['diagnostics' => $this->collect()],
            JSON_PRETTY_PRINT
        );
    }

    public function getAdapters(): array
    {
        return $this->adapters;
    }

    public function getConfig(): EnvironmentConfig
    {
        return $this->config;
    }
}
