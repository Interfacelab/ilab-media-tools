<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Dumper;

use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue;
/**
 * JsonFileDumper generates an json formatted string representation of a message catalogue.
 *
 * @author singles
 */
class JsonFileDumper extends \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Dumper\FileDumper
{
    /**
     * {@inheritdoc}
     */
    public function formatCatalogue(\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue $messages, $domain, array $options = [])
    {
        if (isset($options['json_encoding'])) {
            $flags = $options['json_encoding'];
        } else {
            $flags = \defined('JSON_PRETTY_PRINT') ? \JSON_PRETTY_PRINT : 0;
        }
        return \json_encode($messages->all($domain), $flags);
    }
    /**
     * {@inheritdoc}
     */
    protected function getExtension()
    {
        return 'json';
    }
}
