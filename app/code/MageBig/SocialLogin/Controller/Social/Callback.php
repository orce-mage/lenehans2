<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SocialLogin\Controller\Social;

use Hybridauth\Exception\Exception;
use Hybridauth\Hybridauth;
use Hybridauth\HttpClient;
use Hybridauth\Storage\Session;

/**
 * Class Callback
 *
 * @package MageBig\SocialLogin\Controller\Social
 */
class Callback extends AbstractSocial
{
    /**
     * @inheritdoc
     */
    public function execute()
    {
        if ($this->session->isLoggedIn()) {
            $this->_redirect('customer/account');
            return;
        }

        $type = $this->session->getData('social_type', true);

        if (!$type) {
            $this->_forward('noroute');

            return;
        }

        try {
            $userProfile = $this->apiObject->getUserProfile($type);

            if (!$userProfile->identifier) {
                return $this->emailRedirect($type);
            }
        } catch (\Exception $e) {
            $this->setBodyResponse($e->getMessage());

            return;
        }

        $customer = $this->apiObject->getCustomerBySocial($userProfile->identifier, $type);

        if (!$customer->getId()) {
            if (!$userProfile->email && $this->apiHelper->requireRealEmail()) {
                $this->session->setUserProfile($userProfile);

                return $this->_appendJs(sprintf("<script>window.close();window.opener.fakeEmailCallback('%s');</script>", $type));
            }

            $customer = $this->createCustomerProcess($userProfile, $type);
        }

        $this->refresh($customer);

        return $this->_appendJs();
    }

    /**
     * @param $key
     * @param null $value
     * @return bool|mixed
     */
    public function checkRequest($key, $value = null)
    {
        $param = $this->getRequest()->getParam($key, false);

        if ($value) {
            return $param == $value;
        }

        return $param;
    }
}
