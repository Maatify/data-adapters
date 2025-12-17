<?php

/**
 * @copyright   ©2025 Maatify.dev
 * @Library     maatify/data-adapters
 * @Project     maatify:data-adapters
 * @author      Mohamed Abdulalim (megyptm) <mohamed@maatify.dev>
 * @since       2025-12-17 03:57
 * @see         https://www.maatify.dev Maatify.dev
 * @link        https://github.com/Maatify/data-adapters view Project on GitHub
 * @note        Distributed in the hope that it will be useful - WITHOUT WARRANTY.
 */

declare(strict_types=1);

namespace Maatify\DataAdapters\Tests\Contract;

use Maatify\DataAdapters\Contracts\Adapter\AdapterInterface;
use PHPUnit\Framework\TestCase;

abstract class AbstractAdapterContract extends TestCase
{
    /**
     * كل Adapter لازم يوفّر instance جاهز
     */
    abstract protected function createAdapter(): AdapterInterface;

    public function test_it_returns_a_driver_object(): void
    {
        $adapter = $this->createAdapter();

        $driver = $adapter->getDriver();

        $this->assertIsObject(
            $driver,
            'Adapter::getDriver() must return an object'
        );
    }

    public function test_it_returns_the_same_driver_instance(): void
    {
        $adapter = $this->createAdapter();

        $first = $adapter->getDriver();
        $second = $adapter->getDriver();

        $this->assertSame(
            $first,
            $second,
            'Adapter must not recreate or mutate the driver instance'
        );
    }

    public function test_adapter_does_not_expose_magic_methods(): void
    {
        $adapter = $this->createAdapter();

        $this->assertFalse(
            method_exists($adapter, '__call'),
            'Adapters must not use magic method __call'
        );
    }
}
