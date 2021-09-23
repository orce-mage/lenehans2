<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Model\Location\Filter;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\Config\Source\ConditionType;
use Amasty\StorePickupWithLocator\Api\Filter\LocationProductFilterInterface;
use Amasty\StorePickupWithLocatorMSI\Model\ConfigProvider;
use Amasty\StorePickupWithLocatorMSI\Model\Location\GetLocationIdsByProducts;
use Amasty\StorePickupWithLocatorMSI\Plugin\Storelocator\Model\Config\Source\ConditionTypePlugin;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Session\SessionManagerInterface as CheckoutSession;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\Quote\Item;

class MSIConditionFilter implements LocationProductFilterInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var GetLocationIdsByProducts
     */
    private $getLocationIdsByProducts;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    public function __construct(
        ConfigProvider $configProvider,
        GetLocationIdsByProducts $getLocationIdsByProducts,
        CheckoutSession $checkoutSession
    ) {
        $this->configProvider = $configProvider;
        $this->getLocationIdsByProducts = $getLocationIdsByProducts;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $productIds
     * @param int $storeId
     */
    public function apply(SearchCriteriaBuilder $searchCriteriaBuilder, array $productIds, int $storeId): void
    {
        if (!$this->configProvider->isShowLocationsWithMsi()) {
            return;
        }

        if ($this->configProvider->isIncludeOutOfStockLocations()) {
            $searchCriteriaBuilder->addFilter(LocationInterface::CONDITION_TYPE, ConditionTypePlugin::MSI_SOURCE);

            return;
        }

        $locationIds = $this->getLocationIdsByProducts->getAvailableLocationIds(
            $this->getSkusWithQtysByProductIds($productIds),
            $storeId
        );

        if (!$locationIds) {
            return;
        }

        $searchCriteriaBuilder->addFilter(LocationInterface::ID, $locationIds, 'in');
    }

    /**
     * @param array $productIds
     * @return array
     */
    private function getSkusWithQtysByProductIds(array $productIds): array
    {
        $skus = [];
        if (($this->checkoutSession->getQuoteId() || $this->checkoutSession->hasQuote())) {
            /** @var Quote $quote */
            $quote = $this->checkoutSession->getQuote();
            /** @var Item $item */
            foreach ($quote->getAllItems() as $item) {
                if ($item->getChildren()) {
                    continue;
                }

                if (in_array($item->getProductId(), $productIds)) {
                    $skus[$item->getSku()] = $item->getQty();
                    if ($item->getParentItem()) {
                        $skus[$item->getSku()] *= $item->getParentItem()->getQty();
                    }
                }
            }
        }

        return $skus;
    }
}
