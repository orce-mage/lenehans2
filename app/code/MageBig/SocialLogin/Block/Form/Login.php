<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SocialLogin\Block\Form;

/**
 * Class Login
 *
 * @package MageBig\SocialLogin\Block\Form
 */
class Login extends \Magento\Customer\Block\Form\Login
{
    /**
     * @return string
     */
    public function getRegisterUrl()
    {
        return $this->_customerUrl->getRegisterUrl();
    }
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        return $this;
    }
}
