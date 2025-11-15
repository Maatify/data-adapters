<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-15 19:58
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Core\Config;

use Exception;
use JsonException;

final class RegistryConfig
{
    private ?array $registry = null;
    private ?string $path = null;

    /**
     * @throws Exception
     */
    public function setPath(string $path): void
    {
        $real = realpath($path);
        if (! $real || ! is_readable($real)) {
            throw new Exception("Registry path is invalid or unreadable: {$path}");
        }

        $this->path = $real;
        $this->registry = null; // force reload
    }

    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * @throws JsonException
     * @throws Exception
     */
    public function load(): array
    {
        if ($this->registry !== null) {
            return $this->registry;
        }

        if (! $this->path) {
            return $this->registry = [];
        }

        $json = file_get_contents($this->path);
        if ($json === false) {
            throw new Exception("Unable to read registry file: {$this->path}");
        }

        $data = json_decode($json, true, 512, JSON_THROW_ON_ERROR);

        if (! isset($data['databases'])) {
            throw new Exception("Invalid registry format: missing 'databases' root node");
        }

        return $this->registry = $data;
    }

    public function reload(): void
    {
        $this->registry = null;
    }
}
