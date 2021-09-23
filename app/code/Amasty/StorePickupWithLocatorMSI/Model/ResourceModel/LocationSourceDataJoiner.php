<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Model\ResourceModel;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\ResourceModel\Location;
use Amasty\StorePickupWithLocatorMSI\Api\Data\LocationSourceInterface;
use Amasty\StorePickupWithLocatorMSI\Model\StockIdResolver;
use Magento\Framework\DB\Select;
use Magento\InventoryApi\Api\Data\SourceInterface;

class LocationSourceDataJoiner
{
    /**
     * @var Location
     */
    private $locationSource;

    /**
     * @var StockIdResolver
     */
    private $stockIdResolver;

    public function __construct(
        Location $locationSource,
        StockIdResolver $stockIdResolver
    ) {
        $this->locationSource = $locationSource;
        $this->stockIdResolver = $stockIdResolver;
    }

    /**
     * @param Select $select
     * @param int $storeId
     */
    public function joinData(Select $select, int $storeId): void
    {
        $stockId = $this->stockIdResolver->getStockId($storeId);

        $select->joinInner(
            ['ls' => $this->locationSource->getTable(LocationSource::TABLE_NAME)],
            'main_table.' . LocationInterface::ID . ' = ls.' . LocationSourceInterface::LOCATION_ID,
            []
        );

        $select->joinInner(
            ['i_s' => $this->locationSource->getTable('inventory_source')],
            'ls.' . LocationSourceInterface::SOURCE_CODE . ' = i_s.' . SourceInterface::SOURCE_CODE
            . ' AND i_s.' . SourceInterface::ENABLED . ' = 1',
            []
        );

        $select->joinInner(
            ['issl' => $this->locationSource->getTable('inventory_source_stock_link')],
            'ls.' . LocationSourceInterface::SOURCE_CODE . ' = issl.source_code AND issl.stock_id = ' . $stockId,
            []
        );
    }
}
