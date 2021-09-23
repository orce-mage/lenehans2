<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Report Sold Products collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */
namespace MageBig\WidgetPlus\Model\ResourceModel\Widget\Sold;

/**
 * @SuppressWarnings(PHPMD.DepthOfInheritance)
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Reports\Model\ResourceModel\Product\Sold\Collection
{
    /**
     * @param $pId
     * @return string
     */
    public function addSoldQty($pId)
    {
        $connection = $this->getConnection();
        $orderTableAliasName = $connection->quoteIdentifier('order');

        $orderJoinCondition = [
            $orderTableAliasName . '.entity_id = order_items.order_id',
            $connection->quoteInto("{$orderTableAliasName}.state <> ?", \Magento\Sales\Model\Order::STATE_CANCELED),
        ];

        $select = $this->getSelect()->reset()->from(
            ['order_items' => $this->getTable('sales_order_item')],
            ['ordered_qty' => 'SUM(order_items.qty_ordered)', 'order_items_name' => 'order_items.name', 'order_items_id' => 'order_items.product_id']
        )->where(
            'order_items.product_id = ?',
            $pId
        )->joinInner(
            ['order' => $this->getTable('sales_order')],
            implode(' AND ', $orderJoinCondition),
            []
        )->where(
            'parent_item_id IS NULL'
        )->group(
            'order_items.product_id'
        )->having(
            'SUM(order_items.qty_ordered) > ?',
            0
        );

        /*$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info($connection->fetchOne($select));*/

        return $connection->fetchOne($select);
    }
}
