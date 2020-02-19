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

use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Config\Resource\DirectoryResource;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidResourceException;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\NotFoundResourceException;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue;
/**
 * IcuResFileLoader loads translations from a resource bundle.
 *
 * @author stealth35
 */
class IcuResFileLoader implements \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Loader\LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        if (!\stream_is_local($resource)) {
            throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidResourceException(\sprintf('This is not a local file "%s".', $resource));
        }
        if (!\is_dir($resource)) {
            throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\NotFoundResourceException(\sprintf('File "%s" not found.', $resource));
        }
        try {
            $rb = new \ResourceBundle($locale, $resource);
        } catch (\Exception $e) {
            // HHVM compatibility: constructor throws on invalid resource
            $rb = null;
        }
        if (!$rb) {
            throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidResourceException(\sprintf('Cannot load resource "%s"', $resource));
        } elseif (\intl_is_failure($rb->getErrorCode())) {
            throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidResourceException($rb->getErrorMessage(), $rb->getErrorCode());
        }
        $messages = $this->flatten($rb);
        $catalogue = new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue($locale);
        $catalogue->add($messages, $domain);
        if (\class_exists('ILAB\\MediaCloud\\Utilities\\Misc\\Symfony\\Component\\Config\\Resource\\DirectoryResource')) {
            $catalogue->addResource(new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Config\Resource\DirectoryResource($resource));
        }
        return $catalogue;
    }
    /**
     * Flattens an ResourceBundle.
     *
     * The scheme used is:
     *   key { key2 { key3 { "value" } } }
     * Becomes:
     *   'key.key2.key3' => 'value'
     *
     * This function takes an array by reference and will modify it
     *
     * @param \ResourceBundle $rb       The ResourceBundle that will be flattened
     * @param array           $messages Used internally for recursive calls
     * @param string          $path     Current path being parsed, used internally for recursive calls
     *
     * @return array the flattened ResourceBundle
     */
    protected function flatten(\ResourceBundle $rb, array &$messages = [], $path = null)
    {
        foreach ($rb as $key => $value) {
            $nodePath = $path ? $path . '.' . $key : $key;
            if ($value instanceof \ResourceBundle) {
                $this->flatten($value, $messages, $nodePath);
            } else {
                $messages[$nodePath] = $value;
            }
        }
        return $messages;
    }
}
