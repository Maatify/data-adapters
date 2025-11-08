<?php
/**
 * Created by Maatify.dev
 * User: Maatify.dev
 * Date: 2025-11-08
 * Time: 20:35
 * Project: maatify:data-adapters
 * IDE: PhpStorm
 * https://www.Maatify.dev
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Enums;

/**
 * 🎯 Enum AdapterTypeEnum
 *
 * 🧩 Purpose:
 * Defines the supported adapter types within the Maatify Data Adapters ecosystem.
 * Each case represents a specific backend technology used for data connections.
 *
 * ✅ Usage Scenarios:
 * - Used for type-safe adapter resolution (e.g., Redis, MongoDB, MySQL).
 * - Enhances readability and reduces hardcoded string dependencies.
 *
 * ⚙️ Example Usage:
 * ```php
 * use Maatify\DataAdapters\Enums\AdapterTypeEnum;
 *
 * $type = AdapterTypeEnum::Redis;
 * echo $type->value; // Outputs: 'redis'
 * ```
 *
 * @package Maatify\DataAdapters\Enums
 */
enum AdapterTypeEnum: string
{
    /** 🧱 Redis adapter using native PHP extension */
    case Redis   = 'redis';

    /** ⚙️ Predis adapter (pure PHP fallback for environments without Redis extension) */
    case Predis  = 'predis';

    /** 🧩 MongoDB adapter for NoSQL document-based storage */
    case Mongo   = 'mongo';

    /** 🗄️ MySQL adapter for relational database connections */
    case MySQL   = 'mysql';
}
