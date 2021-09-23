<?php

namespace MageBig\Shopbybrand\Controller;

use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Action\Forward;
use Magento\Framework\App\RouterInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Url;

class Router implements RouterInterface
{
    protected $actionFactory;

    protected $_storeManager;

    protected $_brandFactory;

    protected $_scopeConfig;

    protected $brandHelper;

    public function __construct(
        \Magento\Framework\App\ActionFactory $actionFactory,
        \MageBig\Shopbybrand\Model\BrandFactory $brandFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \MageBig\Shopbybrand\Helper\Data $brandHelper
    )
    {
        $this->actionFactory = $actionFactory;
        $this->_brandFactory = $brandFactory;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->brandHelper = $brandHelper;
        $this->_attributeCode = $this->_scopeConfig->getValue('magebig_shopbybrand/general/attribute_code');
    }

    /**
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ActionInterface|null
     * @throws NoSuchEntityException
     */
    public function match(RequestInterface $request)
    {
        $identifier = trim($request->getPathInfo(), '/');
        $urlKey = explode('/', $identifier);
        $brandRoute = trim(trim($this->brandHelper->getBrandRoute()), '/');

        if ($identifier === $brandRoute) {
            $request->setModuleName('ourbrands');
            $request->setControllerName('index');
            $request->setActionName('index');

            return $this->actionFactory->create(Forward::class);
        }

        if (isset($urlKey[1]) && ($urlKey[0] == $brandRoute)) {
            $urlKey = strtolower(urldecode($urlKey[1]));
            $storeId = $this->_storeManager->getStore()->getId();
            $defaultStoreId = \Magento\Store\Model\Store::DEFAULT_STORE_ID;

            $brandCode = $this->brandHelper->getStoreBrandCode();

            $brandCollection = $this->_brandFactory->create()
                ->getCollection();

            $optionValueTable = $brandCollection->getTable('eav_attribute_option_value');
            $select = $brandCollection->getConnection()->select();
            $select->from(['main_table' => $optionValueTable], ['option_id']);
            $select->joinLeft(['eao' => $brandCollection->getTable('eav_attribute_option')], 'main_table.option_id = eao.option_id', ['attribute_id']);
            $select->joinLeft(['ea' => $brandCollection->getTable('eav_attribute')], 'eao.attribute_id = ea.attribute_id', ['attribute_code']);
            $select->where('main_table.store_id IN (' . $defaultStoreId . ', ' . $storeId . ')')
                ->where("LOWER(REPLACE(REPLACE(RTRIM(main_table.value), ' ', '-'), \"'\", '-')) = \"{$urlKey}\"")
                ->where("ea.attribute_code = '{$brandCode}'")
                ->order('main_table.store_id DESC')
                ->limit(1);

            $brand = $brandCollection->getConnection()->fetchRow($select);

            if ($brand) {
                $request->setModuleName('ourbrands')
                    ->setControllerName('index')
                    ->setActionName('view')
                    ->setParam($brandCode, $brand['option_id']);
                $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $brandRoute.'/' . $urlKey);

                return $this->actionFactory->create(Forward::class);
            }

            $brandCollection->setStore($storeId)
                ->addAttributeToFilter('mb_brand_url_key', $urlKey);
            $brand = $brandCollection->getFirstItem();

            if ($brand->getId()) {
                $request->setModuleName('ourbrands')
                    ->setControllerName('index')
                    ->setActionName('view')
                    ->setParam($brandCode, $brand->getOptionId());
                $request->setAlias(Url::REWRITE_REQUEST_PATH_ALIAS, $brandRoute.'/' . $urlKey);
                return $this->actionFactory->create(Forward::class);
            }
        }
        return null;
    }
}
