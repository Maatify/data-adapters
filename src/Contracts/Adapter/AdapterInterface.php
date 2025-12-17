<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Liberary    maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-11-09 21:49
 * @see         https://www.maatify.dev Maatify.com
 * @link        https://github.com/Maatify/data-adapters view Liberary on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Contracts\Adapter;

/**
 * 🧩 AdapterInterface
 *
 * 🎯 Purpose:
 * Acts as a **DI boundary and ownership wrapper** around a concrete infrastructure driver.
 *
 * This interface intentionally provides **no unified API, no lifecycle management,
 * and no operational behavior**. Consumers are required to explicitly extract
 * and interact with the underlying driver instance.
 *
 * ❗ This is NOT:
 * - A unified database API
 * - A Redis/MySQL abstraction
 * - A connection manager
 *
 * @template TDriver of object
 */
interface AdapterInterface
{
    /**
     * Return the underlying concrete driver instance.
     *
     * @return TDriver
     */
    public function getDriver(): object;
}
