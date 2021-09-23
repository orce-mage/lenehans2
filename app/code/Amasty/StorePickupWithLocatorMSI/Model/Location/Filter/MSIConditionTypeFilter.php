<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Model\Location\Filter;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\StorePickupWithLocator\Api\Filter\LocationProductFilterInterface;
use Amasty\StorePickupWithLocatorMSI\Model\ConfigProvider;
use Amasty\StorePickupWithLocatorMSI\Model\Location\LocationWithSourceChecker;
use Amasty\StorePickupWithLocatorMSI\Plugin\Storelocator\Model\Config\Source\ConditionTypePlugin;
use Magento\Framework\Api\SearchCriteriaBuilder;

class MSIConditionTypeFilter implements LocationProductFilterInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LocationWithSourceChecker
     */
    private $locationWithSourceChecker;

    public function __construct(
        ConfigProvider $configProvider,
        LocationWithSourceChecker $locationWithSourceChecker
    ) {
        $this->configProvider = $configProvider;
        $this->locationWithSourceChecker = $locationWithSourceChecker;
    }

    /**
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param array $productIds
     * @param int $storeId
     */
    public function apply(SearchCriteriaBuilder $searchCriteriaBuilder, array $productIds, int $storeId): void
    {
        if (!$this->configProvider->isShowLocationsWithMsi()
            || !$this->locationWithSourceChecker->isExists($storeId)
        ) {
            return;
        }

        $searchCriteriaBuilder->addFilter(LocationInterface::CONDITION_TYPE, ConditionTypePlugin::MSI_SOURCE);
    }
}
