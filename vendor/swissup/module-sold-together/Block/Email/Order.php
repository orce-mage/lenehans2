<?php

namespace Swissup\SoldTogether\Block\Email;

use Magento\Catalog\Block\Product\Context;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Checkout\Model\ResourceModel\Cart as CartResourceModel;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Module\Manager;

class Order extends \Magento\Catalog\Block\Product\ProductList\Related
{
    /**
     * Name of table in DB
     *
     * @var string
     */
    protected $_tableName = 'swissup_soldtogether_order';

    /**
     * @var \Magento\Sales\Model\OrderRepository
     */
    protected $orderRepository;

    /**
     * @param Context $context
     * @param CartResourceModel $checkoutCart
     * @param ProductVisibility $catalogProductVisibility
     * @param CheckoutSession $checkoutSession
     * @param Manager $moduleManager
     * @param array $data
     */
    public function __construct(
        Context $context,
        CartResourceModel $checkoutCart,
        ProductVisibility $catalogProductVisibility,
        CheckoutSession $checkoutSession,
        Manager $moduleManager,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        array $data = []
    ) {
        $this->orderRepository = $orderRepository;

        parent::__construct(
            $context,
            $checkoutCart,
            $catalogProductVisibility,
            $checkoutSession,
            $moduleManager,
            $data
        );
    }

    /**
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        if ($this->hasOrder()) {
            return $this->getData('order');
        }

        if ($this->hasOrderId()) {
            try {
                $order = $this->orderRepository->get($this->getOrderId());
                $this->setData('order', $order);
                return $order;
            } catch (\Exception $e) {
                //
            }
        }
    }

    /**
     * @return $this
     */
    protected function _prepareData()
    {
        if (!$order = $this->getOrder()) {
            return false;
        }
        $items = $order->getAllVisibleItems();
        $ids = [];
        foreach ($items as $item) {
            $ids[] = $item->getProductId();
        }
        /* @var $product \Magento\Catalog\Model\Product */
        $_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productCollection = $_objectManager->create('Magento\Catalog\Model\ResourceModel\Product\Collection');
        $resource = $_objectManager->get('Magento\Framework\App\ResourceConnection');
        $this->_itemCollection = $productCollection->addAttributeToSelect(
            'required_options'
        )->addStoreFilter();

        if ($this->moduleManager->isEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($this->_itemCollection);
        }
        $this->_itemCollection->setVisibility($this->_catalogProductVisibility->getVisibleInCatalogIds());
        $this->_itemCollection->getSelect()
            ->joinInner(
                ['so' => $resource->getTableName($this->_tableName)],
                'so.related_id=e.entity_id',
                ['soldtogether_weight' => 'so.weight']
            );

        $this->_itemCollection->getSelect()
            ->where('so.product_id in (?)', $ids)
            ->order('soldtogether_weight ' . \Magento\Framework\DB\Select::SQL_DESC);
        $this->_itemCollection->getSelect()->limit($this->getEmailLimit());

        $this->_itemCollection->load();

        foreach ($this->_itemCollection as $product) {
            $product->setDoNotUseCategoryId(true);
        }

        return $this;
    }

    public function getProductCollection()
    {
        return $this->_itemCollection;
    }

    /**
     * Get product limit in email
     *
     * @param  string $key
     * @return string
     */
    public function getEmailLimit()
    {
        return $this->_scopeConfig->getValue(
            "soldtogether/email/order_count",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }
}
