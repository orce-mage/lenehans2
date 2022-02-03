<?php

namespace Amasty\Faq\Exceptions;

class DuplicateUrlKeyException extends \Magento\Framework\Exception\LocalizedException
{
    public function __construct(\Magento\Framework\Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        if (!$phrase) {
            $phrase = __('Duplicate Url key');
        }
        parent::__construct($phrase, $cause, (int) $code);
    }
}
