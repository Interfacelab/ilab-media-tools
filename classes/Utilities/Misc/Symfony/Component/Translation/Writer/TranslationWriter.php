<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Writer;

use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Dumper\DumperInterface;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidArgumentException;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\RuntimeException;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue;
/**
 * TranslationWriter writes translation messages.
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
class TranslationWriter implements \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Writer\TranslationWriterInterface
{
    private $dumpers = [];
    /**
     * Adds a dumper to the writer.
     *
     * @param string          $format The format of the dumper
     * @param DumperInterface $dumper The dumper
     */
    public function addDumper($format, \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Dumper\DumperInterface $dumper)
    {
        $this->dumpers[$format] = $dumper;
    }
    /**
     * Disables dumper backup.
     */
    public function disableBackup()
    {
        foreach ($this->dumpers as $dumper) {
            if (\method_exists($dumper, 'setBackup')) {
                $dumper->setBackup(\false);
            }
        }
    }
    /**
     * Obtains the list of supported formats.
     *
     * @return array
     */
    public function getFormats()
    {
        return \array_keys($this->dumpers);
    }
    /**
     * Writes translation from the catalogue according to the selected format.
     *
     * @param MessageCatalogue $catalogue The message catalogue to write
     * @param string           $format    The format to use to dump the messages
     * @param array            $options   Options that are passed to the dumper
     *
     * @throws InvalidArgumentException
     */
    public function write(\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue $catalogue, $format, $options = [])
    {
        if (!isset($this->dumpers[$format])) {
            throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidArgumentException(\sprintf('There is no dumper associated with format "%s".', $format));
        }
        // get the right dumper
        $dumper = $this->dumpers[$format];
        if (isset($options['path']) && !\is_dir($options['path']) && !@\mkdir($options['path'], 0777, \true) && !\is_dir($options['path'])) {
            throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\RuntimeException(\sprintf('Translation Writer was not able to create directory "%s"', $options['path']));
        }
        // save
        $dumper->dump($catalogue, $options);
    }
    /**
     * Writes translation from the catalogue according to the selected format.
     *
     * @param MessageCatalogue $catalogue The message catalogue to write
     * @param string           $format    The format to use to dump the messages
     * @param array            $options   Options that are passed to the dumper
     *
     * @throws InvalidArgumentException
     *
     * @deprecated since 3.4 will be removed in 4.0. Use write instead.
     */
    public function writeTranslations(\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue $catalogue, $format, $options = [])
    {
        @\trigger_error(\sprintf('The "%s()" method is deprecated since Symfony 3.4 and will be removed in 4.0. Use write() instead.', __METHOD__), \E_USER_DEPRECATED);
        $this->write($catalogue, $format, $options);
    }
}
