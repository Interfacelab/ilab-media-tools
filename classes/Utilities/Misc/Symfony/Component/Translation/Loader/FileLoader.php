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

use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Config\Resource\FileResource;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidResourceException;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\NotFoundResourceException;
/**
 * @author Abdellatif Ait boudad <a.aitboudad@gmail.com>
 */
abstract class FileLoader extends \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Loader\ArrayLoader
{
    /**
     * {@inheritdoc}
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        if (!\stream_is_local($resource)) {
            throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidResourceException(\sprintf('This is not a local file "%s".', $resource));
        }
        if (!\file_exists($resource)) {
            throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\NotFoundResourceException(\sprintf('File "%s" not found.', $resource));
        }
        $messages = $this->loadResource($resource);
        // empty resource
        if (null === $messages) {
            $messages = [];
        }
        // not an array
        if (!\is_array($messages)) {
            throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidResourceException(\sprintf('Unable to load file "%s".', $resource));
        }
        $catalogue = parent::load($messages, $locale, $domain);
        if (\class_exists('ILAB\\MediaCloud\\Utilities\\Misc\\Symfony\\Component\\Config\\Resource\\FileResource')) {
            $catalogue->addResource(new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Config\Resource\FileResource($resource));
        }
        return $catalogue;
    }
    /**
     * @param string $resource
     *
     * @return array
     *
     * @throws InvalidResourceException if stream content has an invalid format
     */
    protected abstract function loadResource($resource);
}
