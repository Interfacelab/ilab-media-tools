<?php

/**
 * This file is part of the Carbon package.
 *
 * (c) Brian Nesbitt <brian@nesbot.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MediaCloud\Vendor\Carbon\Exceptions;

use Exception;
use InvalidArgumentException as BaseInvalidArgumentException;

class NotLocaleAwareException extends BaseInvalidArgumentException implements InvalidArgumentException
{
    /**
     * Constructor.
     *
     * @param mixed          $object
     * @param int            $code
     * @param Exception|null $previous
     */
    public function __construct($object, $code = 0, Exception $previous = null)
    {
        $dump = \is_object($object) ? \get_class($object) : \gettype($object);

        parent::__construct("$dump does neither implements \MediaCloud\Vendor\Symfony\Contracts\Translation\LocaleAwareInterface nor getLocale() method.", $code, $previous);
    }
}
