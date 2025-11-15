<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-15 00:02
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);


namespace Maatify\DataAdapters\Core\Config;

use Maatify\Common\DTO\ConnectionConfigDTO;
use Maatify\DataAdapters\Core\EnvironmentConfig;

/**
 * 🧩 **Class MongoConfigBuilder**
 *
 * 🎯 Responsible for building profile-based MongoDB configuration objects
 * using DSN-first resolution, in alignment with the unified Maatify
 * Data-Adapters architecture.
 *
 * ✔ Supports **DSN per profile**, e.g.:
 * ```
 * MONGO_MAIN_DSN=mongodb://127.0.0.1:27017/mydb
 * MONGO_LOGS_DSN=mongodb://127.0.0.1:27017/logs
 * ```
 *
 * ✔ DSN parsing includes:
 * - Host
 * - Port
 * - Database name
 * - Works with both `mongodb://` and `mongodb+srv://` formats
 *
 * ✔ If DSN is not provided for the given profile:
 * → Returns an empty DTO so the legacy BaseAdapter logic remains in control.
 *
 * @example
 * ```php
 * $builder = new MongoConfigBuilder($env);
 * $config  = $builder->build('main');
 * ```
 */
final readonly class MongoConfigBuilder
{
    /**
     * @var EnvironmentConfig $config
     * Environment loader used to fetch DSN values from the environment.
     */
    public function __construct(private EnvironmentConfig $config)
    {
    }

    /**
     * 🎯 **Build connection configuration for a given Mongo profile**
     *
     * Resolves a DSN environment variable in the format:
     * `MONGO_{PROFILE}_DSN`
     *
     * - If DSN is missing → returns a "blank" config so BaseAdapter legacy config applies.
     * - If DSN exists → parses host, port, and database name.
     *
     * @param string $profile Connection profile name (e.g. "main", "logs")
     *
     * @return ConnectionConfigDTO Parsed connection configuration
     */
    public function build(string $profile): ConnectionConfigDTO
    {
        $key = sprintf('MONGO_%s_DSN', strtoupper($profile));
        $dsn = $this->config->get($key);

        // 🧩 No DSN → Do NOT override BaseAdapter legacy logic
        if (empty($dsn)) {
            return new ConnectionConfigDTO();
        }

        // 🧠 Parse DSN for host, port, database
        $parsed = $this->parseMongoDsn($dsn);

        return new ConnectionConfigDTO(
            dsn     : $dsn,
            host    : $parsed['host'] ?? null,
            port    : $parsed['port'] ?? null,
            database: $parsed['database'] ?? null,
            driver  : 'mongo',
            profile : $profile,
        );
    }

    /**
     * 🧩 **Parse a MongoDB DSN string**
     *
     * Supports both:
     * - `mongodb://user:pass@host:27017/db`
     * - `mongodb+srv://host/db`
     *
     * Extracts:
     * - host
     * - port
     * - database name
     *
     * @param string $dsn Full MongoDB URI
     *
     * @return array Parsed values (`host`, `port`, `database`)
     */
    private function parseMongoDsn(string $dsn): array
    {
        $url = parse_url($dsn);

        return [
            'host'     => $url['host'] ?? null,
            'port'     => isset($url['port']) ? (string)$url['port'] : null,
            'database' => isset($url['path']) ? ltrim($url['path'], '/') : null,
        ];
    }
}
