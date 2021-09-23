<?php

namespace MageBig\Ajaxwishlist\Helper;

use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Registry;
use Magento\Framework\View\LayoutFactory;
use Magento\Store\Model\StoreManagerInterface;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_storeId;

    protected $_coreRegistry;

    protected $_storeManager;

    protected $_customerSession;

    protected $_layoutFactory;

    protected $_urlBuilder;

    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var Json
     */
    private $serializer;

    /**
     * Data constructor.
     * @param Context               $context
     * @param StoreManagerInterface $storeManager
     * @param Registry              $coreRegistry
     * @param CustomerSession       $customerSession
     * @param LayoutFactory         $layoutFactory
     * @param Json|null             $serializer
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        Registry $coreRegistry,
        CustomerSession $customerSession,
        LayoutFactory $layoutFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        Json $serializer = null
    ) {
        parent::__construct($context);
        $this->_storeManager  = $storeManager;
        $this->_coreRegistry  = $coreRegistry;
        $this->httpContext    = $httpContext;
        $this->_layoutFactory = $layoutFactory;
        $this->_urlBuilder    = $context->getUrlBuilder();
        $this->serializer     = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()->get(Json::class);

    }

    public function getAjaxWishlistInitOptions()
    {
        $options = [
            'ajaxWishlistUrl' => $this->_urlBuilder->getUrl('ajaxwishlist/wishlist/add'),
            'isLogedIn'       => $this->isLoggedIn()
        ];

        return $this->serializer->serialize($options);
    }

    /**
     * Is logged in
     *
     * @return bool
     */
    public function isLoggedIn()
    {
        return $this->httpContext->getValue('customer_logged_in');
    }

    public function getOptionsPopupHtml()
    {
        $layout = $this->_layoutFactory->create();
        $update = $layout->getUpdate();
        $update->load('ajaxwishlist_options_popup');
        $layout->generateXml();
        $layout->generateElements();

        return $layout->getOutput();
    }

    public function getSuccessHtml()
    {
        $layout = $this->_layoutFactory->create();
        $layout->getUpdate()->load('ajaxwishlist_success_message');
        $layout->generateXml();
        $layout->generateElements();

        return $layout->getOutput();
    }

    public function getErrorHtml()
    {
        $layout = $this->_layoutFactory->create();
        $layout->getUpdate()->load('ajaxwishlist_error_message');
        $layout->generateXml();
        $layout->generateElements();

        return $layout->getOutput();
    }

    public function isEnabledAjaxWishlist()
    {
        return (bool)$this->scopeConfig->getValue(
            'ajaxwishlist/general/enabled',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeId
        );
    }

}
