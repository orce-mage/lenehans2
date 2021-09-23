<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Resources;

use Magento\Framework\App\ResourceConnection;

class CheckIfProductsManageStock
{
    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    public function __construct(ResourceConnection $resourceConnection)
    {
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * Remove products which manage_stock=0 from given array.
     * Return array which contains only products with manage_stock.
     *
     * @param array $productIds
     * @param bool $globalManageStock
     * @return array
     */
    public function execute(array $productIds, bool $globalManageStock): array
    {
        $select = $this->resourceConnection->getConnection()->select()->from(
            ['csi' => $this->resourceConnection->getTableName('cataloginventory_stock_item')],
            ['product_id']
        )->where('product_id IN (?)', $productIds);

        if ($globalManageStock) {
            $select->where('csi.use_config_manage_stock = 1 OR csi.manage_stock = 1');
        } else {
            $select->where('csi.use_config_manage_stock = 0 AND csi.manage_stock = 1');
        }

        return $this->resourceConnection->getConnection()->fetchCol($select);
    }
}
