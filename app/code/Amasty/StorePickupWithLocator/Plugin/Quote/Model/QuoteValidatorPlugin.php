<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Plugin\Quote\Model;

use Magento\Quote\Model\QuoteValidator;
use Magento\Quote\Model\Quote as QuoteEntity;
use Amasty\StorePickupWithLocator\Model\Quote\SetIgnoreShippingValidationForQuote;

/**
 * Disable Shipping Validation for magento version 2.2.x
 */
class QuoteValidatorPlugin
{
    /**
     * @var SetIgnoreShippingValidationForQuote $validationDisabler
     */
    private $validationDisabler;

    public function __construct(SetIgnoreShippingValidationForQuote $validationDisabler)
    {
        $this->validationDisabler = $validationDisabler;
    }

    /**
     * @param QuoteValidator $subject
     * @param QuoteEntity $quote
     */
    public function beforeValidateBeforeSubmit(
        QuoteValidator $subject,
        QuoteEntity $quote
    ) {
        $this->validationDisabler->execute($quote);
    }
}
