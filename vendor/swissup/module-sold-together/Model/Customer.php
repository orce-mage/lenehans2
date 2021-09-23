<?php

namespace Swissup\SoldTogether\Model;

class Customer extends AbstractModel
{
    /**
     * @var string
     */
    protected $_cacheTag = 'soldtogether_Customer';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'soldtogether_Customer';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\SoldTogether\Model\ResourceModel\Customer');
    }

    public function getCustomerOrderIds($count, $step)
    {
        return $this->_getResource()->getCustomerOrderIds($count, $step);
    }

    public function createNewRelations($order)
    {
        $customerEmail = $order->getCustomerEmail();
        $storeId = $order->getStoreId();
        $orderId = $order->getId();
        $productIds = $this->_getResource()->getCustomerNewOrderIds($customerEmail, $orderId, $storeId);

        $result = [];
        $visibleItems = $order->getAllVisibleItems();

        foreach ($visibleItems as $product) {
            foreach ($productIds as $relatedId => $relatedData) {
                if ($product->getProductId() == $relatedId) {
                    continue;
                }
                $result[] = [
                    'product_id'   => $product->getProductId(),
                    'related_id'   => $relatedId,
                    'product_name' => $product->getName(),
                    'related_name' => $relatedData['name'],
                    'store_id'     => 0,
                    'weight'       => 1,
                    'is_admin'     => 0
                ];
            }
        }

        // add data to db
        foreach ($result as $item) {
            $this->addData($item)
                ->loadRelation(
                    $item['product_id'],
                    $item['related_id'],
                    $item['store_id']
                )
                ->setWeight($this->getWeight() + 1)
                ->save();
        }
    }
}
