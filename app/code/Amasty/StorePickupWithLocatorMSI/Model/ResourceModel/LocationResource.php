<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Model\ResourceModel;

use Amasty\Storelocator\Model\ResourceModel\Location;
use Amasty\StorePickupWithLocatorMSI\Api\Data\LocationSourceInterface;
use Magento\InventoryApi\Api\Data\SourceInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

class LocationResource
{
    /**
     * @var Location
     */
    private $locationResource;

    public function __construct(Location $locationResource)
    {
        $this->locationResource = $locationResource;
    }

    /**
     * @param array $skus
     * @param int $stockId
     * @return array
     */
    public function getProductsLocationData(array $skus, int $stockId): array
    {
        $select = $this->locationResource->getConnection()->select();
        $resource = $this->locationResource;
        $select->from(
            ['isi' => $resource->getTable('inventory_source_item')],
            [
                'sku' => 'isi.sku',
                'location_id' => 'ls.' . LocationSourceInterface::LOCATION_ID,
                'qty' => new \Zend_Db_Expr('SUM(isi.' . SourceItemInterface::QUANTITY . ')'),
            ]
        );
        $select->joinInner(
            ['ls' => $resource->getTable(LocationSource::TABLE_NAME)],
            'isi.' . SourceItemInterface::SOURCE_CODE . ' = ls.' . LocationSourceInterface::SOURCE_CODE,
            []
        );

        $select->joinInner(
            ['i_s' => $resource->getTable('inventory_source')],
            'isi.' . SourceItemInterface::SOURCE_CODE . ' = i_s.' . SourceInterface::SOURCE_CODE
            . ' AND i_s.' . SourceInterface::ENABLED . ' = 1',
            []
        );

        $select->joinInner(
            ['issl' => $resource->getTable('inventory_source_stock_link')],
            'issl.source_code = i_s.' . SourceInterface::SOURCE_CODE . ' AND issl.stock_id = ' . $stockId,
            []
        );

        $select->where('isi.sku IN(?)', $skus);
        $select->where('isi.' . SourceItemInterface::STATUS . ' = ?', SourceItemInterface::STATUS_IN_STOCK);

        $select->group(['isi.sku', 'ls.' . LocationSourceInterface::LOCATION_ID]);

        return $resource->getConnection()->fetchAll($select);
    }
}
