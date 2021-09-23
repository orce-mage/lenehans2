<?php
/**
 * Copyright © 2020 MageBig, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\Shopbybrand\Model\Config\Source;

class Sortby implements \Magento\Framework\Option\ArrayInterface
{
	public function toOptionArray()
    {
        return [
            ['value' => 'brand_label',  'label' => __('Brand Label')],
            ['value' => 'sort_order',   'label' => __('Attribute Option Sort Order')]
        ];
    }

}
