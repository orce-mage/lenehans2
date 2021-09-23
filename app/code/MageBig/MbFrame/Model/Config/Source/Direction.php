<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

/**
 * Used in creating options for Yes|No config value selection.
 */

namespace MageBig\MbFrame\Model\Config\Source;

class Direction implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options getter.
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'vertical', 'label' => __('Vertical')],
            ['value' => 'horizontal', 'label' => __('Horizontal')],
        ];
    }
}
