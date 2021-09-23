<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace MidoriWeb\Inventory\Block\Product;

use Magento\Catalog\Block\Product\View\Description;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\View\Element\Template\Context;

class Capel extends \Magento\Catalog\Block\Product\View\Description
{
    protected $_context;

    protected $_objectManager;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_context = $context;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $registry, $data);
    }

    /**
     * @return int
     */
    public function getCsLocationA()
    {
        $product = $this->getProduct();
        if(!$product->isAvailable())
            return null;
        $helper = $this->_objectManager->get('Magento\Catalog\Helper\Output');
        $code = 'capel_streets_bin_locations1';

        $attributeValue = $helper->productAttribute($product, $product->getData('capel_streets_bin_locations1'), $code);

        return $attributeValue;
    }
}
