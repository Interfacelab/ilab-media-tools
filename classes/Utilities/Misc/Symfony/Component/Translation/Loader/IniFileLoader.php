<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Loader;

/**
 * IniFileLoader loads translations from an ini file.
 *
 * @author stealth35
 */
class IniFileLoader extends \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Loader\FileLoader
{
    /**
     * {@inheritdoc}
     */
    protected function loadResource($resource)
    {
        return \parse_ini_file($resource, \true);
    }
}
