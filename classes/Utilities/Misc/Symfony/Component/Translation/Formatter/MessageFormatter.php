<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Formatter;

use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\IdentityTranslator;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageSelector;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\TranslatorInterface as LegacyTranslatorInterface;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Contracts\Translation\TranslatorInterface;
/**
 * @author Abdellatif Ait boudad <a.aitboudad@gmail.com>
 */
class MessageFormatter implements \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Formatter\MessageFormatterInterface, \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Formatter\IntlFormatterInterface, \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Formatter\ChoiceMessageFormatterInterface
{
    private $translator;
    private $intlFormatter;
    /**
     * @param TranslatorInterface|null $translator An identity translator to use as selector for pluralization
     */
    public function __construct($translator = null, \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Formatter\IntlFormatterInterface $intlFormatter = null)
    {
        if ($translator instanceof \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\MessageSelector) {
            $translator = new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\IdentityTranslator($translator);
        } elseif (null !== $translator && !$translator instanceof \ILAB\MediaCloud\Utilities\Misc\Symfony\Contracts\Translation\TranslatorInterface && !$translator instanceof \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\TranslatorInterface) {
            throw new \TypeError(\sprintf('Argument 1 passed to %s() must be an instance of %s, %s given.', __METHOD__, \ILAB\MediaCloud\Utilities\Misc\Symfony\Contracts\Translation\TranslatorInterface::class, \is_object($translator) ? \get_class($translator) : \gettype($translator)));
        }
        $this->translator = $translator ?? new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\IdentityTranslator();
        $this->intlFormatter = $intlFormatter ?? new \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\Formatter\IntlFormatter();
    }
    /**
     * {@inheritdoc}
     */
    public function format($message, $locale, array $parameters = [])
    {
        if ($this->translator instanceof \ILAB\MediaCloud\Utilities\Misc\Symfony\Contracts\Translation\TranslatorInterface) {
            return $this->translator->trans($message, $parameters, null, $locale);
        }
        return \strtr($message, $parameters);
    }
    /**
     * {@inheritdoc}
     */
    public function formatIntl(string $message, string $locale, array $parameters = []) : string
    {
        return $this->intlFormatter->formatIntl($message, $locale, $parameters);
    }
    /**
     * {@inheritdoc}
     *
     * @deprecated since Symfony 4.2, use format() with a %count% parameter instead
     */
    public function choiceFormat($message, $number, $locale, array $parameters = [])
    {
        @\trigger_error(\sprintf('The "%s()" method is deprecated since Symfony 4.2, use the format() one instead with a %%count%% parameter.', __METHOD__), \E_USER_DEPRECATED);
        $parameters = ['%count%' => $number] + $parameters;
        if ($this->translator instanceof \ILAB\MediaCloud\Utilities\Misc\Symfony\Contracts\Translation\TranslatorInterface) {
            return $this->format($message, $locale, $parameters);
        }
        return $this->format($this->translator->transChoice($message, $number, [], null, $locale), $locale, $parameters);
    }
}
