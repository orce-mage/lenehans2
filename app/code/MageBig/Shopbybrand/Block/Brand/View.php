<?php
/**
 * Copyright © 2020 MageBig, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\Shopbybrand\Block\Brand;

class View extends \Magento\Framework\View\Element\Template implements \Magento\Framework\DataObject\IdentityInterface
{
    protected $_coreRegistry = null;
    protected $brandHelper;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \MageBig\Shopbybrand\Helper\Data $brandHelper,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->brandHelper = $brandHelper;
        parent::__construct($context, $data);
    }
    protected function _prepareLayout()
    {
        parent::_prepareLayout();

        //$this->getLayout()->createBlock('Magento\Catalog\Block\Breadcrumbs');
        $brand = $this->getBrand();
        if ($brand) {
            $pageConfig = $this->pageConfig;
            $title = $brand->getData('brand_label');
            $metaTitle = $brand->getData('mb_brand_meta_title') ?: $title;
            $description = $brand->getData('mb_brand_meta_description') ?: $title;

            $pageConfig->getTitle()->set($metaTitle);
            $pageConfig->setKeywords($brand->getData('mb_brand_meta_keyword') ?: $title);
            $pageConfig->setDescription($description);
            $pageConfig->setMetadata('og:url', $brand->getUrl());
            $pageConfig->setMetadata('og:type', 'article');
            $pageConfig->setMetadata('og:title', $metaTitle);
            $pageConfig->setMetadata('og:description', $description);
            $pageConfig->setMetadata('og:image', $brand->getThumbnail());

            $pageMainTitle = $this->getLayout()->getBlock('page.main.title');
            if ($pageMainTitle) {
                $pageMainTitle->setPageTitle($title);
            }

            $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');
            if ($breadcrumbsBlock) {
                $breadcrumbsBlock->addCrumb(
                    'home',
                    [
                        'label' => __('Home'),
                        'title' => __('Go to Home Page'),
                        'link' => $this->_storeManager->getStore()->getBaseUrl()
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'brands',
                    [
                        'label' => __('Our Brands'),
                        'title' => __('Our Brands'),
                        'link'  => $this->getUrl($this->brandHelper->getBrandRoute())
                    ]
                );
                $breadcrumbsBlock->addCrumb(
                    'brand',
                    [
                        'label' => $title,
                        'title' => $title
                        //'link'  => $brand->getUrl()
                    ]
                );
            }
        }
        return $this;
    }

    public function getBrand()
    {
        if (!$this->hasData('brand')) {
            $this->setData('brand', $this->_coreRegistry->registry('current_brand'));
        }
        return $this->getData('brand');
    }

    public function getProductListHtml()
    {
        return $this->getChildHtml('product_list');
    }
    public function getIdentities()
    {
        return [$this->getBrand()->getOptionId()];
    }
}
