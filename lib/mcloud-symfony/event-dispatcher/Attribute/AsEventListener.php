<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MediaCloud\Vendor\Symfony\Component\EventDispatcher\Attribute;

/**
 * Service tag to autoconfigure event listeners.
 *
 * @author Alexander M. Turek <me@derrabus.de>
 */
#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class AsEventListener
{
    public function __construct(
        ?string $event = null,
        ?string $method = null,
        int $priority = 0,
        ?string $dispatcher = null
    ) {
    }
}
