<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Model\Quote;

use Amasty\StorePickupWithLocator\Model\Carrier\Shipping;

class SetIgnoreShippingValidationForQuote
{
    /**
     * Disable Shipping Validation
     *
     * @param \Magento\Quote\Model\Quote $quote
     */
    public function execute($quote)
    {
        if ($quote->getShippingAddress()->getShippingMethod() === Shipping::SHIPPING_NAME) {
            $shippingAddress = $quote->getShippingAddress();
            $shippingAddress->setShouldIgnoreValidation(true);
        }
    }
}
