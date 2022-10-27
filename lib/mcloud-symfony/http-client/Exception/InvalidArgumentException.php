<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MediaCloud\Vendor\Symfony\Component\HttpClient\Exception;
use MediaCloud\Vendor\Symfony\Contracts\HttpClient\Exception\TransportExceptionInterface;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
final class InvalidArgumentException extends \InvalidArgumentException implements TransportExceptionInterface
{
}
