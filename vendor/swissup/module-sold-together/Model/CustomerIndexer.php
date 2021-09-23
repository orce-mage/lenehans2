<?php

namespace Swissup\SoldTogether\Model;

use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Swissup\SoldTogether\Model\CustomerFactory;

class CustomerIndexer extends AbstractIndexer
{
    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CustomerFactory
     */
    protected $modelFactory;

    /**
     * @param CollectionFactory $collectionFactory
     * @param CustomerFactory   $modelFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        CustomerFactory $modelFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->modelFactory = $modelFactory;
    }

    /**
     * {@inheritdoc}
     */
    protected function collectData($pageNumber, $pageSize)
    {
        $result = [];
        $productIds = $this->modelFactory->create()
            ->getCustomerOrderIds($pageSize, $pageNumber);
        foreach ($productIds as $productId => $orderData) {
            foreach ($productIds as $relatedId => $relatedData) {
                if ($productId == $relatedId) {
                    continue;
                }
                if ($orderData['store'] != $relatedData['store']) {
                    continue;
                }
                $result[] = [
                    'product_id'   => $productId,
                    'related_id'   => $relatedId,
                    'product_name' => $orderData['name'],
                    'related_name' => $relatedData['name'],
                    'store_id'     => 0,
                    'weight'       => 0,
                    'is_admin'     => 0
                ];
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsToProcessCount()
    {
        return $this->collectionFactory->create()->getSize();
    }
}
