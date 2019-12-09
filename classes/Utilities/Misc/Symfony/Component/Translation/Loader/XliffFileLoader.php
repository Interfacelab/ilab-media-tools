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
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Config\Util\XmlUtils;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidResourceException;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\NotFoundResourceException;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Util\XliffUtils;
/**
 * XliffFileLoader loads translations from XLIFF files.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class XliffFileLoader implements \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Loader\LoaderInterface
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
        $catalogue = new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue($locale);
        $this->extract($resource, $catalogue, $domain);
        if (\class_exists('ILAB\\MediaCloud\\Utilities\\Misc\\Symfony\\Component\\Config\\Resource\\FileResource')) {
            $catalogue->addResource(new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Config\Resource\FileResource($resource));
        }
        return $catalogue;
    }
    private function extract($resource, \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue $catalogue, string $domain)
    {
        try {
            $dom = \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Config\Util\XmlUtils::loadFile($resource);
        } catch (\InvalidArgumentException $e) {
            throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidResourceException(\sprintf('Unable to load "%s": %s', $resource, $e->getMessage()), $e->getCode(), $e);
        }
        $xliffVersion = \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Util\XliffUtils::getVersionNumber($dom);
        if ($errors = \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Util\XliffUtils::validateSchema($dom)) {
            throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidResourceException(\sprintf('Invalid resource provided: "%s"; Errors: %s', $resource, \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Util\XliffUtils::getErrorsAsString($errors)));
        }
        if ('1.2' === $xliffVersion) {
            $this->extractXliff1($dom, $catalogue, $domain);
        }
        if ('2.0' === $xliffVersion) {
            $this->extractXliff2($dom, $catalogue, $domain);
        }
    }
    /**
     * Extract messages and metadata from DOMDocument into a MessageCatalogue.
     */
    private function extractXliff1(\DOMDocument $dom, \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue $catalogue, string $domain)
    {
        $xml = \simplexml_import_dom($dom);
        $encoding = \strtoupper($dom->encoding);
        $namespace = 'urn:oasis:names:tc:xliff:document:1.2';
        $xml->registerXPathNamespace('xliff', $namespace);
        foreach ($xml->xpath('//xliff:file') as $file) {
            $fileAttributes = $file->attributes();
            $file->registerXPathNamespace('xliff', $namespace);
            foreach ($file->xpath('.//xliff:trans-unit') as $translation) {
                $attributes = $translation->attributes();
                if (!(isset($attributes['resname']) || isset($translation->source))) {
                    continue;
                }
                $source = isset($attributes['resname']) && $attributes['resname'] ? $attributes['resname'] : $translation->source;
                // If the xlf file has another encoding specified, try to convert it because
                // simple_xml will always return utf-8 encoded values
                $target = $this->utf8ToCharset((string) ($translation->target ?? $translation->source), $encoding);
                $catalogue->set((string) $source, $target, $domain);
                $metadata = ['source' => (string) $translation->source, 'file' => ['original' => (string) $fileAttributes['original']]];
                if ($notes = $this->parseNotesMetadata($translation->note, $encoding)) {
                    $metadata['notes'] = $notes;
                }
                if (isset($translation->target) && $translation->target->attributes()) {
                    $metadata['target-attributes'] = [];
                    foreach ($translation->target->attributes() as $key => $value) {
                        $metadata['target-attributes'][$key] = (string) $value;
                    }
                }
                if (isset($attributes['id'])) {
                    $metadata['id'] = (string) $attributes['id'];
                }
                $catalogue->setMetadata((string) $source, $metadata, $domain);
            }
        }
    }
    private function extractXliff2(\DOMDocument $dom, \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue $catalogue, string $domain)
    {
        $xml = \simplexml_import_dom($dom);
        $encoding = \strtoupper($dom->encoding);
        $xml->registerXPathNamespace('xliff', 'urn:oasis:names:tc:xliff:document:2.0');
        foreach ($xml->xpath('//xliff:unit') as $unit) {
            foreach ($unit->segment as $segment) {
                $source = $segment->source;
                // If the xlf file has another encoding specified, try to convert it because
                // simple_xml will always return utf-8 encoded values
                $target = $this->utf8ToCharset((string) (isset($segment->target) ? $segment->target : $source), $encoding);
                $catalogue->set((string) $source, $target, $domain);
                $metadata = [];
                if (isset($segment->target) && $segment->target->attributes()) {
                    $metadata['target-attributes'] = [];
                    foreach ($segment->target->attributes() as $key => $value) {
                        $metadata['target-attributes'][$key] = (string) $value;
                    }
                }
                if (isset($unit->notes)) {
                    $metadata['notes'] = [];
                    foreach ($unit->notes->note as $noteNode) {
                        $note = [];
                        foreach ($noteNode->attributes() as $key => $value) {
                            $note[$key] = (string) $value;
                        }
                        $note['content'] = (string) $noteNode;
                        $metadata['notes'][] = $note;
                    }
                }
                $catalogue->setMetadata((string) $source, $metadata, $domain);
            }
        }
    }
    /**
     * Convert a UTF8 string to the specified encoding.
     */
    private function utf8ToCharset(string $content, string $encoding = null) : string
    {
        if ('UTF-8' !== $encoding && !empty($encoding)) {
            return \mb_convert_encoding($content, $encoding, 'UTF-8');
        }
        return $content;
    }
    private function parseNotesMetadata(\SimpleXMLElement $noteElement = null, string $encoding = null) : array
    {
        $notes = [];
        if (null === $noteElement) {
            return $notes;
        }
        /** @var \SimpleXMLElement $xmlNote */
        foreach ($noteElement as $xmlNote) {
            $noteAttributes = $xmlNote->attributes();
            $note = ['content' => $this->utf8ToCharset((string) $xmlNote, $encoding)];
            if (isset($noteAttributes['priority'])) {
                $note['priority'] = (int) $noteAttributes['priority'];
            }
            if (isset($noteAttributes['from'])) {
                $note['from'] = (string) $noteAttributes['from'];
            }
            $notes[] = $note;
        }
        return $notes;
    }
}
