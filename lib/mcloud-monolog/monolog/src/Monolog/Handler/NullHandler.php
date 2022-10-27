<?php declare(strict_types=1);

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MediaCloud\Vendor\Monolog\Handler;
use MediaCloud\Vendor\Monolog\Logger;
use MediaCloud\Vendor\Psr\Log\LogLevel;

/**
 * Blackhole
 *
 * Any record it can handle will be thrown away. This can be used
 * to put on top of an existing stack to override it temporarily.
 *
 * @author Jordi Boggiano <j.boggiano@seld.be>
 *
 * @phpstan-import-type Level from \MediaCloud\Vendor\Monolog\Logger
 * @phpstan-import-type LevelName from \MediaCloud\Vendor\Monolog\Logger
 */
class NullHandler extends Handler
{
    /**
     * @var int
     */
    private $level;

    /**
     * @param string|int $level The minimum logging level at which this handler will be triggered
     *
     * @phpstan-param Level|LevelName|LogLevel::* $level
     */
    public function __construct($level = Logger::DEBUG)
    {
        $this->level = Logger::toMonologLevel($level);
    }

    /**
     * {@inheritDoc}
     */
    public function isHandling(array $record): bool
    {
        return $record['level'] >= $this->level;
    }

    /**
     * {@inheritDoc}
     */
    public function handle(array $record): bool
    {
        return $record['level'] >= $this->level;
    }
}
