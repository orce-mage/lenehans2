<?php

namespace Swissup\SoldTogether\Model\Config\Source;

class AllowedProductTypes extends \Magento\Catalog\Model\Product\Type
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return array_filter(parent::toOptionArray(), function ($item) {
            return in_array($item['value'], ['simple', 'virtual', 'configurable']);
        });
    }
}
