<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SmartMenu\Model\System\Config\Source\Category;

class Style extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['value' => 'dropdown_simple', 'label' => __('Simple Dropdown')],
                ['value' => 'dropdown_mega', 'label' => __('Mega Dropdown')],
                ['value' => 'dropdown_group', 'label' => __('Group Dropdown')],
            ];
        }

        return $this->_options;
    }
}
