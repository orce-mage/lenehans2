<?php

namespace Swissup\SoldTogether\Block\Product\Renderer;

class PriceConfig extends \Magento\Catalog\Block\Product\View
{
    /**
     * {@inheritdoc}
     */
    public function getProduct()
    {
        return $this->hasData('product') ?
            $this->getData('product') :
            new \Magento\Framework\DataObject(['identities' => []]);
    }
}
