<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation;

use ILAB\MediaCloud\Utilities\Misc\Psr\Log\LoggerInterface;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidArgumentException;
/**
 * @author Abdellatif Ait boudad <a.aitboudad@gmail.com>
 */
class LoggingTranslator implements \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\TranslatorInterface, \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\TranslatorBagInterface
{
    /**
     * @var TranslatorInterface|TranslatorBagInterface
     */
    private $translator;
    private $logger;
    /**
     * @param TranslatorInterface $translator The translator must implement TranslatorBagInterface
     */
    public function __construct(\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\TranslatorInterface $translator, \ILAB\MediaCloud\Utilities\Misc\Psr\Log\LoggerInterface $logger)
    {
        if (!$translator instanceof \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\TranslatorBagInterface) {
            throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidArgumentException(\sprintf('The Translator "%s" must implement TranslatorInterface and TranslatorBagInterface.', \get_class($translator)));
        }
        $this->translator = $translator;
        $this->logger = $logger;
    }
    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        $trans = $this->translator->trans($id, $parameters, $domain, $locale);
        $this->log($id, $domain, $locale);
        return $trans;
    }
    /**
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        $trans = $this->translator->transChoice($id, $number, $parameters, $domain, $locale);
        $this->log($id, $domain, $locale);
        return $trans;
    }
    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->translator->setLocale($locale);
    }
    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->translator->getLocale();
    }
    /**
     * {@inheritdoc}
     */
    public function getCatalogue($locale = null)
    {
        return $this->translator->getCatalogue($locale);
    }
    /**
     * Gets the fallback locales.
     *
     * @return array The fallback locales
     */
    public function getFallbackLocales()
    {
        if ($this->translator instanceof \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Translator || \method_exists($this->translator, 'getFallbackLocales')) {
            return $this->translator->getFallbackLocales();
        }
        return [];
    }
    /**
     * Passes through all unknown calls onto the translator object.
     */
    public function __call($method, $args)
    {
        return \call_user_func_array([$this->translator, $method], $args);
    }
    /**
     * Logs for missing translations.
     *
     * @param string      $id
     * @param string|null $domain
     * @param string|null $locale
     */
    private function log($id, $domain, $locale)
    {
        if (null === $domain) {
            $domain = 'messages';
        }
        $id = (string) $id;
        $catalogue = $this->translator->getCatalogue($locale);
        if ($catalogue->defines($id, $domain)) {
            return;
        }
        if ($catalogue->has($id, $domain)) {
            $this->logger->debug('Translation use fallback catalogue.', ['id' => $id, 'domain' => $domain, 'locale' => $catalogue->getLocale()]);
        } else {
            $this->logger->warning('Translation not found.', ['id' => $id, 'domain' => $domain, 'locale' => $catalogue->getLocale()]);
        }
    }
}
