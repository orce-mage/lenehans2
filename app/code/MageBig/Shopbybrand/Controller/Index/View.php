<?php
/**
 * Copyright © 2020 MageBig, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\Shopbybrand\Controller\Index;

use MageBig\Shopbybrand\Helper\Data;
use MageBig\Shopbybrand\Model\BrandFactory;
use Magento\Catalog\Api\CategoryRepositoryInterface;
use Magento\Catalog\Model\Design;
use Magento\Catalog\Model\Layer\Resolver;
use Magento\Catalog\Model\Product\ProductList\ToolbarMemorizer;
use Magento\Catalog\Model\Session;
use Magento\CatalogUrlRewrite\Model\CategoryUrlPathGenerator;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\ForwardFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;

class View extends Action
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * Catalog session
     *
     * @var Session
     */
    protected $_catalogSession;

    /**
     * Catalog design
     *
     * @var Design
     */
    protected $_catalogDesign;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var CategoryUrlPathGenerator
     */
    protected $categoryUrlPathGenerator;

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ForwardFactory
     */
    protected $resultForwardFactory;

    /**
     * Catalog Layer Resolver
     *
     * @var Resolver
     */
    private $layerResolver;

    /**
     * @var CategoryRepositoryInterface
     */
    protected $categoryRepository;

    /**
     * @var ToolbarMemorizer
     */
    private $toolbarMemorizer;

    protected $_brandFactory;

    protected $_urlManager;

    protected $_helper;

    /**
     * @var mixed
     */
    private $_attributeCode;

    /**
     * View constructor.
     * @param Context $context
     * @param Design $catalogDesign
     * @param Registry $coreRegistry
     * @param StoreManagerInterface $storeManager
     * @param CategoryUrlPathGenerator $categoryUrlPathGenerator
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param Resolver $layerResolver
     * @param CategoryRepositoryInterface $categoryRepository
     * @param ToolbarMemorizer|null $toolbarMemorizer
     * @param BrandFactory $brandFactory
     * @param Data $helper
     */
    public function __construct(
        Context $context,
        Design $catalogDesign,
        Session $catalogSession,
        Registry $coreRegistry,
        StoreManagerInterface $storeManager,
        CategoryUrlPathGenerator $categoryUrlPathGenerator,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Resolver $layerResolver,
        CategoryRepositoryInterface $categoryRepository,
        ToolbarMemorizer $toolbarMemorizer = null,
        BrandFactory $brandFactory,
        Data $helper
    ) {
        parent::__construct($context);
        $this->_storeManager = $storeManager;
        $this->_catalogDesign = $catalogDesign;
        $this->_catalogSession = $catalogSession;
        $this->_coreRegistry = $coreRegistry;
        $this->categoryUrlPathGenerator = $categoryUrlPathGenerator;
        $this->resultPageFactory = $resultPageFactory;
        $this->resultForwardFactory = $resultForwardFactory;
        $this->layerResolver = $layerResolver;
        $this->categoryRepository = $categoryRepository;
        $this->toolbarMemorizer = $toolbarMemorizer ?: ObjectManager::getInstance()->get(ToolbarMemorizer::class);
        $this->_brandFactory = $brandFactory;
        $this->_attributeCode = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue('magebig_shopbybrand/general/attribute_code');
        $this->_helper = $helper;
        $this->_urlManager = $context->getUrl();
    }

    protected function _initBrand($optionId)
    {
        $brandModel = $this->_brandFactory->create();
        $brandModel->setStoreId($this->_storeManager->getStore()->getId())->setOptionId($optionId)->load(null);
        $brandModel->setUrl($this->_helper->getBrandPageUrl($brandModel));
        $brandModel->setThumbnail($this->_helper->getBrandImage($brandModel, 'mb_brand_thumbnail', ['width' => 400, 'height' => 400]));
        $brandModel->setAttributeCode($this->_attributeCode);
        return $brandModel;
    }

    /**
     * Category view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        if ($this->_request->getParam(\Magento\Framework\App\ActionInterface::PARAM_NAME_URL_ENCODED)) {
            return $this->resultRedirectFactory->create()->setUrl($this->_redirect->getRedirectUrl());
        }

        $optionId = (int)$this->getRequest()->getParam($this->_attributeCode, false);
        $categoryId = $this->_storeManager->getStore()->getRootCategoryId();

        if (!$optionId) {
            return false;
        }

        $brand = $this->_initBrand($optionId);
        if ($brand) {
            $this->_coreRegistry->register('current_brand', $brand);
        }

        try {
            $category = $this->categoryRepository->get($categoryId, $this->_storeManager->getStore()->getId());
        } catch (NoSuchEntityException $e) {
            return false;
        }

        /* get all products of children categories */
        $category->setIsAnchor(true);

        $this->_catalogSession->setLastVisitedCategoryId($category->getId());
        $this->_coreRegistry->register('current_category', $category);
        $this->toolbarMemorizer->memorizeParams();
        try {
            $this->_eventManager->dispatch(
                'catalog_controller_category_init_after',
                ['category' => $category, 'controller_action' => $this]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
            return false;
        }

        if ($category) {
            if (!$this->layerResolver->get(Resolver::CATALOG_LAYER_CATEGORY)) {
                $this->layerResolver->create(Resolver::CATALOG_LAYER_CATEGORY);
            }
            $optionId = (int)$this->getRequest()->getParam($this->_attributeCode, false);

            $this->layerResolver->get(Resolver::CATALOG_LAYER_CATEGORY)->getProductCollection()->addFieldToFilter(
                $this->_attributeCode,
                $optionId
            );
            $this->getRequest()->setParam('brand_page', [$this->_attributeCode, $optionId]);
            $settings = $this->_catalogDesign->getDesignSettings($category);

            $page = $this->resultPageFactory->create();
            $pageConfig = $page->getConfig();
            // apply custom layout (page) template once the blocks are generated
            if ($settings->getPageLayout()) {
                $pageConfig->setPageLayout($settings->getPageLayout());
            }

            $hasChildren = $category->hasChildren();
            $type = $hasChildren ? 'layered' : 'default_without_children';

            if (!$hasChildren) {
                // Two levels removed from parent.  Need to add default page type.
                $parentType = strtok($type, '_');
                $page->addPageLayoutHandles(['type' => $parentType]);
            }

            $page->addPageLayoutHandles(['type' => $type, 'id' => $category->getId()]);
            $pageConfig->addBodyClass('page-products')
                ->addBodyClass('brand-' . $category->getUrlKey())
                ->addBodyClass('catalog-category-view');

            return $page;
        } elseif (!$this->getResponse()->isRedirect()) {
            return $this->resultForwardFactory->create()->forward('noroute');
        }
    }
}
