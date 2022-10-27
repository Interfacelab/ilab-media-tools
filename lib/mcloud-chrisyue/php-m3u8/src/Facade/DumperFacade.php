<?php

/*
 * This file is part of the PhpM3u8 package.
 *
 * (c) Chrisyue <https://chrisyue.com/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace MediaCloud\Vendor\Chrisyue\PhpM3u8\Facade;
use MediaCloud\Vendor\Chrisyue\PhpM3u8\Config;
use MediaCloud\Vendor\Chrisyue\PhpM3u8\Definition\TagDefinitions;
use MediaCloud\Vendor\Chrisyue\PhpM3u8\Dumper\Dumper;
use MediaCloud\Vendor\Chrisyue\PhpM3u8\Line\Lines;
use MediaCloud\Vendor\Chrisyue\PhpM3u8\Stream\StreamInterface;

class DumperFacade
{
    private $dumper;

    public function dump(\ArrayAccess $data, StreamInterface $stream)
    {
        if (null === $this->dumper) {
            $rootPath = realpath(__DIR__.'/../..');
            $tagDefinitions = new TagDefinitions(require $rootPath.'/resources/tags.php');

            $this->dumper = new Dumper(
                $tagDefinitions,
                new Config(require $rootPath.'/resources/tagValueDumpers.php')
            );
        }

        $this->dumper->dumpToLines($data, new Lines($stream));
    }
}
