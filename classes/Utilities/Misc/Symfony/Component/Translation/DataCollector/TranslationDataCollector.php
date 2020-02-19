<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\DataCollector;

use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\HttpFoundation\Request;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\HttpFoundation\Response;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\HttpKernel\DataCollector\DataCollector;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface;
use ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\DataCollectorTranslator;
/**
 * @author Abdellatif Ait boudad <a.aitboudad@gmail.com>
 */
class TranslationDataCollector extends \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\HttpKernel\DataCollector\DataCollector implements \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\HttpKernel\DataCollector\LateDataCollectorInterface
{
    private $translator;
    public function __construct(\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\DataCollectorTranslator $translator)
    {
        $this->translator = $translator;
    }
    /**
     * {@inheritdoc}
     */
    public function lateCollect()
    {
        $messages = $this->sanitizeCollectedMessages($this->translator->getCollectedMessages());
        $this->data = $this->computeCount($messages);
        $this->data['messages'] = $messages;
        $this->data['locale'] = $this->translator->getLocale();
        $this->data['fallback_locales'] = $this->translator->getFallbackLocales();
        $this->data = $this->cloneVar($this->data);
    }
    /**
     * {@inheritdoc}
     */
    public function collect(\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\HttpFoundation\Request $request, \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\HttpFoundation\Response $response, \Exception $exception = null)
    {
    }
    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->data = [];
    }
    /**
     * @return array|Data
     */
    public function getMessages()
    {
        return isset($this->data['messages']) ? $this->data['messages'] : [];
    }
    /**
     * @return int
     */
    public function getCountMissings()
    {
        return isset($this->data[\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\DataCollectorTranslator::MESSAGE_MISSING]) ? $this->data[\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\DataCollectorTranslator::MESSAGE_MISSING] : 0;
    }
    /**
     * @return int
     */
    public function getCountFallbacks()
    {
        return isset($this->data[\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\DataCollectorTranslator::MESSAGE_EQUALS_FALLBACK]) ? $this->data[\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\DataCollectorTranslator::MESSAGE_EQUALS_FALLBACK] : 0;
    }
    /**
     * @return int
     */
    public function getCountDefines()
    {
        return isset($this->data[\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\DataCollectorTranslator::MESSAGE_DEFINED]) ? $this->data[\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\DataCollectorTranslator::MESSAGE_DEFINED] : 0;
    }
    public function getLocale()
    {
        return !empty($this->data['locale']) ? $this->data['locale'] : null;
    }
    public function getFallbackLocales()
    {
        return isset($this->data['fallback_locales']) && \count($this->data['fallback_locales']) > 0 ? $this->data['fallback_locales'] : [];
    }
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'translation';
    }
    private function sanitizeCollectedMessages($messages)
    {
        $result = [];
        foreach ($messages as $key => $message) {
            $messageId = $message['locale'] . $message['domain'] . $message['id'];
            if (!isset($result[$messageId])) {
                $message['count'] = 1;
                $message['parameters'] = !empty($message['parameters']) ? [$message['parameters']] : [];
                $messages[$key]['translation'] = $this->sanitizeString($message['translation']);
                $result[$messageId] = $message;
            } else {
                if (!empty($message['parameters'])) {
                    $result[$messageId]['parameters'][] = $message['parameters'];
                }
                ++$result[$messageId]['count'];
            }
            unset($messages[$key]);
        }
        return $result;
    }
    private function computeCount($messages)
    {
        $count = [\ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\DataCollectorTranslator::MESSAGE_DEFINED => 0, \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\DataCollectorTranslator::MESSAGE_MISSING => 0, \ILAB\MediaCloud\Utilities\Misc\Symfony\Component\Translation\DataCollectorTranslator::MESSAGE_EQUALS_FALLBACK => 0];
        foreach ($messages as $message) {
            ++$count[$message['state']];
        }
        return $count;
    }
    private function sanitizeString($string, $length = 80)
    {
        $string = \trim(\preg_replace('/\\s+/', ' ', $string));
        if (\false !== ($encoding = \mb_detect_encoding($string, null, \true))) {
            if (\mb_strlen($string, $encoding) > $length) {
                return \mb_substr($string, 0, $length - 3, $encoding) . '...';
            }
        } elseif (\strlen($string) > $length) {
            return \substr($string, 0, $length - 3) . '...';
        }
        return $string;
    }
}
