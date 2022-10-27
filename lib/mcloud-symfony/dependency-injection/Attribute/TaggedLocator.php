<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MediaCloud\Vendor\Symfony\Component\DependencyInjection\Attribute;

#[\Attribute(\Attribute::TARGET_PARAMETER)]
class TaggedLocator
{
    public function __construct(
        string $tag,
        ?string $indexAttribute = null,
        ?string $defaultIndexMethod = null,
        ?string $defaultPriorityMethod = null
    ) {
    }
}
