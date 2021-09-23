<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\Ajaxcompare\Controller\Compare;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use MageBig\Ajaxcompare\Helper\Data as AjaxcompareData;

class Add extends \Magento\Catalog\Controller\Product\Compare\Add
{
    /**
     * @var AjaxcompareData Data
     */
    protected $_ajaxCompareHelper;

    /**
     * @var null
     */
    protected $_coreRegistry = null;

    public function __construct
    (
        \Magento\Framework\App\Action\Context $context,
        \Magento\Catalog\Model\Product\Compare\ItemFactory $compareItemFactory,
        \Magento\Catalog\Model\ResourceModel\Product\Compare\Item\CollectionFactory $itemCollectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Model\Visitor $customerVisitor,
        \Magento\Catalog\Model\Product\Compare\ListCompare $catalogProductCompareList,
        \Magento\Catalog\Model\Session $catalogSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        PageFactory $resultPageFactory,
        ProductRepositoryInterface $productRepository,
        AjaxcompareData $ajaxCompareHelper,
        Registry $registry
    )
    {
        parent::__construct($context, $compareItemFactory, $itemCollectionFactory, $customerSession, $customerVisitor, $catalogProductCompareList, $catalogSession, $storeManager, $formKeyValidator, $resultPageFactory, $productRepository);
        $this->_ajaxCompareHelper = $ajaxCompareHelper;
        $this->_coreRegistry = $registry;
    }

    /**
     * @return \Magento\Framework\Controller\ResultInterface
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $result = [];

        $productId = (int)$this->getRequest()->getParam('product');
        if ($productId && ($this->_customerVisitor->getId() || $this->_customerSession->isLoggedIn())) {
            $storeId = $this->_storeManager->getStore()->getId();
            try {
                $product = $this->productRepository->getById($productId, false, $storeId);
            } catch (NoSuchEntityException $e) {
                $product = null;
            }

            if ($product) {
                $this->_catalogProductCompareList->addProduct($product);
                $this->_eventManager->dispatch('catalog_product_compare_add_product', ['product' => $product]);

                $this->_coreRegistry->register('product', $product);
                $this->_coreRegistry->register('current_product', $product);

                $htmlPopup = $this->_ajaxCompareHelper->getSuccessHtml();
                $result['success'] = true;
                $result['html_popup'] = $htmlPopup;
            }
            $this->_objectManager->get(\Magento\Catalog\Helper\Product\Compare::class)->calculate();

        } else {
            $htmlPopup = $this->_ajaxCompareHelper->getErrorHtml();
            $result['success'] = false;
            $result['html_popup'] = $htmlPopup;
        }

        return $this->getResponse()->representJson(
            $this->_objectManager->get('Magento\Framework\Serialize\Serializer\Json')->serialize($result)
        );
    }
}