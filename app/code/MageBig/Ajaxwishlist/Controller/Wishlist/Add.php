<?php

namespace MageBig\Ajaxwishlist\Controller\Wishlist;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Action;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;

class Add extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Wishlist\Controller\WishlistProviderInterface
     */
    protected $wishlistProvider;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \MageBig\Ajaxwishlist\Helper\Data
     */
    protected $_wishlistHelper;

    /**
     * @var \Magento\Framework\Registry|null
     */
    protected $_coreRegistry = null;

    /**
     * Add constructor.
     *
     * @param Action\Context $context
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Json\Helper\Data $jsonEncode
     * @param \MageBig\Ajaxwishlist\Helper\Data $wishlistHelper
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Wishlist\Controller\WishlistProviderInterface $wishlistProvider,
        ProductRepositoryInterface $productRepository,
        \Magento\Framework\Json\Helper\Data $jsonEncode,
        \MageBig\Ajaxwishlist\Helper\Data $wishlistHelper,
        \Magento\Framework\Registry $registry
    ) {
        $this->_customerSession  = $customerSession;
        $this->wishlistProvider  = $wishlistProvider;
        $this->productRepository = $productRepository;
        $this->_wishlistHelper   = $wishlistHelper;
        $this->_coreRegistry     = $registry;
        parent::__construct($context);
    }

    public function execute()
    {
        $result     = [];
        $isLoggedIn = $this->_customerSession->isLoggedIn();
        //$this->_objectManager->get('Psr\Log\LoggerInterface')->critical($isLoggedIn);

        if ($isLoggedIn == true) {
            try {
                $product = $this->_initProduct();
                $this->_coreRegistry->register('product', $product);
                $this->_coreRegistry->register('current_product', $product);

                $wishlist = $this->wishlistProvider->getWishlist();
                $session = $this->_customerSession;

                $requestParams = $this->getRequest()->getParams();

                if ($session->getBeforeWishlistRequest()) {
                    $requestParams = $session->getBeforeWishlistRequest();
                    $session->unsBeforeWishlistRequest();
                }
                $buyRequest = new \Magento\Framework\DataObject($requestParams);

                $resultItem = $wishlist->addNewItem($product, $buyRequest);
                if (is_string($resultItem)) {
                    throw new \Magento\Framework\Exception\LocalizedException(__($resultItem));
                }

                if ($wishlist->isObjectNew()) {
                    $wishlist->save();
                }

                $this->_eventManager->dispatch(
                    'wishlist_add_product',
                    ['wishlist' => $wishlist, 'product' => $product, 'item' => $resultItem]
                );

                $wishlistHelper = $this->_objectManager->get(\Magento\Wishlist\Helper\Data::class);
                $wishlistHelper->calculate();

                $htmlPopup            = $this->_wishlistHelper->getSuccessHtml();
                $result['success']    = true;
                $result['html_popup'] = $htmlPopup;
            } catch (\Exception $e) {
                //$this->messageManager->addExceptionMessage($e, __('You can\'t login right now.'));
                //$this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $htmlPopup = __('You can\'t login right now.');
                $result['success'] = false;
                $result['html_popup'] = $htmlPopup;
            }
        } else {
            $product = $this->_initProduct();
            $this->_coreRegistry->register('product', $product);
            $this->_coreRegistry->register('current_product', $product);

            $htmlPopup            = $this->_wishlistHelper->getErrorHtml();
            $result['success']    = false;
            $result['html_popup'] = $htmlPopup;

        }
        $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Serialize\Serializer\Json')->serialize($result)
        );
    }

    protected function _initProduct()
    {
        $productId = (int) $this->getRequest()->getParam('product');
        if ($productId) {
            $storeId = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface')->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productId, false, $storeId);
                return $product;
            } catch (NoSuchEntityException $e) {
                return false;
            }
        }
        return false;
    }
}
