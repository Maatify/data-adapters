<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-17 03:58
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-adapters view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Contract;

use Maatify\DataAdapters\Contracts\Adapter\AdapterInterface;
use Maatify\DataAdapters\TestDoubles\DummyAdapter;

final class DummyAbstractAdapter extends AbstractAdapterContract
{
    protected function createAdapter(): AdapterInterface
    {
        return new DummyAdapter(new \stdClass());
    }
}
