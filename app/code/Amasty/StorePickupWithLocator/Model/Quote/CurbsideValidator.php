<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */

declare(strict_types=1);

namespace Amasty\StorePickupWithLocator\Model\Quote;

use Amasty\Storelocator\Model\Location;
use Amasty\Storelocator\Model\LocationFactory;
use Amasty\StorePickupWithLocator\Api\Data\QuoteInterface;
use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\StringUtils;

class CurbsideValidator
{
    const COMMENT_MAX_LENGTH = 300;

    /**
     * @var StringUtils
     */
    private $stringUtils;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LocationFactory
     */
    private $locationFactory;

    /**
     * @var Location
     */
    private $location;

    public function __construct(
        StringUtils $stringUtils,
        ConfigProvider $configProvider,
        LocationFactory $locationFactory
    ) {
        $this->stringUtils = $stringUtils;
        $this->configProvider = $configProvider;
        $this->locationFactory = $locationFactory;
    }

    /**
     * @param QuoteInterface $pickupQuoteData
     * @return bool
     * @throws InputException
     */
    public function validateComment(QuoteInterface $pickupQuoteData): bool
    {
        if ($this->configProvider->isCurbsideCheckboxEnabled() && !$pickupQuoteData->getIsCurbsidePickup()) {
            return true;
        }

        $location = $this->getLocation((int)$pickupQuoteData->getStoreId());

        if ($location->getCurbsideEnabled() && $this->configProvider->isCurbsideCommentsEnabled()) {
            $commentLength = $this->stringUtils->strlen($pickupQuoteData->getCurbsidePickupComment());

            if ($commentLength > self::COMMENT_MAX_LENGTH) {
                throw new InputException(
                    __(
                        'Comment length limit is exceeded. Please keep it less than %1 symbols.',
                        self::COMMENT_MAX_LENGTH
                    )
                );
            }

            if ($this->configProvider->isCurbsideCommentRequired() && $commentLength === 0) {
                throw new InputException(
                    __('Please fill in the comment field.')
                );
            }
        }

        return true;
    }

    /**
     * @param QuoteInterface $pickupQuoteData
     * @return bool
     */
    public function shouldSaveComment(QuoteInterface $pickupQuoteData): bool
    {
        if ($this->configProvider->isCurbsideCheckboxEnabled() && !$pickupQuoteData->getIsCurbsidePickup()) {
            return false;
        }

        $location = $this->getLocation((int)$pickupQuoteData->getStoreId());

        return $location->getCurbsideEnabled() && $this->configProvider->isCurbsideCommentsEnabled();
    }

    /**
     * @param QuoteInterface $pickupQuoteData
     * @return bool
     */
    public function shouldSaveCurbsideValue(QuoteInterface $pickupQuoteData): bool
    {
        $location = $this->getLocation((int)$pickupQuoteData->getStoreId());

        return $location->getCurbsideEnabled() && $this->configProvider->isCurbsideCheckboxEnabled();
    }

    /**
     * @param int $locationId
     * @return Location
     */
    private function getLocation(int $locationId): Location
    {
        if ($this->location === null) {
            $this->location = $this->locationFactory->create();
            $this->location->load($locationId);
        }

        return $this->location;
    }
}
