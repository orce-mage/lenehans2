<?php

namespace StripeIntegration\Payments\Controller\Authorization;

use Magento\Framework\Exception\LocalizedException;
use StripeIntegration\Payments\Helper\Logger;

class Multishipping extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \Magento\Customer\Model\Session $customerSession
    )
    {
        parent::__construct($context);

        $this->resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->customerSession = $customerSession;
    }

    public function execute()
    {
        $successUrl = $this->customerSession->getAuthorizationSuccessUrl();
        $failUrl = $this->customerSession->getAuthorizationFailUrl();

        $page = $this->resultPageFactory->create();
        $page->addHandle('stripe_authorization_multishipping');
        return $page;
    }
}
