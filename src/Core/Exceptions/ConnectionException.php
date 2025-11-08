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
 * ⚠️ Class ConnectionException
 *
 * 🧩 Purpose:
 * Represents an exception thrown when a data adapter fails to establish
 * or maintain a valid connection to its target data source.
 *
 * ✅ Typical Scenarios:
 * - Missing required environment variables.
 * - Invalid connection credentials or configuration.
 * - Network or authentication issues with the target service.
 *
 * ⚙️ Example Usage:
 * ```php
 * throw new ConnectionException("Failed to connect to Redis server");
 * ```
 *
 * @package Maatify\DataAdapters\Core\Exceptions
 */
final class ConnectionException extends RuntimeException {}
