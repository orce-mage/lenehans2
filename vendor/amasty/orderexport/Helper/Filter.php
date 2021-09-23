<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Helper;

use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\Order\InvoiceRepository;
use Magento\Sales\Model\Order\ShipmentRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;

/**
 * Class Filter
 *
 * @package Amasty\Orderexport\Helper
 */
class Filter extends \Magento\Framework\App\Helper\AbstractHelper
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var  \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var  \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var  \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_cmsPage;

    /**
     * @var \Magento\Cms\Model\Block
     */
    protected $_cmsBlock;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_category;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Action flag
     *
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * Order Model
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_modelOrder;

    /**
     * Order Invoice Model
     *
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $_modelOrderInvoice;

    /**
     * Order Config Model
     *
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_modelOrderConfig;

    /**
     * @var \Amasty\Orderexport\Model\History
     */
    protected $_modelHistory;

    /**
     * @var \Amasty\Orderexport\Helper\Fields $helperFields
     */
    protected $_helperFields;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /** @var \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory */
    protected $_orderItemCollectionFactory;

    /** @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory */
    protected $_orderCollectionFactory;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var InvoiceRepository
     */
    private $invoiceRepository;

    /**
     * @var ShipmentRepository
     */
    private $shipmentRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @param \Magento\Framework\Registry                                     $registry
     * @param \Magento\Framework\View\Result\PageFactory                      $resultPageFactory
     * @param \Magento\Framework\ObjectManagerInterface                       $objectManager
     * @param \Magento\Framework\Message\ManagerInterface                     $messageManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface            $localeDate
     * @param \Magento\Framework\Filesystem                                   $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface                      $storeManager
     * @param \Magento\Framework\View\LayoutFactory                           $layoutFactory
     * @param \Magento\Framework\App\ResourceConnection                       $resource
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable    $catalogProductTypeConfigurable
     * @param \Magento\Framework\App\ActionFlag                               $actionFlag
     * @param \Magento\Catalog\Model\Product                                  $product
     * @param \Magento\Framework\App\Helper\Context                           $context
     * @param \Magento\Cms\Model\Page                                         $cmsPage
     * @param \Magento\Sales\Model\Order                                      $modelOrder
     * @param \Magento\Sales\Model\Order\Invoice                              $_modelOrderInvoice
     * @param \Magento\Sales\Model\Order\Config                               $_modelOrderConfig
     * @param \Magento\Cms\Model\Block                                        $cmsBlock
     * @param Fields                                                          $helperFields
     * @param \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory      $orderCollectionFactory
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Cms\Model\Page $cmsPage,
        \Magento\Sales\Model\Order $modelOrder,
        \Magento\Sales\Model\Order\Invoice $_modelOrderInvoice,
        \Magento\Sales\Model\Order\Config $_modelOrderConfig,
        \Magento\Cms\Model\Block $cmsBlock,
        \Amasty\Orderexport\Helper\Fields $helperFields,
        \Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory $orderItemCollectionFactory,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        OrderRepository $orderRepository,
        InvoiceRepository $invoiceRepository,
        ShipmentRepository $shipmentRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        parent::__construct($context);
        $this->_registry = $registry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_objectManager = $objectManager;
        $this->_messageManager = $messageManager;
        $this->_resource = $resource;
        $this->_localeDate = $localeDate;
        $this->_filesystem = $filesystem;
        $this->_storeManager = $storeManager;
        $this->layoutFactory = $layoutFactory;
        $this->_productTypeConfigurable = $catalogProductTypeConfigurable;
        $this->_product = $product;
        $this->_actionFlag = $actionFlag;
        $this->_cmsPage = $cmsPage;
        $this->_cmsBlock = $cmsBlock;
        $this->_helperFields = $helperFields;
        $this->_modelOrder = $modelOrder;
        $this->_modelOrderInvoice = $_modelOrderInvoice;
        $this->_modelOrderConfig = $_modelOrderConfig;
        $this->_orderItemCollectionFactory = $orderItemCollectionFactory;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->invoiceRepository = $invoiceRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
    }

    /**
     * @param $profile \Amasty\Orderexport\Model\Profiles
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrdersForProfile($profile, $ids)
    {
        $orders = $this->_modelOrder->getCollection();

        if (!empty($profile->getData('store_ids'))) {
            $storeIds = explode(',', $profile->getData('store_ids'));
            $orders->addFieldToFilter('main_table.store_id', ['in' => $storeIds]);
        }

        if ($profile->getData('filter_customergroup')) {
            $customerGroupIds = @unserialize($profile->getData('filter_customergroup_ids'));
            $orders->addFieldToFilter('main_table.customer_group_id', $customerGroupIds);
        }

        if (!is_null($ids) && ($ids != 'false')) {
            $orders->addFieldToFilter('entity_id', ['in' => $ids]);
        }

        if ($profile->getData('filter_number_enabled')
            && ($profile->getData('filter_number_from') || $profile->getData('filter_number_to'))
        ) {
            $filterConditions = [];

            $ordNumFr = trim($profile->getData('filter_number_from'));
            if ($profile->getData('increment_auto')) {
                $ordNumFr = trim($profile->getData('last_increment_id')) ? : $ordNumFr;
            }

            if ($ordNumFr) {
                $orderFrom = $this->getItemByIncrementId('order', $ordNumFr);

                if ($orderFrom->getId()) {
                    $ordNumFr = $orderFrom->getId();
                    if ($profile->getData('filter_number_from_skip')) {
                        $ordNumFr++;
                    }

                    $filterConditions['from'] = $ordNumFr;
                }
            }

            $ordNumTo = trim($profile->getData('filter_number_to'));
            if ($ordNumTo) {
                $orderTo = $this->getItemByIncrementId('order', $ordNumTo);

                if ($orderTo->getId()) {
                    $filterConditions['to'] = $orderTo->getId();
                }
            }

            if (!empty($filterConditions)) {
                $orders->addFieldToFilter('main_table.entity_id', $filterConditions);
            }
        }

        if (($profile->getData('filter_date_enabled'))
            && ($profile->getData('filter_date_from') || $profile->getData('filter_date_to'))
        ) {
            $dateFr = $profile->getData('filter_date_from');
            $dateTo = $profile->getData('filter_date_to');

            // add filter conditions
            $filterConditions = [];
            if ($dateFr) {
                $filterConditions['from'] = $dateFr;
            }
            if ($dateTo) {
                $filterConditions['to'] = $dateTo;
            }
            $orders->addFieldToFilter('main_table.created_at', $filterConditions);
        }

        if ($profile->getData('filter_status')) {
            $statuses = @unserialize($profile->getData('filter_status'));
            if ($statuses) {
                $orders->addFieldToFilter('main_table.status', $statuses);
            }
        }

        if ($profile->getData('filter_invoice_enabled')
            && ($profile->getData('filter_invoice_from') || $profile->getData('filter_invoice_to'))
        ) {
            $filterConditions = [];

            $invNumFr = trim($profile->getData('filter_invoice_from'));
            if ($profile->getData('invoice_increment_auto')) {
                $invNumFr = trim($profile->getData('last_invoice_increment_id')) ? : $invNumFr;
            }

            if ($invNumFr) {
                $invoiceFrom = $this->getItemByIncrementId('invoice', $invNumFr);

                if ($invoiceFrom->getId()) {
                    $invNumFr = $invoiceFrom->getId();
                    if ($profile->getData('filter_invoice_from_skip')) {
                        $invNumFr++;
                    }

                    $filterConditions['from'] = $invNumFr;
                }
            }

            $invNumTo = trim($profile->getData('filter_invoice_to'));
            if ($invNumTo) {
                $invoiceTo = $this->getItemByIncrementId('invoice', $invNumTo);

                if ($invoiceTo->getId()) {
                    $filterConditions['to'] = $invoiceTo->getId();
                }
            }

            if (!empty($filterConditions)) {
                $orders->join(
                    ['invoice' => $this->_resource->getTableName('sales_invoice')],
                    'main_table.entity_id = invoice.order_id',
                    []
                );

                $orders->addFieldToFilter('invoice.entity_id', $filterConditions);
            }
        }

        if ($profile->getData('filter_shipment_enabled')
            && ($profile->getData('filter_shipment_from') || $profile->getData('filter_shipment_to'))
        ) {
            $filterConditions = [];

            $shpNumFr = trim($profile->getData('filter_shipment_from'));
            if ($shpNumFr) {
                $shipmentFrom = $this->getItemByIncrementId('shipment', $shpNumFr);

                if ($shipmentFrom->getId()) {
                    $filterConditions['to'] = $shipmentFrom->getId();
                }
            }

            $shpNumTo = trim($profile->getData('filter_shipment_to'));
            if ($shpNumTo) {
                $shipmentTo = $this->getItemByIncrementId('shipment', $shpNumTo);

                if ($shipmentTo->getId()) {
                    $filterConditions['to'] = $shipmentTo->getId();
                }
            }

            if (!empty($filterConditions)) {
                $orders->join(
                    ['shipment' => $this->_resource->getTableName('sales_shipment')],
                    'main_table.entity_id = shipment.order_id',
                    []
                );

                $orders->addFieldToFilter('shipment.entity_id', $filterConditions);
            }
        }

        /*
         * get our select total rows count before joins & group
         */
        $orders->setPageSize(50);
        $orders->getLastPageNumber();

        /*
         * filter fields
         */
        $orders = $this->_helperFields->filterFields($orders);

        /*
         * add mapping
         */
        $orders = $this->_helperFields->addFieldMapping(
            $orders,
            $profile,
            $this->_helperFields->getOrderTables()
        );

        /*
         * add 3rdParty Connections
         */
        $orders = $this->_helperFields->addThirdpartyConnect($orders);

        /*
         * avoid double-loading of each order in same request
         */
        $orders->getSelect()->group('main_table.entity_id');

        return $orders;
    }

    /**
     * @param \Amasty\Orderexport\Model\Profiles                  $profile
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $ordersDataSelection
     *
     * @return array
     * @throws \Zend_Db_Select_Exception
     */
    public function getItemsForOrders(
        \Amasty\Orderexport\Model\Profiles $profile,
        \Magento\Sales\Model\ResourceModel\Order\Collection $ordersDataSelection
    ) {
        $ordersIds = [];
        $items = [];
        foreach ($ordersDataSelection->getData() as $item) {
            $ordersIds[] = $item['entity_id_track'];
        }

        /** @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection $orderItemCollection */
        $orderItemCollection = $this->_orderItemCollectionFactory->create();

        $orderItemCollection->addFieldToFilter('sales_order_item.order_id', ['in' => $ordersIds]);

        $from = $orderItemCollection->getSelect()->getPart(\Zend_Db_Select::FROM);
        $from['sales_order_item'] = $from['main_table'];
        unset($from['main_table']);
        $orderItemCollection->getSelect()->setPart(\Zend_Db_Select::FROM, $from);

        $this->_helperFields->addFieldMapping(
            $orderItemCollection,
            $profile,
            $this->_helperFields->getOrderItemsTables(),
            'sales_order_item'
        );

        if ($profile->getSkipChildProducts()) {
            $orderItemCollection->addFieldToFilter('parent_item_id', ['null' => null]);
        }

        if ($profile->getSkipParentProducts()) {
            $orderItemCollection->addFieldToFilter('product_type', ['neq' => 'configurable']);
            $orderItemCollection->addFieldToFilter('product_type', ['neq' => 'bundle']);
        }

        $orderItemCollection->getSelect()->columns('sales_order_item.order_id as order_id_track');

        foreach ($orderItemCollection->getData() as $item) {
            if (!array_key_exists($item['order_id_track'], $items)) {
                $items[$item['order_id_track']] = [];
            }
            $items[$item['order_id_track']][$item['entity_id_track']] = $item;
            unset($items[$item['order_id_track']][$item['entity_id_track']]['entity_id_track']);
            unset($items[$item['order_id_track']][$item['entity_id_track']]['order_id_track']);

        }

        return $items;
    }

    /**
     * @param \Magento\Sales\Model\ResourceModel\Order\Collection $ordersDataSelection
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrdersCollection(
        \Magento\Sales\Model\ResourceModel\Order\Collection $ordersDataSelection
    ) {
        $collection = $this->_orderCollectionFactory->create();

        $ordersIds = [];

        foreach ($ordersDataSelection->getData() as $item) {
            $ordersIds[] = $item['entity_id_track'];
        }

        $collection->addFieldToFilter('entity_id', ['in' => $ordersIds]);

        return $collection;
    }

    private function getItemByIncrementId($entityType, $incrementId)
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('increment_id', $incrementId, 'eq')
            ->create();

        switch ($entityType) {
            case 'order':
                $item = $this->orderRepository->getList($searchCriteria)->getFirstItem();
                break;
            case 'invoice':
                $item = $this->invoiceRepository->getList($searchCriteria)->getFirstItem();
                break;
            case 'shipment':
                $item = $this->shipmentRepository->getList($searchCriteria)->getFirstItem();
                break;
        }

        return $item;
    }
}
