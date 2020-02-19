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

/**
 * IdentityTranslator does not translate anything.
 *
 * @author Fabien Potencier <fabien@symfony.com>
 */
class IdentityTranslator implements \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\TranslatorInterface
{
    private $selector;
    private $locale;
    /**
     * @param MessageSelector|null $selector The message selector for pluralization
     */
    public function __construct(\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageSelector $selector = null)
    {
        $this->selector = $selector ?: new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageSelector();
    }
    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }
    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->locale ?: \Locale::getDefault();
    }
    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = [], $domain = null, $locale = null)
    {
        return \strtr((string) $id, $parameters);
    }
    /**
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = [], $domain = null, $locale = null)
    {
        return \strtr($this->selector->choose((string) $id, (int) $number, $locale ?: $this->getLocale()), $parameters);
    }
}
