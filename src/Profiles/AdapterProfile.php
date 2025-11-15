<?php
/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-14 15:26
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Profiles;

use InvalidArgumentException;

/**
 * 🧩 **Class AdapterProfile**
 *
 * 🎯 A value object that represents a normalized, validated adapter profile.
 *
 * Profiles are used across the Maatify Data-Adapters ecosystem to distinguish
 * between different connection scopes:
 *
 * ```
 * mysql.main
 * mysql.logs
 * mongo.reporting
 * redis.cache
 * ```
 *
 * ✔ Ensures profile names are safe
 * ✔ Enforces allowed characters
 * ✔ Auto-normalizes to **uppercase** internally
 * ✔ Immutable (`readonly`) and convertible to string
 *
 * @example Creating a profile:
 * ```php
 * $profile = AdapterProfile::from('logs');
 * echo $profile;           // "LOGS"
 * ```
 *
 * @example Invalid:
 * ```php
 * AdapterProfile::from('bad profile'); // throws InvalidArgumentException
 * ```
 */
final readonly class AdapterProfile implements ProfileInterface
{
    /**
     * @param string $profile Normalized profile name (always uppercase)
     */
    private function __construct(
        private string $profile
    ) {
    }

    /**
     * 🎯 **Create an AdapterProfile from raw input**
     *
     * Steps:
     * - Trim whitespace
     * - Convert to lowercase
     * - Validate allowed characters: `[a-z0-9._-]`
     * - Store internally as uppercase
     *
     * @param string $profile Raw profile string (e.g., "main", "cache", "logs")
     *
     * @return self
     *
     * @throws InvalidArgumentException When profile contains invalid characters or is empty
     */
    public static function from(string $profile): self
    {
        $profile = strtolower(trim($profile));

        if ($profile === '') {
            throw new InvalidArgumentException("Profile name cannot be empty.");
        }

        if (! preg_match('/^[a-z0-9._-]+$/', $profile)) {
            throw new InvalidArgumentException(
                "Invalid profile '{$profile}'. Allowed characters: a-z, 0-9, ., _, -"
            );
        }

        return new self(strtoupper($profile));
    }

    /**
     * Get the normalized profile value.
     *
     * @return string Uppercase profile string
     */
    public function value(): string
    {
        return $this->profile;
    }

    /**
     * Cast the profile to its string value.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->profile;
    }
}
