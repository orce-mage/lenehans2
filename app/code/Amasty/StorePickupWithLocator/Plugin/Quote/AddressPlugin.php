<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Plugin\Quote;

use Amasty\StorePickupWithLocator\Model\Quote\QuoteAddressResolver;

/**
 * Plugin for fill empty data
 */
class AddressPlugin
{
    /**
     * @var AddressHelper
     */
    private $quoteAddressResolver;

    public function __construct(
        QuoteAddressResolver $quoteAddressResolver
    ) {
        $this->quoteAddressResolver = $quoteAddressResolver;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address $subject
     * @param $result
     *
     * @return mixed
     */
    public function afterAddData(
        \Magento\Quote\Model\Quote\Address $subject,
        $result
    ) {
        $this->quoteAddressResolver->fillEmpty($subject);

        return $result;
    }
}
