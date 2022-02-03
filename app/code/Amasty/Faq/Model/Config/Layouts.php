<?php

namespace Amasty\Faq\Model\Config;

use Magento\Framework\Option\ArrayInterface;

class Layouts implements ArrayInterface
{
    const LAYOUT_2COLUMNS_LEFT_SIDEBAR = '2columns-left';
    const LAYOUT_2COLUMNS_RIGHT_SIDEBAR = '2columns-right';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::LAYOUT_2COLUMNS_LEFT_SIDEBAR, 'label' => __('2 columns with left sidebar')],
            ['value' => self::LAYOUT_2COLUMNS_RIGHT_SIDEBAR, 'label' => __('2 columns with right sidebar')]
        ];
    }
}
