<?php
namespace Swissup\SoldTogether\Model\ResourceModel;

/**
 * SoldTogether Customer mysql resource
 */
class Customer extends AbstractResourceModel
{
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('swissup_soldtogether_customer', 'relation_id');
    }

    public function getCustomerOrderIds($count, $step)
    {
        $connection = $this->getConnection();

        $customerSelect = $connection->select()
            ->from($this->getTable('customer_entity'), 'email')
            ->order('entity_id')
            ->limit($count, $count * ($step - 1));

        $customerMail = $connection->fetchCol($customerSelect);

        $customerMail = array_filter($customerMail);

        $select = $connection->select()
            ->from(
                ['soi' => $this->getTable('sales_order_item')],
                ['parent_item_id', 'name']
            )
            ->join(
                ['so' => $this->getTable('sales_order')],
                'so.entity_id = soi.order_id',
                ['customer_email', 'store_id']
            )
            ->joinInner(
                ['cp' => $this->getTable('catalog_product_entity')],
                'cp.sku = soi.sku',
                ['product_id' => 'cp.entity_id']
            )
            ->where('so.customer_email IN (?)', $customerMail)
            ->order(['order_id', 'product_id']);
        $result = [];

        foreach ($connection->fetchAll($select) as $row) {
            if (!$row['parent_item_id']) {
                $result[$row['product_id']] = ['name' => $row['name'], 'store' => $row['store_id']];
            }
        }

        return $result;
    }

    public function getCustomerNewOrderIds($customerEmail, $orderId, $storeId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()
            ->from(
                ['soi' => $this->getTable('sales_order_item')],
                ['product_id', 'parent_item_id', 'name']
            )
            ->join(
                ['so' => $this->getTable('sales_order')],
                'so.entity_id = soi.order_id',
                ['customer_email', 'store_id']
            )
            ->joinInner(
                ['cp' => $this->getTable('catalog_product_entity')],
                'cp.entity_id = soi.product_id',
                []
            )
            ->where('so.entity_id <> ?', $orderId)
            ->where('so.customer_email = ?', $customerEmail)
            ->where('so.store_id = ?', $storeId)
            ->order(['order_id', 'product_id']);
        $result = [];

        foreach ($connection->fetchAll($select) as $row) {
            if (!$row['parent_item_id']) {
                $result[$row['product_id']] = ['name' => $row['name'], 'store' => $row['store_id']];
            }
        }

        return $result;
    }
}
