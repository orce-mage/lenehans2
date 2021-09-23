<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


namespace Amasty\Number\Exceptions;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Phrase;

class NumberGenerationFailure extends LocalizedException
{
    public function __construct(Phrase $phrase = null, \Exception $cause = null, $code = 0)
    {
        if (!$phrase) {
            $phrase = __('Unable to properly generate next increment id');
        }

        parent::__construct($phrase, $cause, $code);
    }
}
