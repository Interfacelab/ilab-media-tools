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

use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidResourceException;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\LogicException;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Yaml\Exception\ParseException;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Yaml\Parser as YamlParser;
/**
 * YamlFileLoader loads translations from Yaml files.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class YamlFileLoader extends \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Loader\FileLoader
{
    private $yamlParser;
    /**
     * {@inheritdoc}
     */
    protected function loadResource($resource)
    {
        if (null === $this->yamlParser) {
            if (!\class_exists('ILAB\\MediaCloud\\Utilities\\Misc\\Symfony\\Component\\Yaml\\Parser')) {
                throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\LogicException('Loading translations from the YAML format requires the Symfony Yaml component.');
            }
            $this->yamlParser = new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Yaml\Parser();
        }
        $prevErrorHandler = \set_error_handler(function ($level, $message, $script, $line) use($resource, &$prevErrorHandler) {
            $message = \E_USER_DEPRECATED === $level ? \preg_replace('/ on line \\d+/', ' in "' . $resource . '"$0', $message) : $message;
            return $prevErrorHandler ? $prevErrorHandler($level, $message, $script, $line) : \false;
        });
        try {
            $messages = $this->yamlParser->parseFile($resource);
        } catch (\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Yaml\Exception\ParseException $e) {
            throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidResourceException(\sprintf('Error parsing YAML, invalid file "%s"', $resource), 0, $e);
        } finally {
            \restore_error_handler();
        }
        if (null !== $messages && !\is_array($messages)) {
            throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidResourceException(\sprintf('Unable to load file "%s".', $resource));
        }
        return $messages ?: [];
    }
}
