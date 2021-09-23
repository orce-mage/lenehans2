<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\SocialLogin\Controller\Social;

use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Store\Model\StoreManagerInterface;
use MageBig\SocialLogin\Helper\Social as SocialHelper;
use MageBig\SocialLogin\Model\Social;

/**
 * Class AbstractSocialS
 *
 * @package MageBig\SocialLogin\Controller
 */
class Email extends AbstractSocial
{
    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * Email constructor.
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param SocialHelper $apiHelper
     * @param Social $apiObject
     * @param Session $customerSession
     * @param RawFactory $resultRawFactory
     * @param JsonFactory $resultJsonFactory
     * @param CustomerFactory $customerFactory
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        SocialHelper $apiHelper,
        Social $apiObject,
        Session $customerSession,
        RawFactory $resultRawFactory,
        JsonFactory $resultJsonFactory,
        CustomerFactory $customerFactory
    )
    {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->customerFactory   = $customerFactory;

        parent::__construct($context, $storeManager, $apiHelper, $apiObject, $customerSession, $resultRawFactory);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface|void
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultJsonFactory->create();

        $type = $this->apiHelper->setType($this->getRequest()->getParam('type', null));
        if (!$type) {
            $this->_forward('noroute');

            return;
        }

        $result = ['success' => false];

        $realEmail = $this->getRequest()->getParam('realEmail', null);
        if (!$realEmail) {
            $result['message'] = __('Email is Null');

            return $resultJson->setData($result);
        }

        $customer = $this->customerFactory->create()
            ->setWebsiteId($this->getStore()->getWebsiteId())
            ->loadByEmail($realEmail);
        if ($customer->getId()) {
            $result['message'] = __('Email already exists');

            return $resultJson->setData($result);
        }

        $userProfile        = $this->session->getUserProfile();
        $userProfile->email = $realEmail;

        $customer = $this->createCustomerProcess($userProfile, $type);
        $this->refresh($customer);

        $result['success'] = true;
        $result['message'] = __('Success!');
        $result['url']     = $this->_loginPostRedirect();

        return $resultJson->setData($result);
    }
}
