<?php declare(strict_types=1);

/*
 * This file is part of the Monolog package.
 *
 * (c) Jordi Boggiano <j.boggiano@seld.be>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MediaCloud\Vendor\Monolog\Handler\FingersCrossed;
use MediaCloud\Vendor\Monolog\Logger;
use MediaCloud\Vendor\Psr\Log\LogLevel;

/**
 * Error level based activation strategy.
 *
 * @author Johannes M. Schmitt <schmittjoh@gmail.com>
 *
 * @phpstan-import-type Level from \MediaCloud\Vendor\Monolog\Logger
 * @phpstan-import-type LevelName from \MediaCloud\Vendor\Monolog\Logger
 */
class ErrorLevelActivationStrategy implements ActivationStrategyInterface
{
    /**
     * @var Level
     */
    private $actionLevel;

    /**
     * @param int|string $actionLevel Level or name or value
     *
     * @phpstan-param Level|LevelName|LogLevel::* $actionLevel
     */
    public function __construct($actionLevel)
    {
        $this->actionLevel = Logger::toMonologLevel($actionLevel);
    }

    public function isHandlerActivated(array $record): bool
    {
        return $record['level'] >= $this->actionLevel;
    }
}
