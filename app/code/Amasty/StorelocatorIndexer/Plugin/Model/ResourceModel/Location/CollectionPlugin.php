<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorelocatorIndexer
 */


namespace Amasty\StorelocatorIndexer\Plugin\Model\ResourceModel\Location;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\Config\Source\ConditionType;
use Amasty\Storelocator\Model\ResourceModel\Location\Collection;
use Amasty\StorelocatorIndexer\Model\ResourceModel\LocationProductIndex;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @TODO: need to do it more flexible
 */
class CollectionPlugin
{
    /**
     * @var LocationProductIndex
     */
    private $locationProductIndex;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        LocationProductIndex $locationProductIndex,
        StoreManagerInterface $storeManager
    ) {
        $this->locationProductIndex = $locationProductIndex;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Collection $subject
     * @param \Closure $proceed
     * @param int|array|string $productId
     * @param int|array|string $storeIds
     */
    public function aroundFilterLocationsByProduct(
        Collection $subject,
        \Closure $proceed,
        $productId,
        $storeIds
    ) {
        $locationIds = [];
        $fields = $this->locationProductIndex->getLocationsByProduct($productId, [Store::DEFAULT_STORE_ID, $storeIds]);
        foreach ($fields as $field) {
            $locationIds[] = $field[LocationProductIndex::LOCATION_ID];
        }
        $subject->addFieldToFilter(
            [
                'main_table.id',
                'main_table.' . LocationInterface::CONDITION_TYPE
            ],
            [
                ['in' => $locationIds],
                ConditionType::NO_CONDITIONS
            ]
        );
    }

    /**
     * @param Collection $subject
     * @param \Closure $proceed
     * @param int $categoryId
     * @param int|array|string $storeIds
     */
    public function aroundFilterLocationsByCategory(
        Collection $subject,
        \Closure $proceed,
        $categoryId,
        $storeIds
    ) {
        $locationIds = [];
        $fields = $this->locationProductIndex->getLocationsByCategory(
            $categoryId,
            [Store::DEFAULT_STORE_ID, $storeIds]
        );
        foreach ($fields as $field) {
            $locationIds[] = $field[LocationProductIndex::LOCATION_ID];
        }
        $subject->addFieldToFilter(
            [
                'main_table.id',
                'main_table.' . LocationInterface::CONDITION_TYPE
            ],
            [
                ['in' => $locationIds],
                ConditionType::NO_CONDITIONS
            ]
        );
    }
}
