<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-11-08
 * Time: 21:12
 * Project: maatify:data-adapters
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Diagnostics;

use Maatify\DataAdapters\Contracts\AdapterInterface;
use Maatify\DataAdapters\Core\DatabaseResolver;
use Maatify\DataAdapters\Core\EnvironmentConfig;
use Maatify\DataAdapters\Enums\AdapterTypeEnum;

/**
 * ⚙️ Class DiagnosticService
 *
 * 🧩 Purpose:
 * Provides a centralized mechanism for performing diagnostic health checks
 * across multiple adapters (Redis, MongoDB, MySQL, etc.), returning results
 * in a unified and JSON-compatible structure.
 *
 * ✅ Features:
 * - Dynamically registers and tests multiple adapter types.
 * - Performs connection attempts and health checks.
 * - Catches and logs connection failures using {@see AdapterFailoverLog}.
 * - Returns formatted diagnostics suitable for API responses or dashboards.
 *
 * ⚙️ Example Usage:
 * ```php
 * $config   = new EnvironmentConfig(__DIR__ . '/../');
 * $resolver = new DatabaseResolver($config);
 * $diag     = new DiagnosticService($config, $resolver);
 *
 * $diag->register(['redis', 'mongo', 'mysql']);
 * echo $diag->toJson();
 * ```
 *
 * @package Maatify\DataAdapters\Diagnostics
 */
final class DiagnosticService
{
    /**
     * 🧠 Registered adapters for diagnostic checks.
     *
     * @var array<string, AdapterInterface> Indexed by adapter type name.
     */
    private array $adapters = [];

    /**
     * 🧩 Constructor
     *
     * Initializes the diagnostic service with a shared configuration and resolver.
     *
     * @param EnvironmentConfig $config   Environment configuration handler.
     * @param DatabaseResolver  $resolver Adapter resolver factory.
     */
    public function __construct(
        private readonly EnvironmentConfig $config,
        private readonly DatabaseResolver $resolver
    ) {}

    /**
     * 🎯 Register adapter types to be included in diagnostics.
     *
     * Supports both string identifiers (e.g. `'redis'`) and {@see AdapterTypeEnum} values.
     *
     * @param string[]|AdapterTypeEnum[] $types List of adapter identifiers or enum values.
     *
     * ✅ Example:
     * ```php
     * $diag->register(['redis', 'mysql']);
     * $diag->register([AdapterTypeEnum::Redis, AdapterTypeEnum::Mongo]);
     * ```
     */
    public function register(array $types): void
    {
        foreach ($types as $type) {
            // 🎯 Normalize to AdapterTypeEnum if given as string
            $enum = $type instanceof AdapterTypeEnum ? $type : AdapterTypeEnum::from(strtolower($type));

            // 🧠 Resolve adapter instance and store by type name
            $this->adapters[$enum->value] = $this->resolver->resolve($enum);
        }
    }

    /**
     * 🔍 Collect health information for all registered adapters.
     *
     * Performs a complete connection test cycle for each adapter:
     * - `connect()` → Establish connection.
     * - `healthCheck()` → Verify active connection.
     * - `disconnect()` → Safely close the session.
     *
     * Any exceptions encountered are logged via {@see AdapterFailoverLog}
     * for future analysis.
     *
     * @return array<int, array{
     *     adapter: string,
     *     connected: bool,
     *     error: string|null,
     *     timestamp: string
     * }> Structured diagnostics data.
     *
     * ✅ Example:
     * ```php
     * $results = $diag->collect();
     * print_r($results);
     * ```
     */
    public function collect(): array
    {
        $data = [];

        foreach ($this->adapters as $type => $adapter) {
            $status = false;
            $error  = null;

            try {
                $adapter->connect();
                $status = $adapter->healthCheck();
            } catch (\Throwable $e) {
                $error = $e->getMessage();
                AdapterFailoverLog::record($type, $error);
            } finally {
                $adapter->disconnect();
            }

            // 🧾 Append diagnostic result entry
            $data[] = [
                'adapter'   => $type,
                'connected' => $status,
                'error'     => $error,
                'timestamp' => date('Y-m-d H:i:s'),
            ];
        }

        return $data;
    }

    /**
     * 📦 Convert collected diagnostics into a formatted JSON string.
     *
     * Useful for returning structured results to monitoring systems,
     * web APIs, or CLI diagnostic tools.
     *
     * @return string JSON-encoded diagnostics report.
     *
     * ✅ Example:
     * ```php
     * echo $diag->toJson();
     * ```
     */
    public function toJson(): string
    {
        return json_encode(['diagnostics' => $this->collect()], JSON_PRETTY_PRINT);
    }
}
