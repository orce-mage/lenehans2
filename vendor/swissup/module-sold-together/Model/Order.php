<?php

namespace Swissup\SoldTogether\Model;

class Order extends AbstractModel
{
    /**
     * @var string
     */
    protected $_cacheTag = 'soldtogether_Order';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'soldtogether_Order';

    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Swissup\SoldTogether\Model\ResourceModel\Order');
    }

    public function createNewRelations($order)
    {
        $storeId = $order->getStoreId();
        $visibleItems = $order->getAllVisibleItems();
        $orderProducts = [];
        $result = [];
        if (count($visibleItems) > 1) {
            foreach ($visibleItems as $product) {
                $orderProducts[$product->getProductId()] = $product->getName();
            }
            foreach ($orderProducts as $productId => $productName) {
                foreach ($orderProducts as $relatedId => $relatedName) {
                    if ($productId == $relatedId) {
                        continue;
                    }
                    $result[] = [
                        'product_id'   => $productId,
                        'related_id'   => $relatedId,
                        'product_name' => $productName,
                        'related_name' => $relatedName,
                        'store_id'     => 0,
                        'weight'       => 0,
                        'is_admin'     => 0
                    ];
                }
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

        return true;
    }
}
