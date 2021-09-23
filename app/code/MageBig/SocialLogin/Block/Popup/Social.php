<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SocialLogin\Block\Popup;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use MageBig\SocialLogin\Helper\Social as SocialHelper;
use MageBig\SocialLogin\Model\System\Config\Source\Position;

/**
 * Class Social
 *
 * @package MageBig\SocialLogin\Block\Popup
 */
class Social extends Template
{
    /**
     * @type \MageBig\SocialLogin\Helper\Social
     */
    protected $socialHelper;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \MageBig\SocialLogin\Helper\Social $socialHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        SocialHelper $socialHelper,
        array $data = []
    )
    {
        $this->socialHelper = $socialHelper;

        parent::__construct($context, $data);
    }

    /**
     * @return array
     */
    public function getAvailableSocials()
    {
        $availabelSocials = [];

        foreach ($this->socialHelper->getSocialTypes() as $socialKey => $socialLabel) {
            if ($this->socialHelper->isEnabled($socialKey)) {
                $availabelSocials[$socialKey] = [
                    'label' => $socialLabel,
                    'login_url' => $this->getLoginUrl($socialKey),
                ];
            }
        }

        return $availabelSocials;
    }

    /**
     * @param $key
     * @return string
     */
    public function getBtnKey($key)
    {
        switch ($key) {
            case 'vkontakte':
                $class = 'vk';
                break;
            default:
                $class = $key;
        }

        return $class;
    }

    /**
     * @return array
     */
    public function getSocialButtonsConfig()
    {
        $availableButtons = $this->getAvailableSocials();

        foreach ($availableButtons as $key => &$button) {
            $button['url'] = $this->getLoginUrl($key);
            $button['key'] = $key;
            $button['btn_key'] = $this->getBtnKey($key);
        }

        return $availableButtons;
    }

    /**
     * @param null $position
     * @return bool
     */
    public function canShow($position = null)
    {
        $displayConfig = $this->socialHelper->getConfigGeneral('social_display');
        $displayConfig = explode(',', $displayConfig);

        if (!$position) {
            $position = ($this->getRequest()->getFullActionName() == 'customer_account_login') ?
                Position::PAGE_LOGIN :
                Position::PAGE_CREATE;
        }

        return in_array($position, $displayConfig);
    }

    /**
     * @param $socialKey
     * @return string
     */
    public function getLoginUrl($socialKey)
    {
        $params['type'] = $socialKey;

        if ($this->getRequest()->getFullActionName() == 'checkout_index_index') {
            $params['is_checkout'] = 1;
        }


        return $this->getUrl('sociallogin/social/login', $params);
    }
}
