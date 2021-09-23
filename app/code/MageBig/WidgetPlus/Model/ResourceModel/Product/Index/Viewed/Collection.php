<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * Reports Viewed Product Index Resource Collection
 *
 * @author      Magento Core Team <core@magentocommerce.com>
 */

namespace MageBig\WidgetPlus\Model\ResourceModel\Product\Index\Viewed;

/**
 * @api
 * @since 100.0.2
 */
class Collection extends \Magento\Reports\Model\ResourceModel\Product\Index\Viewed\Collection
{
    protected $storage;

    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactory $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Eav\Model\EntityFactory $eavEntityFactory,
        \Magento\Catalog\Model\ResourceModel\Helper $resourceHelper,
        \Magento\Framework\Validator\UniversalFactory $universalFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Catalog\Model\Indexer\Product\Flat\State $catalogProductFlatState,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Product\OptionFactory $productOptionFactory,
        \Magento\Catalog\Model\ResourceModel\Url $catalogUrl,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Customer\Api\GroupManagementInterface $groupManagement,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Framework\Stdlib\CookieManagerInterface $storage,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\App\Http\Context $context,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null
    ) {

        parent::__construct(
            $entityFactory,
            $logger,
            $fetchStrategy,
            $eventManager,
            $eavConfig,
            $resource,
            $eavEntityFactory,
            $resourceHelper,
            $universalFactory,
            $storeManager,
            $moduleManager,
            $catalogProductFlatState,
            $scopeConfig,
            $productOptionFactory,
            $catalogUrl,
            $localeDate,
            $customerSession,
            $dateTime,
            $groupManagement,
            $customerVisitor,
            $connection
        );

        $this->_customerVisitor = $customerVisitor;
        $this->storage = $storage;
        $this->objectManager = $objectManager;
        $this->context = $context;
    }

    protected function _getWhereCondition()
    {
        // var_dump($this->context->getValue('customer_logged_id'));
        // var_dump($this->context->getValue('customer_visitor_id'));
        // exit();
        $condition    = [];

        $myCustomerId = $this->storage->getCookie('customer_id');
        $myVisitorId  = $this->storage->getCookie('visitor_id');

        // if ($this->_customerVisitor->getId() && $myVisitorId != $this->_customerVisitor->getId()) {
        //     $this->storage->setPublicCookie('visitor_id', $this->_customerVisitor->getId());
        //     $myVisitorId  = $this->_customerVisitor->getId();
        // }
        // if ($this->_customerSession->getCustomerId() && $myCustomerId != $this->_customerSession->getCustomerId()) {
        //     $this->storage->setPublicCookie('customer_id', $this->_customerSession->getCustomerId());
        //     $myCustomerId = $this->_customerSession->getCustomerId();
        // }

        var_dump($myVisitorId);

        if ($myCustomerId) {
            $condition['customer_id'] = $myCustomerId;
        } elseif ($this->_customerSession->isLoggedIn()) {
            $condition['customer_id'] = $this->_customerSession->getCustomerId();
            //$this->storage->setPublicCookie('customer_id', $condition['customer_id']);
        } elseif ($this->_customerId) {
            $condition['customer_id'] = $this->_customerId;
            //$this->storage->setPublicCookie('customer_id', $condition['customer_id']);
        } elseif ($myVisitorId) {
            $condition['visitor_id'] = $myVisitorId;
        } else {
            $condition['visitor_id'] = $this->_customerVisitor->getId();
            //if ($condition['visitor_id'])
                //$this->storage->setPublicCookie('visitor_id', $condition['visitor_id']);
        }

        return $condition;
    }

    public function getCustomerSession(){
        $customerSession = $this->objectManager->create('Magento\Customer\Model\SessionFactory')->create();
        return $customerSession->getCustomerId();
    }
    public function getVisitorSession(){
        $visitor = $this->objectManager->create('Magento\Customer\Model\VisitorFactory')->create();
        return $visitor;
    }
}
