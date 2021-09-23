<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SocialLogin\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Color
 *
 * @package MageBig\SocialLogin\Model\System\Config\Source
 */
class Color implements ArrayInterface
{
    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        foreach ($this->toArray() as $value => $label) {
            $options[] = [
                'value' => $value,
                'label' => $label
            ];
        }

        return $options;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        return [
            '#3399cc'  => __('Default'),
            'orange'   => __('Orange'),
            'green'    => __('Green'),
            'black'    => __('Black'),
            'blue'     => __('Blue'),
            'darkblue' => __('Dark Blue'),
            'pink'     => __('Pink'),
            'red'      => __('Red'),
            'violet'   => __('Violet'),
            'custom'   => __('Custom'),
        ];
    }
}
