<?php
/**
 * Copyright © www.magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageBig\AjaxFilter\Model\Adminhtml\Source;

/**
 * Config category source
 *
 * @SuppressWarnings(PHPMD.LongVariable)
 */
class FilterStyle implements \Magento\Framework\Data\OptionSourceInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => '',         'label' => __('Default')],
            ['value' => 'checkbox',  'label' => __('Multiple Select')],
        ];
    }
}
