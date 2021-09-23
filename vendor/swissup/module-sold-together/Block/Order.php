<?php

namespace Swissup\SoldTogether\Block;

class Order extends Related
{
    /**
     * Can be 'order' or 'customer'
     */
    const SOLDTOGETHER_ENTITY = 'order';

    /**
     *  Product collection
     *
     * @var Collection
     */
    protected $_collection;

    /**
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * @var \Magento\Framework\Locale\FormatInterface
     */
    protected $localeFormat;

    /**
     * @param \Magento\Framework\App\Http\Context       $httpContext
     * @param \Magento\Framework\Locale\FormatInterface $localeFormat
     * @param Context                                   $context
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Framework\App\Http\Context $httpContext,
        \Magento\Framework\Locale\FormatInterface $localeFormat,
        Context $context,
        array $data = []
    ) {
        $this->httpContext = $httpContext;
        $this->localeFormat = $localeFormat;
        return parent::__construct($context, $data);
    }

    /**
     * Initialize block's cache
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->addData(
            [
                'cache_lifetime' => 86400,
                'cache_tags' => [\Magento\Catalog\Model\Product::CACHE_TAG]
            ]
        );
    }

    /**
     * Get Key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return [
            'SOLDTOGETHER_' . static::SOLDTOGETHER_ENTITY,
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_GROUP),
            'template' => $this->getTemplate(),
            'name' => $this->getNameInLayout(),
            $this->getProductsCount(),
            implode(',', $this->getProductIds()),
        ];
    }

    /**
     * {@inheritdoc}
     */
    protected function _beforeToHtml()
    {
        if (!$this->getConfig('enabled')) {
            return $this;
        }

        return parent::_beforeToHtml();
    }

    /**
     * Get price format
     *
     * @return array
     */
    public function getPriceFormat()
    {
        return $this->localeFormat->getPriceFormat();
    }

    /**
     * Get tax display config
     *
     * @return string
     */
    public function getTaxDisplayConfig()
    {
        return $this->_scopeConfig->getValue(
            "tax/display/type",
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * Get config value
     *
     * @param  string $key
     * @return string
     */
    public function getConfig($key)
    {
        return $this->_scopeConfig->getValue(
            sprintf("soldtogether/%s/%s", static::SOLDTOGETHER_ENTITY, $key),
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return integer
     */
    public function getProductsCount()
    {
        return (int) $this->getConfig('count');
    }

    /**
     * Get layout style for block
     *
     * @return string
     */
    public function getLayoutStyle()
    {
        if (!$this->hasData('layout_style')) {
            $this->setData('layout_style', $this->getConfig('layout'));
        }

        return $this->getData('layout_style');
    }

    /**
     * Get list of allowed product types to display
     *
     * @return array
     */
    public function getAllowedProductTypes()
    {
        $parentAllowed = parent::getAllowedProductTypes();

        return $parentAllowed ?: explode(',', $this->getConfig('allowed_product_types'));
    }

    /**
     * {@inheritdoc}
     */
    public function showOutOfStock()
    {
        return !!$this->getConfig('out');
    }

    /**
     * {@inheritdoc}
     */
    public function canUseRandom()
    {
        return !!$this->getConfig('random');
    }

    /**
     * {@inheritdoc}
     */
    public function getRelation()
    {
        return static::SOLDTOGETHER_ENTITY;
    }
}
