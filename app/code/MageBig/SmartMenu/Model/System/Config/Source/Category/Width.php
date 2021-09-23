<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SmartMenu\Model\System\Config\Source\Category;

class Width extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['value' => '', 'label' => 'Select width'],
                ['value' => '1', 'label' => __('8.3%')],
                ['value' => '2', 'label' => __('16.6%')],
                ['value' => '3', 'label' => __('25.0%')],
                ['value' => '4', 'label' => __('33.3%')],
                ['value' => '5', 'label' => __('41.6%')],
                ['value' => '6', 'label' => __('50.0%')],
            ];
        }

        return $this->_options;
    }
}
