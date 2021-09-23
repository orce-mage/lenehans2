<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Helper;

use Magento\Framework\Error\Processor;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
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
     * Order Invoice Model
     *
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_modelOrderConfig;

    /**
     * @var \Magento\Framework\App\ProductMetadata
     */
    protected $_metadata;


    /**
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\View\Result\PageFactory                   $resultPageFactory
     * @param \Magento\Framework\ObjectManagerInterface                    $objectManager
     * @param \Magento\Framework\Message\ManagerInterface                  $messageManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface         $localeDate
     * @param \Magento\Framework\Filesystem                                $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager
     * @param \Magento\Framework\View\LayoutFactory                        $layoutFactory
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $catalogProductTypeConfigurable
     * @param \Magento\Framework\App\ActionFlag                            $actionFlag
     * @param \Magento\Catalog\Model\Product                               $product
     * @param \Magento\Framework\App\Helper\Context                        $context
     * @param \Magento\Cms\Model\Page                                      $cmsPage
     * @param \Magento\Sales\Model\Order                                   $modelOrder
     * @param \Magento\Sales\Model\Order\Invoice                           $_modelOrderInvoice
     * @param \Magento\Sales\Model\Order\Config                            $_modelOrderConfig
     * @param \Magento\Cms\Model\Block                                     $cmsBlock
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
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\ProductMetadata $metadata,
        \Magento\Cms\Model\Page $cmsPage,
        \Magento\Sales\Model\Order $modelOrder,
        \Magento\Sales\Model\Order\Invoice $_modelOrderInvoice,
        \Magento\Sales\Model\Order\Config $_modelOrderConfig,
        \Magento\Cms\Model\Block $cmsBlock
    ) {
        parent::__construct($context);
        $this->_registry                = $registry;
        $this->_resultPageFactory       = $resultPageFactory;
        $this->_objectManager           = $objectManager;
        $this->_messageManager          = $messageManager;
        $this->_localeDate              = $localeDate;
        $this->_filesystem              = $filesystem;
        $this->_storeManager            = $storeManager;
        $this->layoutFactory            = $layoutFactory;
        $this->_productTypeConfigurable = $catalogProductTypeConfigurable;
        $this->_product                 = $product;
        $this->_actionFlag              = $actionFlag;
        $this->_cmsPage                 = $cmsPage;
        $this->_cmsBlock                = $cmsBlock;
        $this->_modelOrder              = $modelOrder;
        $this->_modelOrderInvoice       = $_modelOrderInvoice;
        $this->_modelOrderConfig        = $_modelOrderConfig;
        $this->_metadata                = $metadata;
        if (version_compare($this->_metadata->getVersion(), "2.3.0", "<")) {
            $this->_variables = $this->_objectManager->get('\Magento\Email\Model\Source\Variables');
        } else {
            $this->_variables = $this->_objectManager->get('\Magento\Variable\Model\Source\Variables');
        }
    }

    public function getModuleConfig($path)
    {
        return $this->scopeConfig->getValue('amasty_orderexport/' . $path);
    }

    public function getNextIncrementId($lastIncrementId)
    {
        $lastId = $this->_modelOrder->load($lastIncrementId, 'increment_id')->getId();
        if (!$lastId) {
            return '';
        }
        $collection = $this->_modelOrder->getCollection();
        $collection->getSelect()->where('entity_id > "' . $lastId . '"');
        $collection->getSelect()->order('entity_id ASC');
        $collection->getSelect()->limit(1);
        $collection->load();
        if ($collection->getSize() > 0) {
            foreach ($collection as $order) {
                return $order->getIncrementId();
            }
        }

        return '';
    }

    public function getNextInvoiceIncrementId($lastIncrementId)
    {
        $lastId     = $this->_modelOrderInvoice->load($lastIncrementId, 'increment_id')->getId();
        $collection = $this->_modelOrderInvoice->getCollection();
        $collection->getSelect()->where('entity_id > "' . $lastId . '"');
        $collection->getSelect()->order('entity_id ASC');
        $collection->getSelect()->limit(1);
        $collection->load();
        if ($collection->getSize() > 0) {
            foreach ($collection as $order) {
                return $order->getIncrementId();
            }
        }

        return '';
    }

    public function getOrderStatuses()
    {
        $statuses = $this->_modelOrderConfig->getStatuses();
        $values   = [];

        foreach ($statuses as $key => $status) {
            $values[] = [
                'value' => $key,
                'label' => $status
            ];
        }

        return $values;
    }

    public function getEmails()
    {
        $configVariables = $this->_variables->getData();
        foreach ($configVariables as $key => $value){
            if (false === strpos($value['value'],'/email'))
                unset($configVariables[$key]);
        }
        $configVariables[] = ['value' => 'trans_email/ident_support/email', 'label' => __('Customer Support Email')];

        foreach ($configVariables as $key => $value){
            $namePath = str_replace('/email','/name',$value['value']);
            $name =' - '.$this->scopeConfig->getValue($namePath)." (".$this->scopeConfig->getValue($value['value']).")";
            $configVariables[$key]['label'] .= $name;

        }

        return $configVariables;
    }

}
