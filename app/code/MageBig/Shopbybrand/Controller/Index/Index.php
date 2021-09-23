<?php
/**
 * Copyright © 2020 MageBig, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\Shopbybrand\Controller\Index;

use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_scopeConfig;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PageFactory $resultPageFactory,
        \MageBig\Shopbybrand\Model\BrandFactory $brandFactory
    ) {
        parent::__construct($context);
        $this->_scopeConfig = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        $this->_brandFactory = $brandFactory;
        $this->_attributeCode = $this->_scopeConfig->getValue('magebig_shopbybrand/general/attribute_code');
    }

    protected function _initBrandPage()
    {
        if (!$this->_coreRegistry->registry('all_brands_info')) {
            $brands = new \Magento\Framework\DataObject([
                'title'                     => $this->_scopeConfig->getValue('magebig_shopbybrand/our_brands_page/title')?:__('Our Brands'),
                'description'               => $this->_scopeConfig->getValue('magebig_shopbybrand/our_brands_page/description')?:'',
                'display_featured_brands'   => $this->_scopeConfig->getValue('magebig_shopbybrand/our_brands_page/display_featured_brands'),
                'display_brand_search'      => $this->_scopeConfig->getValue('magebig_shopbybrand/our_brands_page/display_brand_search'),
                'meta_title'                => $this->_scopeConfig->getValue('magebig_shopbybrand/our_brands_page/meta_title'),
                'meta_keywords'             => $this->_scopeConfig->getValue('magebig_shopbybrand/our_brands_page/meta_keywords'),
                'meta_description'          => $this->_scopeConfig->getValue('magebig_shopbybrand/our_brands_page/meta_description'),
                'featured_brand_title'      => $this->_scopeConfig->getValue('magebig_shopbybrand/featured_brands/title')
            ]);
            $this->_coreRegistry->register('all_brands_info', $brands);
        }
        return $this->_coreRegistry->registry('all_brands_info');
    }

    public function execute()
    {
        $page = $this->resultPageFactory->create();
        $brand = $this->_initBrandPage();
        $pageConfig = $page->getConfig();

        $pageConfig->addBodyClass('our-brands');

        $title = $brand->getData('title');

        $pageConfig->getTitle()->set($brand->getData('meta_title') ? : $title);
        $pageConfig->setKeywords($brand->getData('meta_keywords'));
        $pageConfig->setDescription($brand->getData('meta_description'));

        $pageMainTitle = $page->getLayout()->getBlock('page.main.title');
        if ($pageMainTitle) {
            $pageMainTitle->setPageTitle($title);
        }
        return $page;
    }

}
