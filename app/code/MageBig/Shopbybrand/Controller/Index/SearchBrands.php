<?php
/**
 * Copyright © 2020 MageBig, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\Shopbybrand\Controller\Index;

use MageBig\Shopbybrand\Model\BrandFactory;
use Magento\Framework\View\Result\LayoutFactory;

class SearchBrands extends \Magento\Framework\App\Action\Action
{
    protected $_brandObject;

    protected $_urlManager;

    protected $_storeManager;

    protected $_imageHelper;

    protected $_coreRegistry;

    protected $_mediaUrl;

    protected $resultLayoutFactory;

    protected $_brandFactory;

    protected $_attributeCode;

    protected $_helper;

    protected $_context;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        LayoutFactory $resultLayoutFactory,
        BrandFactory $brandFactory,
        \MageBig\Shopbybrand\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->_urlManager = $context->getUrl();
        $this->_storeManager = $storeManager;
        $this->_coreRegistry = $coreRegistry;
        $this->resultLayoutFactory = $resultLayoutFactory;
        $this->_brandFactory = $brandFactory;
        $this->_mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $this->_imageHelper = $this->_objectManager->get('MageBig\Shopbybrand\Helper\Image');
        $this->_attributeCode = $this->_objectManager
            ->get('Magento\Framework\App\Config\ScopeConfigInterface')
            ->getValue('magebig_shopbybrand/general/attribute_code');
        $this->_helper = $helper;
    }

    public function getUrl($urlKey, $params = null)
    {
        return $this->_urlManager->getUrl($urlKey, $params);
    }

    public function getAllBrandsArray($query = false, $orderBy = 'brand_label', $order = 'asc')
    {
        if (!$this->_brandObject) {
            $this->_brandObject = [];
            $brand = $this->_brandFactory->create();
            $col = $brand->getCollection();
            $connection = $col->getConnection();
            $defaultStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            $storeId = $this->_storeManager->getStore()->getId();

            $select = $connection->select();
            $select->from(['main_table' => $col->getTable('eav_attribute_option') ], ['option_id', 'attribute_id', 'sort_order'])
                ->joinLeft([ 'cea' => $col->getTable('catalog_eav_attribute') ], 'main_table.attribute_id = cea.attribute_id')
                ->joinLeft([ 'ea' => $col->getTable('eav_attribute') ], 'cea.attribute_id = ea.attribute_id', ['attribute_code'])
                ->joinLeft([ 'eaov' => $col->getTable('eav_attribute_option_value') ], 'eaov.option_id = main_table.option_id', ['brand_label' => 'value', 'store_id'])
                ->where("ea.attribute_code = '{$this->_attributeCode}'")
                ->where("eaov.store_id IN ({$defaultStoreId}, {$storeId})")
                ->group("main_table.option_id")
                ->order($orderBy . ' ' . $order);

            if ($query) {
                $select->where("value LIKE '%{$query}%'");
            }

            $rows = $connection->fetchAll($select);

            if (count($rows) > 0) {
                $optionIds = [];
                foreach ($rows as $row) {
                    $optionIds[] = $row['option_id'];
                }
                $brandItems = $this->_brandFactory
                    ->create()
                    ->getCollection()->setStore($storeId)
                    ->addFieldToFilter('option_id', ['in' => $optionIds])
                    ->addAttributeToSelect(['mb_brand_thumbnail','mb_brand_url_key'])->getItems();
                $brands = [];
                $brandRoute = $this->_helper->getBrandRoute();

                foreach ($brandItems as $brandItem) {
                    $brands[$brandItem->getData('option_id')] = $brandItem;
                }

                foreach ($rows as $row) {
                    $optionId = $row['option_id'];
                    if (isset($brands[$optionId])) {
                        if (isset($brands[$optionId]['is_active'])) {
                            if ($brands[$optionId]['is_active'] == 0) {
                                continue;
                            }
                        }
                        $brandModel = $brands[$optionId]->addData($row);
                    } else {
                        $brandModel = new \Magento\Framework\DataObject($row);
                    }
                    if ($brandModel->getData('mb_brand_url_key')) {
                        $brandModel->setUrl($this->getUrl($brandRoute) . $brandModel->getData('mb_brand_url_key'));
                    } else {
                        $brandModel->setUrl($this->getUrl($brandRoute) . urlencode(str_replace(' ', '-', strtolower(trim($brandModel->getData('brand_label'))))));
                    }
                    $this->_brandObject[] = $brandModel;
                }
            }
        }
        return $this->_brandObject;
    }

    public function execute()
    {
        $brandLabels = [];
        $query = $this->getRequest()->getParam('term', false);
        $brandData = $this->getAllBrandsArray($query);
        if (count($brandData)) {
            foreach ($brandData as $brand) {
                $brandLabels[] = [
                    'label' => $brand->getData('brand_label'),
                    'value' => $brand->getData('brand_label'),
                    'url'   => $brand->getData('url'),
                    'img'   => $this->getThumbnailImage($brand, ['width' => 50, 'height' => 50])
                ];
            }
        }
        echo json_encode($brandLabels);
    }

    public function getThumbnailImage($brand, array $options = [])
    {
        if (!($brandThumb = $brand->getMbBrandThumbnail())) {
            $brandThumb = 'catalog/brand/placeholder.png';
        }
        if (isset($options['width']) || isset($options['height'])) {
            if (!isset($options['width'])) {
                $options['width'] = null;
            }
            if (!isset($options['height'])) {
                $options['height'] = null;
            }
            return $this->_imageHelper->init($brandThumb)->resize($options['width'], $options['height'])->__toString();
        } else {
            return $this->_mediaUrl . $brandThumb;
        }
    }
}
