<?php

namespace MageBig\AjaxSearch\Block;

/**
 * Autocomplete class used for transport config data
 */
class Autocomplete extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \MageBig\AjaxSearch\Helper\Data
     */
    protected $helperData;

    protected $_storeManager;

    /**
     * Autocomplete constructor.
     *
     * @param \MageBig\AjaxSearch\Helper\Data $helperData
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \MageBig\AjaxSearch\Helper\Data $helperData,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {

        $this->helperData = $helperData;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve search delay in milliseconds (500 by default)
     *
     * @return int
     */
    public function getSearchDelay()
    {
        return $this->helperData->getSearchDelay();
    }

    /**
     * Retrieve search action url
     *
     * @return string
     */
    public function getSearchUrl()
    {
        return $this->getUrl("magebig_ajaxsearch/ajax/index");
    }

    public function getLifetime()
    {
        return $this->helperData->getLifetime();
    }

    public function getStoreCode()
    {
        return $this->_storeManager->getStore()->getCode();
    }

    public function getCurrencyCode(){
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }
}
