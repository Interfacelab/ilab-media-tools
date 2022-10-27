<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MediaCloud\Vendor\Symfony\Component\Cache\Exception;
use MediaCloud\Vendor\Psr\Cache\InvalidArgumentException as Psr6CacheInterface;
use MediaCloud\Vendor\Psr\SimpleCache\InvalidArgumentException as SimpleCacheInterface;

if (interface_exists(SimpleCacheInterface::class)) {
    class InvalidArgumentException extends \InvalidArgumentException implements Psr6CacheInterface, SimpleCacheInterface
    {
    }
} else {
    class InvalidArgumentException extends \InvalidArgumentException implements Psr6CacheInterface
    {
    }
}
