<?php

namespace Swissup\SoldTogether\Model;

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Swissup\SoldTogether\Model\OrderFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;

class OrderIndexer extends AbstractIndexer
{
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    protected $searchCriteriaBuilder;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var OrderFactory
     */
    protected $modelFactory;

    /**
     * @var string
     */
    protected $orderItemField;

    /**
     * @var string
     */
    protected $productField;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param SearchCriteriaBuilder      $searchCriteriaBuilder
     * @param CollectionFactory          $collectionFactory
     * @param OrderFactory               $modelFactory
     * @param string                     $orderItemField
     * @param string                     $productField
     */
    public function __construct(
        ProductRepositoryInterface $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $collectionFactory,
        OrderFactory $modelFactory,
        $orderItemField = 'sku',
        $productField = 'sku'
    ) {
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionFactory = $collectionFactory;
        $this->modelFactory = $modelFactory;
        $this->orderItemField = $orderItemField;
        $this->productField = $productField;
    }

    /**
     * {@inheritdoc}
     */
    protected function collectData($pageNumber, $pageSize)
    {
        $result = [];
        $collection = $this->collectionFactory->create();
        $collection->setPageSize($pageSize);
        if ($pageNumber > $collection->getLastPageNumber()) {
            return $result;
        }

        $collection->setCurPage($pageNumber);
        foreach ($collection as $order) {
            $storeId = $order->getStoreId();
            $visibleItems = $order->getAllVisibleItems();
            $orderProducts = [];
            if (count($visibleItems) > 1) {
                $filterValues = [];
                foreach ($visibleItems as $item) {
                    $filterValues[] = $item->getData($this->orderItemField);
                }

                $criteria = $this->searchCriteriaBuilder
                    ->addFilter($this->productField, implode(',', $filterValues), 'in')
                    ->create();
                $orderProducts = $this->productRepository->getList($criteria)->getItems();

                foreach ($orderProducts as $product) {
                    foreach ($orderProducts as $related) {
                        if ($product->getEntityId() == $related->getEntityId()) {
                            continue;
                        }

                        $result[] = [
                            'product_id'   => $product->getEntityId(),
                            'related_id'   => $related->getEntityId(),
                            'product_name' => $product->getName(),
                            'related_name' => $related->getName(),
                            'store_id'     => 0,
                            'weight'       => 0,
                            'is_admin'     => 0
                        ];
                    }
                }
            }
        }

        return $result;
    }

    /**
     * {@inheritdoc}
     */
    public function getItemsToProcessCount()
    {
        return $this->collectionFactory->create()->getTotalCount();
    }
}
