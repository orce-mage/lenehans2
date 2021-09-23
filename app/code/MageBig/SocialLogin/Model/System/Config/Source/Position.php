<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SocialLogin\Model\System\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class Position
 *
 * @package MageBig\SocialLogin\Model\System\Config\Source
 */
class Position implements ArrayInterface
{
    const PAGE_LOGIN  = 1;
    const PAGE_CREATE = 2;
    const PAGE_POPUP  = 3;
    const PAGE_AUTHEN = 4;

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => '', 'label' => __('-- Please Select --')],
            ['value' => self::PAGE_LOGIN, 'label' => __('Customer Login Page')],
            ['value' => self::PAGE_CREATE, 'label' => __('Customer Create Page')],
            ['value' => self::PAGE_POPUP, 'label' => __('Social Popup Login')],
            ['value' => self::PAGE_AUTHEN, 'label' => __('Customer Authentication Popup')]
        ];
    }
}
