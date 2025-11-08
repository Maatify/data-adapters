<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-11-08
 * Time: 20:29
 * Project: maatify:data-adapters
 * IDE: PhpStorm
 * https://www.Maatify.dev
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
