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

use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue;
/**
 * ArrayLoader loads translations from a PHP array.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class ArrayLoader implements \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Loader\LoaderInterface
{
    /**
     * {@inheritdoc}
     */
    public function load($resource, $locale, $domain = 'messages')
    {
        $resource = $this->flatten($resource);
        $catalogue = new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue($locale);
        $catalogue->add($resource, $domain);
        return $catalogue;
    }
    /**
     * Flattens an nested array of translations.
     *
     * The scheme used is:
     *   'key' => ['key2' => ['key3' => 'value']]
     * Becomes:
     *   'key.key2.key3' => 'value'
     */
    private function flatten(array $messages) : array
    {
        $result = [];
        foreach ($messages as $key => $value) {
            if (\is_array($value)) {
                foreach ($this->flatten($value) as $k => $v) {
                    $result[$key . '.' . $k] = $v;
                }
            } else {
                $result[$key] = $value;
            }
        }
        return $result;
    }
}
