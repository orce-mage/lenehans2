<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SocialLogin\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\ScopeInterface;
use MageBig\SocialLogin\Helper\Data as HelperData;

/**
 * Class Social
 *
 * @package MageBig\SocialLogin\Helper
 */
class Social extends HelperData
{
    /**
     * @return array
     */
    public function getSocialTypes()
    {
        $socialTypes = $this->getSocialTypesArray();
        uksort(
            $socialTypes,
            function ($a, $b) {
                $sortA = $this->getConfigValue("sociallogin/{$a}/sort_order") ?: 0;
                $sortB = $this->getConfigValue("sociallogin/{$b}/sort_order") ?: 0;
                if ($sortA === $sortB) {
                    return 0;
                }
                return ($sortA < $sortB) ? -1 : 1;
            }
        );
        return $socialTypes;
    }

    /**
     * @param $type
     * @return array|mixed
     */
    public function getSocialConfig($type)
    {
        $apiData = [
            'Google' => ['scope' => 'profile email']
        ];
        if ($type && array_key_exists($type, $apiData)) {
            return $apiData[$type];
        }
        return [];
    }

    /**
     * @return array|null
     */
    public function getAuthenticateParams($type)
    {
        return null;
    }

    /**
     * @param null $storeId
     *
     * @return mixed
     */
    public function isEnabled($type)
    {
        return $this->getConfigValue("sociallogin/{$type}/is_enabled");
    }

    /**
     * @param null $storeId
     *
     * @return array|mixed
     */
    public function isSignInAsAdmin($storeId = null)
    {
        return $this->getConfigValue("sociallogin/{$this->_type}/admin", $storeId);
    }

    /**
     * @return array
     * @throws LocalizedException
     */
    public function getAuthConfig($type): array
    {
        return [
            'callback' => $this->getCallbackUrl(),
            'providers' => $this->getProviderData($type),
            'debug_mode' => false,
            'debug_file' => BP . '/var/log/social.log'
        ];
    }

    /**
     * @return array|array[]
     */
    public function getProviderData($type): array
    {
        $data = [];
        $label = ucfirst($type);

        if ($isEnable = $this->getConfigValue("sociallogin/{$type}/is_enabled")) {
            $config = [
                'enabled' => $isEnable,
                'keys' => [
                    'id' => trim($this->getConfigValue("sociallogin/{$type}/app_id")),
                    'key' => trim($this->getConfigValue("sociallogin/{$type}/app_id")),
                    'secret' => trim($this->getConfigValue("sociallogin/{$type}/app_secret"))
                ]
            ];
            //$config = array_merge($config, $this->getSocialConfig($label));
            $data = [
                $label => $config
            ];
        }

        return $data;
    }

    /**
     * @return string
     * @throws LocalizedException
     */
    public function getCallbackUrl()
    {
        $storeId = $this->getScopeId();

        return $this->_getUrl(
            'sociallogin/social/callback',
            [
                '_nosid' => true,
                '_scope' => $storeId,
                '_secure' => true
            ]
        );
    }

    /**
     * @return int
     * @throws LocalizedException
     */
    protected function getScopeId()
    {
        $scope = $this->_request->getParam(ScopeInterface::SCOPE_STORE) ?: $this->storeManager->getStore()->getId();
        if ($website = $this->_request->getParam(ScopeInterface::SCOPE_WEBSITE)) {
            $scope = $this->storeManager->getWebsite($website)->getDefaultStore()->getId();
        }
        return $scope;
    }

    /**
     * @return array
     */
    public function getSocialTypesArray()
    {
        return [
            'facebook' => 'Facebook',
            'google' => 'Google',
            'twitter' => 'Twitter',
            'amazon' => 'Amazon',
            'linkedin' => 'LinkedIn',
            'yahoo' => 'Yahoo',
            'foursquare' => 'Foursquare',
            'vkontakte' => 'Vkontakte',
            'instagram' => 'Instagram',
            'github' => 'Github',
            'live' => 'Live'
        ];
    }
}
