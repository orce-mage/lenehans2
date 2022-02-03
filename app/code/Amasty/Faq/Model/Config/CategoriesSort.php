<?php

namespace Amasty\Faq\Model\Config;

use Magento\Framework\Option\ArrayInterface;

class CategoriesSort implements ArrayInterface
{
    const SORT_BY_POSITION = 'position';
    const SORT_BY_NAME = 'name';
    const MOST_VIEWED = 'most_viewed';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::SORT_BY_POSITION, 'label' => __('Position')],
            ['value' => self::SORT_BY_NAME, 'label' => __('Name')],
            ['value' => self::MOST_VIEWED, 'label' => __('Most Viewed')]
        ];
    }
}
