<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Model;

use Amasty\StorePickupWithLocator\Api\Data\QuoteInterface;
use Amasty\StorePickupWithLocator\Api\GuestLocationPickupValuesInterface;
use Amasty\StorePickupWithLocator\Api\LocationPickupValuesInterface;
use Magento\Quote\Model\QuoteIdMask;
use Magento\Quote\Model\QuoteIdMaskFactory;

class GuestLocationPickupValues implements GuestLocationPickupValuesInterface
{
    /**
     * @var QuoteIdMaskFactory
     */
    private $quoteIdMaskFactory;

    /**
     * @var LocationPickupValuesInterface
     */
    private $locationPickupValues;

    public function __construct(
        QuoteIdMaskFactory $quoteIdMaskFactory,
        LocationPickupValuesInterface $locationPickupValues
    ) {
        $this->quoteIdMaskFactory = $quoteIdMaskFactory;
        $this->locationPickupValues = $locationPickupValues;
    }

    /**
     * @deprecated
     * @param string $cartId
     * @param int $locationId
     * @param string|null $date
     * @param string|null $timePeriod
     *
     * @return mixed
     */
    public function saveSelectedPickupValues(
        $cartId,
        $locationId,
        $date = null,
        $timePeriod = null
    ) {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->locationPickupValues->saveSelectedPickupValues(
            $quoteIdMask->getQuoteId(),
            $locationId,
            $date,
            $timePeriod
        );
    }

    /**
     * @param string $cartId
     * @param QuoteInterface $quotePickupData
     * @return bool
     */
    public function saveSelectedPickupData(
        string $cartId,
        QuoteInterface $quotePickupData
    ): bool {
        /** @var $quoteIdMask QuoteIdMask */
        $quoteIdMask = $this->quoteIdMaskFactory->create()->load($cartId, 'masked_id');

        return $this->locationPickupValues->saveSelectedPickupData(
            (int)$quoteIdMask->getQuoteId(),
            $quotePickupData
        );
    }
}
