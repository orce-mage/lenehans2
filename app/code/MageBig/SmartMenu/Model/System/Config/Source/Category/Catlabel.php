<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SmartMenu\Model\System\Config\Source\Category;

class Catlabel extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    protected $_options;

    public $scopeConfig;

    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Get list of existing category labels.
     */
    public function getAllOptions()
    {
        if (!$this->_options) {
            $this->_options = [
                ['value' => '', 'label' => ' '],
                ['value' => 'hot', 'label' => __('Hot')],
                ['value' => 'new', 'label' => __('New')],
                ['value' => 'sale', 'label' => __('Sale')],
                ['value' => 'trending', 'label' => __('Trending')],
            ];
        }

        return $this->_options;
    }
}
