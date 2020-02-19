<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\DependencyInjection;

use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\DependencyInjection\ContainerBuilder;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\DependencyInjection\Exception\RuntimeException;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\DependencyInjection\Reference;
/**
 * Adds tagged translation.extractor services to translation extractor.
 */
class TranslationExtractorPass implements \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface
{
    private $extractorServiceId;
    private $extractorTag;
    public function __construct($extractorServiceId = 'translation.extractor', $extractorTag = 'translation.extractor')
    {
        $this->extractorServiceId = $extractorServiceId;
        $this->extractorTag = $extractorTag;
    }
    public function process(\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\DependencyInjection\ContainerBuilder $container)
    {
        if (!$container->hasDefinition($this->extractorServiceId)) {
            return;
        }
        $definition = $container->getDefinition($this->extractorServiceId);
        foreach ($container->findTaggedServiceIds($this->extractorTag, \true) as $id => $attributes) {
            if (!isset($attributes[0]['alias'])) {
                throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\DependencyInjection\Exception\RuntimeException(\sprintf('The alias for the tag "translation.extractor" of service "%s" must be set.', $id));
            }
            $definition->addMethodCall('addExtractor', [$attributes[0]['alias'], new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\DependencyInjection\Reference($id)]);
        }
    }
}
