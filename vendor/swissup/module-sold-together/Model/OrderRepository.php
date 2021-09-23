<?php
namespace Swissup\SoldTogether\Model;

use Swissup\SoldTogether\Api\Data;
use Swissup\SoldTogether\Api\OrderRepositoryInterface;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Swissup\SoldTogether\Model\ResourceModel\Order as ResourceOrder;
use Swissup\SoldTogether\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use Magento\Store\Model\StoreManagerInterface;

class OrderRepository implements OrderRepositoryInterface
{
    /**
     * @var ResourceOrder
     */
    protected $resource;

    /**
     * @var OrderFactory
     */
    protected $orderFactory;

    /**
     * @var OrderCollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var Data\OrderSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var \Swissup\SoldTogether\Api\Data\OrderInterfaceFactory
     */
    protected $dataOrderFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        ResourceOrder $resource,
        OrderFactory $orderFactory,
        Data\OrderInterfaceFactory $dataOrderFactory,
        OrderCollectionFactory $orderCollectionFactory,
        Data\OrderSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager
    ) {
        $this->resource = $resource;
        $this->orderFactory = $orderFactory;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataOrderFactory = $dataOrderFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
    }

    public function save(\Swissup\SoldTogether\Api\Data\EntityInterface $order)
    {
        try {
            $this->resource->save($order);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__($exception->getMessage()));
        }
        return $order;
    }

    public function getById($orderId)
    {
        $order = $this->orderFactory->create();
        $order->load($orderId);
        if (!$order->getId()) {
            throw new NoSuchEntityException(__('Record with id "%1" does not exist.', $orderId));
        }
        return $order;
    }

    public function delete(\Swissup\SoldTogether\Api\Data\EntityInterface $order)
    {
        try {
            $this->resource->delete($order);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }

        return true;
    }

    public function deleteById($orderId)
    {
        return $this->delete($this->getById($orderId));
    }
}
