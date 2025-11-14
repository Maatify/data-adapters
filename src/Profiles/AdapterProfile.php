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

final readonly class AdapterProfile implements ProfileInterface
{

    private function __construct(
        private string $profile
    ) {
    }

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

    public function value(): string
    {
        return $this->profile;
    }

    public function __toString(): string
    {
        return $this->profile;
    }
}