<?php
/**
 * @category    WeltPixel
 * @package     WeltPixel_EnhancedEmail
 * @copyright   Copyright (c) 2018 Weltpixel
 * @author      Nagy Attila @ Weltpixel TEAM
 */

namespace WeltPixel\EnhancedEmail\Block;

/**
 * Class MarkupCustomer
 * @package WeltPixel\EnhancedEmail\Block
 */
class MarkupCustomer extends \Magento\Customer\Block\CustomerData
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\User\Helper\Data
     */
    protected $_userHelper;


    /**
     * MarkupNewsletter constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\User\Helper\Data $user_helper,
        \Magento\Backend\Block\Template\Context $context,
        array $data = []
    )
    {
        $this->_customerSession = $customerSession;
        $this->_userHelper = $user_helper;
        parent::__construct($context, $data);
    }

    /**
     * @return bool
     */
    public function isLoggedIn()
    {
        if ($customerId = $this->_customerSession->getCustomerId()) {
            return true;
        }

        return false;
    }

    /**
     * @return string
     */
    public function getCustomerToken()
    {
        return $this->_userHelper->generateResetPasswordLinkToken();
    }

    /**
     * @param $link
     * @param array $params
     * @return string
     */
    public function getFrontendUrl($link, $params = []) {
        $url = $this->_urlBuilder->getUrl($link, $params);
        return $url;
    }


}