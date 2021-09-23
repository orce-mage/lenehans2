<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SocialLogin\Controller\Social;

/**
 * Class Login
 * @package MageBig\SocialLogin\Controller\Social
 */
class Login extends AbstractSocial
{
    public function execute()
    {
        if ($this->session->isLoggedIn()) {
            $this->_redirect('customer/account');
            return;
        }

        $type = $this->getRequest()->getParam('type');

        if (!$type) {
            $this->_forward('noroute');

            return;
        }

        $this->session->setData('social_type', $type);

        if ($isCheckout = $this->getRequest()->getParam('is_checkout')) {
            $this->session->setData('social_in_checkout', $isCheckout);
        }

        try {
            $this->apiObject->getUserProfile($type);
        } catch (\Exception $e) {
            $this->setBodyResponse($e->getMessage());

            return;
        }
    }
}
