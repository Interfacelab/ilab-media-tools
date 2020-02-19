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

use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidArgumentException;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\RuntimeException;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue;
/**
 * FileDumper is an implementation of DumperInterface that dump a message catalogue to file(s).
 * Performs backup of already existing files.
 *
 * Options:
 * - path (mandatory): the directory where the files should be saved
 *
 * @author Michel Salib <michelsalib@hotmail.com>
 */
abstract class FileDumper implements \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Dumper\DumperInterface
{
    /**
     * A template for the relative paths to files.
     *
     * @var string
     */
    protected $relativePathTemplate = '%domain%.%locale%.%extension%';
    /**
     * Make file backup before the dump.
     *
     * @var bool
     */
    private $backup = \true;
    /**
     * Sets the template for the relative paths to files.
     *
     * @param string $relativePathTemplate A template for the relative paths to files
     */
    public function setRelativePathTemplate($relativePathTemplate)
    {
        $this->relativePathTemplate = $relativePathTemplate;
    }
    /**
     * Sets backup flag.
     *
     * @param bool $backup
     */
    public function setBackup($backup)
    {
        $this->backup = $backup;
    }
    /**
     * {@inheritdoc}
     */
    public function dump(\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue $messages, $options = [])
    {
        if (!\array_key_exists('path', $options)) {
            throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidArgumentException('The file dumper needs a path option.');
        }
        // save a file for each domain
        foreach ($messages->getDomains() as $domain) {
            // backup
            $fullpath = $options['path'] . '/' . $this->getRelativePath($domain, $messages->getLocale());
            if (\file_exists($fullpath)) {
                if ($this->backup) {
                    @\trigger_error('Creating a backup while dumping a message catalogue is deprecated since Symfony 3.1 and will be removed in 4.0. Use TranslationWriter::disableBackup() to disable the backup.', \E_USER_DEPRECATED);
                    \copy($fullpath, $fullpath . '~');
                }
            } else {
                $directory = \dirname($fullpath);
                if (!\file_exists($directory) && !@\mkdir($directory, 0777, \true)) {
                    throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\RuntimeException(\sprintf('Unable to create directory "%s".', $directory));
                }
            }
            // save file
            \file_put_contents($fullpath, $this->formatCatalogue($messages, $domain, $options));
        }
    }
    /**
     * Transforms a domain of a message catalogue to its string representation.
     *
     * @param string $domain
     *
     * @return string representation
     */
    public abstract function formatCatalogue(\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue $messages, $domain, array $options = []);
    /**
     * Gets the file extension of the dumper.
     *
     * @return string file extension
     */
    protected abstract function getExtension();
    /**
     * Gets the relative file path using the template.
     *
     * @param string $domain The domain
     * @param string $locale The locale
     *
     * @return string The relative file path
     */
    private function getRelativePath($domain, $locale)
    {
        return \strtr($this->relativePathTemplate, ['%domain%' => $domain, '%locale%' => $locale, '%extension%' => $this->getExtension()]);
    }
}
