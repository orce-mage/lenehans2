<?php
/**
 * Copyright © www.magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageBig\AjaxFilter\Plugin\Catalog\Model;

class Config
{
    protected $helper;

    public function __construct(
        \MageBig\AjaxFilter\Helper\Data $helper
    ) {
        $this->helper = $helper;

    }

    /**
     * Adding custom options and changing labels
     *
     * @param \Magento\Catalog\Model\Config $catalogConfig
     * @param [] $options
     * @return []
     */
    public function afterGetAttributeUsedForSortByArray(\Magento\Catalog\Model\Config $catalogConfig, $options)
    {
        if ($this->helper->enableRatingSort()) {
            $options[\MageBig\AjaxFilter\Model\Layer\Filter\Rating::RATING_CODE] = __('Rating');
        }
        return $options;
    }
}


