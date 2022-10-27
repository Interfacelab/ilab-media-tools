<?php

/*
 * This file is part of the PhpM3u8 package.
 *
 * (c) Chrisyue <https://chrisyue.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MediaCloud\Vendor\Chrisyue\PhpM3u8\Test\Line;
use MediaCloud\Vendor\Chrisyue\PhpM3u8\Line\Line;
use MediaCloud\Vendor\Chrisyue\PhpM3u8\Line\Lines;
use MediaCloud\Vendor\Chrisyue\PhpM3u8\Stream\StreamInterface;
use PHPUnit\Framework\TestCase;

class LinesTest extends TestCase
{
    public function testValid()
    {
        $stream = $this->prophesize(StreamInterface::class);
        $stream->valid()->shouldBeCalledOnce()->willReturn(false);
        $lines = new Lines($stream->reveal());

        $this->assertFalse($lines->valid());

        $tag = 'EXT-X-FOO:1';
        $stream = $this->prophesize(StreamInterface::class);
        $stream->valid()->shouldBeCalledOnce()->willReturn(true);
        $stream->current()->shouldBeCalled()->willReturn($tag);
        $lines = new Lines($stream->reveal());

        $this->assertTrue($lines->valid());
        $this->assertEquals(Line::fromString($tag), $lines->current());
    }
}
