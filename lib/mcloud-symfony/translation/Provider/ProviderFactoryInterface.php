<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MediaCloud\Vendor\Symfony\Component\Translation\Provider;
use MediaCloud\Vendor\Symfony\Component\Translation\Exception\IncompleteDsnException;
use MediaCloud\Vendor\Symfony\Component\Translation\Exception\UnsupportedSchemeException;

interface ProviderFactoryInterface
{
    /**
     * @throws UnsupportedSchemeException
     * @throws IncompleteDsnException
     */
    public function create(Dsn $dsn): ProviderInterface;

    public function supports(Dsn $dsn): bool;
}
