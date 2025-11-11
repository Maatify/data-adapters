<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm)
 * @since       2025-11-08 20:19
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters  view project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Core\Exceptions;

use RuntimeException;

/**
 * ⚙️ Class FallbackException
 *
 * 🧩 Purpose:
 * Thrown when a fallback mechanism within a data adapter fails or cannot recover
 * from an unavailable primary data source (e.g., Redis → MySQL fallback failure).
 *
 * ✅ Typical Scenarios:
 * - Fallback data source unreachable or misconfigured.
 * - Sync operation between fallback and primary failed.
 * - Recovery attempt exceeded retry threshold.
 *
 * ⚙️ Example Usage:
 * ```php
 * throw new FallbackException("Failed to sync cache data from MySQL to Redis.");
 * ```
 *
 * @package Maatify\DataAdapters\Core\Exceptions
 */
final class FallbackException extends RuntimeException {}
