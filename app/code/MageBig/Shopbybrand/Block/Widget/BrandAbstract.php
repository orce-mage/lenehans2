<?php
/**
 * Copyright © 2020 MageBig, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\Shopbybrand\Block\Widget;

use MageBig\Shopbybrand\Model\BrandFactory as BrandFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;

class BrandAbstract extends Template implements \Magento\Widget\Block\BlockInterface
{
    protected $_brandFactory;
    protected $_brandObject;
    protected $_mediaUrl;
    protected $_objectManager;
    protected $_context;
    protected $_attributeCode;
    protected $_assetRepository;
    protected $_imageHelper;
    protected $_cacheTag = 'MAGEBIG_BRAND';
    protected $_template = '';
    protected $_categoryHeper = null;
    protected $_categoryRepository = null;
    protected $_coreRegistry = null;
    protected $_copeConfig;
    protected $_helper;
    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var Json
     */
    protected $_serialize;

    public function __construct(
        Template\Context $context,
        BrandFactory $brandFactory,
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Registry $coreRegistry,
        \MageBig\Shopbybrand\Helper\Data $helper,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\Layer\Category\FilterableAttributeList $FilterableAttributeList,
        Json $serialize,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_brandFactory = $brandFactory;
        $this->httpContext = $httpContext;
        $this->_context = $context;
        $this->_storeManager = $context->getStoreManager();
        $this->_mediaUrl = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
        $this->_objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_assetRepository = $context->getAssetRepository();
        $this->_helper = $helper;
        $this->_coreRegistry = $coreRegistry;
        $this->_copeConfig = $context->getScopeConfig();
        $this->_attributeCode = $helper->getStoreBrandCode();
        $this->_serialize = $serialize;

        $this->addData([
            'cache_lifetime' => 86400,
            'cache_tags' => [$this->_cacheTag]
        ]);
    }

    public function getConfigValue($path)
    {
        return $this->_copeConfig->getValue($path);
    }

    public function getAttributeCode()
    {
        return $this->_attributeCode;
    }

    public function getBrandObject($orderBy = 'brand_label', $order = 'asc', $onlyFeaturedBrands = false, $limit = false)
    {
        $registryName = 'mb_brand_' . $orderBy . '_' . $order . '_' . (string)$onlyFeaturedBrands . '_' . (string)$limit;
        $brandObject = $this->_coreRegistry->registry($registryName);
        if (!$brandObject) {
            $brandObject = [];
            $brand = $this->_brandFactory->create();
            $col = $brand->getCollection();
            $connection = $col->getConnection();

            $defaultStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;
            $storeId = $this->_storeManager->getStore()->getId();

            $select = $connection->select();
            $select->from(['main_table' => $col->getTable('eav_attribute_option') ], ['option_id', 'sort_order'])
                ->joinLeft([ 'cea' => $col->getTable('catalog_eav_attribute') ], 'main_table.attribute_id = cea.attribute_id', ['attribute_id'])
                ->joinLeft([ 'ea' => $col->getTable('eav_attribute') ], 'cea.attribute_id = ea.attribute_id', ['attribute_code'])
                ->joinLeft([ 'eaov' => $col->getTable('eav_attribute_option_value') ], 'eaov.option_id = main_table.option_id', ['store_id', 'default_brand_label' => 'eaov.value'])
                ->joinLeft([ 'eaov2' => $col->getTable('eav_attribute_option_value') ], "eaov2.option_id = main_table.option_id AND eaov2.store_id = {$storeId}", ['brand_label' => 'IF(eaov2.value_id > 0, eaov2.value, eaov.value)'])
                ->where("ea.attribute_code = '{$this->_attributeCode}'")
                ->where("eaov.store_id = {$defaultStoreId}")
                ->order($orderBy . ' ' . $order);

            if ($limit) {
                $select->limit($limit);
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
                    ->addAttributeToSelect(['mb_brand_thumbnail', 'mb_brand_url_key', 'mb_brand_is_featured'])->getItems();
                $brands = [];
                foreach ($brandItems as $brandItem) {
                    $brands[$brandItem->getData('option_id')] = $brandItem;
                }
                foreach ($rows as $row) {
                    $optionId = $row['option_id'];
                    $row['product_count'] = $this->_helper->getProductCount($this->_attributeCode, $optionId);
                    if (isset($brands[$optionId])) {
                        if ($onlyFeaturedBrands && (!$brands[$optionId]->getData('mb_brand_is_featured'))) {
                            continue;
                        }
                        if (!$brands[$optionId]->getData('is_active')) {
                            continue;
                        }
                        $brandModel = $brands[$optionId]->addData($row);
                    } else {
                        if ($onlyFeaturedBrands) {
                            continue;
                        }
                        $brandModel = new \Magento\Framework\DataObject($row);
                    }
                    $brandModel->setUrl($this->_helper->getBrandPageUrl($brandModel));
                    $brandObject[] = $brandModel;
                }
            }
            if ($brandObject) {
                $this->_coreRegistry->register($registryName, $brandObject);
            }
        }
        return $brandObject;
    }

    public function getTemplate()
    {
        if (parent::getTemplate()) {
            return parent::getTemplate();
        } else {
            return $this->_template;
        }
    }

    public function getThumbnailImage($brand, array $options = [])
    {
        return $this->_helper->getBrandImage($brand, 'mb_brand_thumbnail', $options);
    }

    public function getBrandPageUrl($brandModel)
    {
        return $this->_helper->getBrandPageUrl($brandModel);
    }

    public function getCacheKeyInfo()
    {
        return [
            $this->_cacheTag,
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            $this->getTemplate()
        ];
    }

    public function getIdentities()
    {
        return [$this->_cacheTag . '_' . $this->getTemplate()];
    }
}
