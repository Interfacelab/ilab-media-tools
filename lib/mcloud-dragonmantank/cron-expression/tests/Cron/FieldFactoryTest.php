<?php

declare(strict_types=1);

namespace MediaCloud\Vendor\Cron\Tests;
use MediaCloud\Vendor\Cron\FieldFactory;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * @author Michael Dowling <mtdowling@gmail.com>
 */
class FieldFactoryTest extends TestCase
{
    /**
     * @covers \MediaCloud\Vendor\Cron\FieldFactory::getField
     */
    public function testRetrievesFieldInstances()
    {
        $mappings = [
            0 => 'MediaCloud\Vendor\Cron\MinutesField',
            1 => 'MediaCloud\Vendor\Cron\HoursField',
            2 => 'MediaCloud\Vendor\Cron\DayOfMonthField',
            3 => 'MediaCloud\Vendor\Cron\MonthField',
            4 => 'MediaCloud\Vendor\Cron\DayOfWeekField',
        ];

        $f = new FieldFactory();

        foreach ($mappings as $position => $class) {
            $this->assertSame($class, \get_class($f->getField($position)));
        }
    }

    /**
     * @covers \MediaCloud\Vendor\Cron\FieldFactory::getField
     */
    public function testValidatesFieldPosition()
    {
        $this->expectException(InvalidArgumentException::class);
        $f = new FieldFactory();
        $f->getField(-1);
    }
}
