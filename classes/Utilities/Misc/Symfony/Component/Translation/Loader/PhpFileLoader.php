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
 * PhpFileLoader loads translations from PHP files returning an array of translations.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class PhpFileLoader extends \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Loader\FileLoader
{
    /**
     * {@inheritdoc}
     */
    protected function loadResource($resource)
    {
        return require $resource;
    }
}
