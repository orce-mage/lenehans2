<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SmartMenu\Model\System\Config\Source\Category;

class Position extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @return array
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['value' => 'left', 'label' => __('Left')],
                ['value' => 'right', 'label' => __('Right')],
                ['value' => 'center', 'label' => __('Center')],
                ['value' => 'fullwidth', 'label' => __('Fullwidth - Mega Style')],
            ];
        }

        return $this->_options;
    }
}
