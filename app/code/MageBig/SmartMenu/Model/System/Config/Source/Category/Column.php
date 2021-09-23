<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SmartMenu\Model\System\Config\Source\Category;

class Column extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['value' => '', 'label' => ' '],
                ['value' => '1', 'label' => __('1 column')],
                ['value' => '2', 'label' => __('2 columns')],
                ['value' => '3', 'label' => __('3 columns')],
                ['value' => '4', 'label' => __('4 columns')],
                ['value' => '5', 'label' => __('5 columns')],
                ['value' => '6', 'label' => __('6 columns')],
            ];
        }

        return $this->_options;
    }
}
