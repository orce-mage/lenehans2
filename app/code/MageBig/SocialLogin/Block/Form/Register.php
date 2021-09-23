<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SocialLogin\Block\Form;

/**
 * Class Register
 *
 * @package MageBig\SocialLogin\Block\Form
 */
class Register extends \Magento\Customer\Block\Form\Register
{
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        return $this;
    }
}