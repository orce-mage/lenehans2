<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Model\Location;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\ResourceModel\Location\Collection;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Amasty\StorePickupWithLocatorMSI\Api\Data\LocationSourceInterface;
use Amasty\StorePickupWithLocatorMSI\Model\ResourceModel\LocationSourceDataJoiner;
use Amasty\StorePickupWithLocatorMSI\Plugin\Storelocator\Model\Config\Source\ConditionTypePlugin;
use Magento\InventoryApi\Api\Data\SourceItemInterface;
use Magento\Store\Model\Store;

class GetLocationsByProduct
{
    const QTY_IN_STOCK = 'qty_in_stock';

    /**
     * @var CollectionFactory
     */
    private $locationCollectionFactory;

    /**
     * @var LocationSourceDataJoiner
     */
    private $locationSourceDataJoiner;

    public function __construct(
        CollectionFactory $locationCollectionFactory,
        LocationSourceDataJoiner $locationSourceDataJoiner
    ) {
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->locationSourceDataJoiner = $locationSourceDataJoiner;
    }

    /**
     * @param string $productSku
     * @param int $storeId
     * @param bool $inStockOnly
     * @return Collection
     */
    public function getLocationsByProduct(string $productSku, int $storeId, bool $inStockOnly): Collection
    {
        $collection = $this->locationCollectionFactory->create();

        $collection->addFilterByStores([Store::DEFAULT_STORE_ID, $storeId]);
        $collection->addFieldToFilter(
            'main_table.' . LocationInterface::STATUS,
            LocationInterface::STATUS_ENABLED
        );
        $collection->addFieldToFilter(
            'main_table.' . LocationInterface::CONDITION_TYPE,
            ConditionTypePlugin::MSI_SOURCE
        );

        $select = $collection->getSelect();
        $resource = $collection->getResource();

        $this->locationSourceDataJoiner->joinData($select, $storeId);
        $select->joinInner(
            ['s_item' => $resource->getTable('inventory_source_item')],
            'ls.' . LocationSourceInterface::SOURCE_CODE . ' = s_item.' . SourceItemInterface::SOURCE_CODE,
            [
                self::QTY_IN_STOCK => new \Zend_Db_Expr(
                    'SUM(
                        IF(s_item.' . SourceItemInterface::STATUS . '=' . SourceItemInterface::STATUS_IN_STOCK . ',
                            s_item.' . SourceItemInterface::QUANTITY . ',
                        0)
                    )'
                )
            ]
        );
        $select->where('s_item.' . SourceItemInterface::SKU . ' = ?', $productSku);

        if ($inStockOnly) {
            $select->where(
                's_item.' . SourceItemInterface::STATUS . ' = ?',
                SourceItemInterface::STATUS_IN_STOCK
            );
            $select->where('s_item.' . SourceItemInterface::QUANTITY . ' > 0');
        }

        $select->group(LocationInterface::ID);

        return $collection;
    }
}
