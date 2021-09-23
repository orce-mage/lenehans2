<?php

namespace Searchanise\SearchAutocomplete\Model\Config\Source;

use \Magento\Framework\Option\ArrayInterface;
use \Searchanise\SearchAutocomplete\Model\Configuration;

class DescriptionAttr implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            Configuration::ATTR_SHORT_DESCRIPTION => __('Short Description'),
            Configuration::ATTR_DESCRIPTION       => __('Description'),
        ];
    }
}
