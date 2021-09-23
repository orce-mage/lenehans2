<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Plugin\Quote;

use Amasty\StorePickupWithLocator\Model\Quote\QuoteAddressResolver;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Checkout\Api\Data\ShippingInformationInterface;

/**
 * Plugin for fill empty data, data will be equals with frontend
 * in file Amasty/StorePickupWithLocator/view/frontend/web/js/model/klarna-mixin.js
 */
class AddressDataPlugin
{
    /**
     * @var QuoteAddressResolver
     */
    private $quoteAddressResolver;

    public function __construct(
        QuoteAddressResolver $quoteAddressResolver
    ) {
        $this->quoteAddressResolver = $quoteAddressResolver;
    }

    /**
     * @param ShippingInformationManagement $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     *
     * @return array
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        foreach ([$addressInformation->getShippingAddress(), $addressInformation->getBillingAddress()] as $address) {
            $this->quoteAddressResolver->fillEmpty($address);
        }

        return [$cartId, $addressInformation];
    }
}
