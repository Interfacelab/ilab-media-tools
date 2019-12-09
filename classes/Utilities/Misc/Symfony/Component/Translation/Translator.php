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

use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Config\ConfigCacheFactory;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Config\ConfigCacheFactoryInterface;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Config\ConfigCacheInterface;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidArgumentException;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\LogicException;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\NotFoundResourceException;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\RuntimeException;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Formatter\ChoiceMessageFormatterInterface;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Formatter\IntlFormatterInterface;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Formatter\MessageFormatter;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Formatter\MessageFormatterInterface;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Loader\LoaderInterface;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\TranslatorInterface as LegacyTranslatorInterface;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Contracts\Translation\TranslatorInterface;
/**
 * @author Fabien Potencier <fabien@symfony.com>
 */
class Translator implements \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\TranslatorInterface, \ILAB\MediaCloud\Utilities\Misc\Symfony\Contracts\Translation\TranslatorInterface, \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\TranslatorBagInterface
{
    /**
     * @var MessageCatalogueInterface[]
     */
    protected $catalogues = [];
    /**
     * @var string
     */
    private $locale;
    /**
     * @var array
     */
    private $fallbackLocales = [];
    /**
     * @var LoaderInterface[]
     */
    private $loaders = [];
    /**
     * @var array
     */
    private $resources = [];
    /**
     * @var MessageFormatterInterface
     */
    private $formatter;
    /**
     * @var string
     */
    private $cacheDir;
    /**
     * @var bool
     */
    private $debug;
    private $cacheVary;
    /**
     * @var ConfigCacheFactoryInterface|null
     */
    private $configCacheFactory;
    /**
     * @var array|null
     */
    private $parentLocales;
    private $hasIntlFormatter;
    /**
     * @throws InvalidArgumentException If a locale contains invalid characters
     */
    public function __construct(?string $locale, \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Formatter\MessageFormatterInterface $formatter = null, string $cacheDir = null, bool $debug = \false, array $cacheVary = [])
    {
        if (null === $locale) {
            @\trigger_error(\sprintf('Passing "null" as the $locale argument to %s() is deprecated since Symfony 4.4.', __METHOD__), \E_USER_DEPRECATED);
        }
        $this->setLocale($locale, \false);
        if (null === $formatter) {
            $formatter = new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Formatter\MessageFormatter();
        }
        $this->formatter = $formatter;
        $this->cacheDir = $cacheDir;
        $this->debug = $debug;
        $this->cacheVary = $cacheVary;
        $this->hasIntlFormatter = $formatter instanceof \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Formatter\IntlFormatterInterface;
    }
    public function setConfigCacheFactory(\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Config\ConfigCacheFactoryInterface $configCacheFactory)
    {
        $this->configCacheFactory = $configCacheFactory;
    }
    /**
     * Adds a Loader.
     *
     * @param string $format The name of the loader (@see addResource())
     */
    public function addLoader($format, \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Loader\LoaderInterface $loader)
    {
        $this->loaders[$format] = $loader;
    }
    /**
     * Adds a Resource.
     *
     * @param string $format   The name of the loader (@see addLoader())
     * @param mixed  $resource The resource name
     * @param string $locale   The locale
     * @param string $domain   The domain
     *
     * @throws InvalidArgumentException If the locale contains invalid characters
     */
    public function addResource($format, $resource, $locale, $domain = null)
    {
        if (null === $domain) {
            $domain = 'messages';
        }
        if (null === $locale) {
            @\trigger_error(\sprintf('Passing "null" to the third argument of the "%s" method has been deprecated since Symfony 4.4 and will throw an error in 5.0.', __METHOD__), \E_USER_DEPRECATED);
        }
        $this->assertValidLocale($locale);
        $this->resources[$locale][] = [$format, $resource, $domain];
        if (\in_array($locale, $this->fallbackLocales)) {
            $this->catalogues = [];
        } else {
            unset($this->catalogues[$locale]);
        }
    }
    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        if (null === $locale && (2 > \func_num_args() || \func_get_arg(1))) {
            @\trigger_error(\sprintf('Passing "null" as the $locale argument to %s() is deprecated since Symfony 4.4.', __METHOD__), \E_USER_DEPRECATED);
        }
        $this->assertValidLocale($locale);
        $this->locale = $locale;
    }
    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale;
    }
    /**
     * Sets the fallback locales.
     *
     * @param array $locales The fallback locales
     *
     * @throws InvalidArgumentException If a locale contains invalid characters
     */
    public function setFallbackLocales(array $locales)
    {
        // needed as the fallback locales are linked to the already loaded catalogues
        $this->catalogues = [];
        foreach ($locales as $locale) {
            if (null === $locale) {
                @\trigger_error(\sprintf('Passing "null" as the $locale argument to %s() is deprecated since Symfony 4.4.', __METHOD__), \E_USER_DEPRECATED);
            }
            $this->assertValidLocale($locale);
        }
        $this->fallbackLocales = $this->cacheVary['fallback_locales'] = $locales;
    }
    /**
     * Gets the fallback locales.
     *
     * @internal since Symfony 4.2
     *
     * @return array The fallback locales
     */
    public function getFallbackLocales()
    {
        return $this->fallbackLocales;
    }
    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        if ('' === ($id = (string) $id)) {
            return '';
        }
        if (null === $domain) {
            $domain = 'messages';
        }
        $catalogue = $this->getCatalogue($locale);
        $locale = $catalogue->getLocale();
        while (!$catalogue->defines($id, $domain)) {
            if ($cat = $catalogue->getFallbackCatalogue()) {
                $catalogue = $cat;
                $locale = $catalogue->getLocale();
            } else {
                break;
            }
        }
        if ($this->hasIntlFormatter && $catalogue->defines($id, $domain . \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue::INTL_DOMAIN_SUFFIX)) {
            return $this->formatter->formatIntl($catalogue->get($id, $domain), $locale, $parameters);
        }
        return $this->formatter->format($catalogue->get($id, $domain), $locale, $parameters);
    }
    /**
     * {@inheritdoc}
     *
     * @deprecated since Symfony 4.2, use the trans() method instead with a %count% parameter
     */
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        @\trigger_error(\sprintf('The "%s()" method is deprecated since Symfony 4.2, use the trans() one instead with a "%%count%%" parameter.', __METHOD__), \E_USER_DEPRECATED);
        if ('' === ($id = (string) $id)) {
            return '';
        }
        if (!$this->formatter instanceof \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Formatter\ChoiceMessageFormatterInterface) {
            throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\LogicException(\sprintf('The formatter "%s" does not support plural translations.', \get_class($this->formatter)));
        }
        if (null === $domain) {
            $domain = 'messages';
        }
        $catalogue = $this->getCatalogue($locale);
        $locale = $catalogue->getLocale();
        while (!$catalogue->defines($id, $domain)) {
            if ($cat = $catalogue->getFallbackCatalogue()) {
                $catalogue = $cat;
                $locale = $catalogue->getLocale();
            } else {
                break;
            }
        }
        if ($this->hasIntlFormatter && $catalogue->defines($id, $domain . \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue::INTL_DOMAIN_SUFFIX)) {
            return $this->formatter->formatIntl($catalogue->get($id, $domain), $locale, ['%count%' => $number] + $parameters);
        }
        return $this->formatter->choiceFormat($catalogue->get($id, $domain), $number, $locale, $parameters);
    }
    /**
     * {@inheritdoc}
     */
    public function getCatalogue($locale = null)
    {
        if (null === $locale) {
            $locale = $this->getLocale();
        } else {
            $this->assertValidLocale($locale);
        }
        if (!isset($this->catalogues[$locale])) {
            $this->loadCatalogue($locale);
        }
        return $this->catalogues[$locale];
    }
    /**
     * Gets the loaders.
     *
     * @return array LoaderInterface[]
     */
    protected function getLoaders()
    {
        return $this->loaders;
    }
    /**
     * @param string $locale
     */
    protected function loadCatalogue($locale)
    {
        if (null === $this->cacheDir) {
            $this->initializeCatalogue($locale);
        } else {
            $this->initializeCacheCatalogue($locale);
        }
    }
    /**
     * @param string $locale
     */
    protected function initializeCatalogue($locale)
    {
        $this->assertValidLocale($locale);
        try {
            $this->doLoadCatalogue($locale);
        } catch (\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\NotFoundResourceException $e) {
            if (!$this->computeFallbackLocales($locale)) {
                throw $e;
            }
        }
        $this->loadFallbackCatalogues($locale);
    }
    private function initializeCacheCatalogue(string $locale) : void
    {
        if (isset($this->catalogues[$locale])) {
            /* Catalogue already initialized. */
            return;
        }
        $this->assertValidLocale($locale);
        $cache = $this->getConfigCacheFactory()->cache($this->getCatalogueCachePath($locale), function (\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Config\ConfigCacheInterface $cache) use($locale) {
            $this->dumpCatalogue($locale, $cache);
        });
        if (isset($this->catalogues[$locale])) {
            /* Catalogue has been initialized as it was written out to cache. */
            return;
        }
        /* Read catalogue from cache. */
        $this->catalogues[$locale] = (include $cache->getPath());
    }
    private function dumpCatalogue(string $locale, \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Config\ConfigCacheInterface $cache) : void
    {
        $this->initializeCatalogue($locale);
        $fallbackContent = $this->getFallbackContent($this->catalogues[$locale]);
        $content = \sprintf(<<<EOF
<?php

use Symfony\\Component\\Translation\\MessageCatalogue;

\$catalogue = new MessageCatalogue('%s', %s);

%s
return \$catalogue;

EOF
, $locale, \var_export($this->getAllMessages($this->catalogues[$locale]), \true), $fallbackContent);
        $cache->write($content, $this->catalogues[$locale]->getResources());
    }
    private function getFallbackContent(\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue $catalogue) : string
    {
        $fallbackContent = '';
        $current = '';
        $replacementPattern = '/[^a-z0-9_]/i';
        $fallbackCatalogue = $catalogue->getFallbackCatalogue();
        while ($fallbackCatalogue) {
            $fallback = $fallbackCatalogue->getLocale();
            $fallbackSuffix = \ucfirst(\preg_replace($replacementPattern, '_', $fallback));
            $currentSuffix = \ucfirst(\preg_replace($replacementPattern, '_', $current));
            $fallbackContent .= \sprintf(<<<'EOF'
$catalogue%s = new MessageCatalogue('%s', %s);
$catalogue%s->addFallbackCatalogue($catalogue%s);

EOF
, $fallbackSuffix, $fallback, \var_export($this->getAllMessages($fallbackCatalogue), \true), $currentSuffix, $fallbackSuffix);
            $current = $fallbackCatalogue->getLocale();
            $fallbackCatalogue = $fallbackCatalogue->getFallbackCatalogue();
        }
        return $fallbackContent;
    }
    private function getCatalogueCachePath(string $locale) : string
    {
        return $this->cacheDir . '/catalogue.' . $locale . '.' . \strtr(\substr(\base64_encode(\hash('sha256', \serialize($this->cacheVary), \true)), 0, 7), '/', '_') . '.php';
    }
    /**
     * @internal
     */
    protected function doLoadCatalogue(string $locale) : void
    {
        $this->catalogues[$locale] = new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue($locale);
        if (isset($this->resources[$locale])) {
            foreach ($this->resources[$locale] as $resource) {
                if (!isset($this->loaders[$resource[0]])) {
                    throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\RuntimeException(\sprintf('The "%s" translation loader is not registered.', $resource[0]));
                }
                $this->catalogues[$locale]->addCatalogue($this->loaders[$resource[0]]->load($resource[1], $locale, $resource[2]));
            }
        }
    }
    private function loadFallbackCatalogues(string $locale) : void
    {
        $current = $this->catalogues[$locale];
        foreach ($this->computeFallbackLocales($locale) as $fallback) {
            if (!isset($this->catalogues[$fallback])) {
                $this->initializeCatalogue($fallback);
            }
            $fallbackCatalogue = new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue($fallback, $this->getAllMessages($this->catalogues[$fallback]));
            foreach ($this->catalogues[$fallback]->getResources() as $resource) {
                $fallbackCatalogue->addResource($resource);
            }
            $current->addFallbackCatalogue($fallbackCatalogue);
            $current = $fallbackCatalogue;
        }
    }
    protected function computeFallbackLocales($locale)
    {
        if (null === $this->parentLocales) {
            $parentLocales = \json_decode(\file_get_contents(__DIR__ . '/Resources/data/parents.json'), \true);
        }
        $locales = [];
        foreach ($this->fallbackLocales as $fallback) {
            if ($fallback === $locale) {
                continue;
            }
            $locales[] = $fallback;
        }
        while ($locale) {
            $parent = $parentLocales[$locale] ?? null;
            if (!$parent && \false !== \strrchr($locale, '_')) {
                $locale = \substr($locale, 0, -\strlen(\strrchr($locale, '_')));
            } elseif ('root' !== $parent) {
                $locale = $parent;
            } else {
                $locale = null;
            }
            if (null !== $locale) {
                \array_unshift($locales, $locale);
            }
        }
        return \array_unique($locales);
    }
    /**
     * Asserts that the locale is valid, throws an Exception if not.
     *
     * @param string $locale Locale to tests
     *
     * @throws InvalidArgumentException If the locale contains invalid characters
     */
    protected function assertValidLocale($locale)
    {
        if (1 !== \preg_match('/^[a-z0-9@_\\.\\-]*$/i', $locale)) {
            throw new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Exception\InvalidArgumentException(\sprintf('Invalid "%s" locale.', $locale));
        }
    }
    /**
     * Provides the ConfigCache factory implementation, falling back to a
     * default implementation if necessary.
     */
    private function getConfigCacheFactory() : \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Config\ConfigCacheFactoryInterface
    {
        if (!$this->configCacheFactory) {
            $this->configCacheFactory = new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Config\ConfigCacheFactory($this->debug);
        }
        return $this->configCacheFactory;
    }
    private function getAllMessages(\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogueInterface $catalogue) : array
    {
        $allMessages = [];
        foreach ($catalogue->all() as $domain => $messages) {
            if ($intlMessages = $catalogue->all($domain . \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue::INTL_DOMAIN_SUFFIX)) {
                $allMessages[$domain . \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageCatalogue::INTL_DOMAIN_SUFFIX] = $intlMessages;
                $messages = \array_diff_key($messages, $intlMessages);
            }
            if ($messages) {
                $allMessages[$domain] = $messages;
            }
        }
        return $allMessages;
    }
}
