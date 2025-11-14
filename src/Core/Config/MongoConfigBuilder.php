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

final readonly class MongoConfigBuilder
{
    public function __construct(private EnvironmentConfig $config)
    {
    }

    public function build(string $profile): ConnectionConfigDTO
    {
        $key = sprintf('MONGO_%s_DSN', strtoupper($profile));
        $dsn = $this->config->get($key);

        // No DSN → do NOT override BaseAdapter legacy logic
        if (empty($dsn)) {
            return new ConnectionConfigDTO();
        }

        // Parse DSN for host / port / database
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

    private function parseMongoDsn(string $dsn): array
    {
        // Supports both:
        // - mongodb://user:pass@host:27017/db
        // - mongodb+srv://host/db
        $url = parse_url($dsn);

        return [
            'host'     => $url['host'] ?? null,
            'port'     => isset($url['port']) ? (string)$url['port'] : null,
            'database' => isset($url['path']) ? ltrim($url['path'], '/') : null,
        ];
    }
}
