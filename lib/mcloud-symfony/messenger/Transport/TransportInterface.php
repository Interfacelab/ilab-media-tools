<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MediaCloud\Vendor\Symfony\Component\Messenger\Transport;
use MediaCloud\Vendor\Symfony\Component\Messenger\Transport\Receiver\ReceiverInterface;
use MediaCloud\Vendor\Symfony\Component\Messenger\Transport\Sender\SenderInterface;

/**
 * @author Nicolas Grekas <p@tchwork.com>
 */
interface TransportInterface extends ReceiverInterface, SenderInterface
{
}
