<?php

namespace StripeIntegration\Payments\Controller\Ach;

use Magento\Framework\Exception\LocalizedException;
use StripeIntegration\Payments\Helper\Logger;

class Verification extends \Magento\Framework\App\Action\Action
{
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \StripeIntegration\Payments\Helper\Generic $helper,
        \StripeIntegration\Payments\Helper\Ach $achHelper
    )
    {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultFactory = $context->getResultFactory();
        $this->helper = $helper;
        $this->response = $context->getResponse();
        $this->redirect = $context->getRedirect();
        $this->achHelper = $achHelper;
    }

    public function execute()
    {
        $request = $this->helper->getRequest();
        $customerId = $request->getParam("customer", null);
        $bankAccountId = $request->getParam("account", null);

        if (empty($customerId) || empty($bankAccountId))
        {
            $norouteUrl = $this->helper->getUrl('noroute');
            $resultRedirect = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_REDIRECT);
            $result = $resultRedirect->setUrl($norouteUrl);
            return $result;
        }

        $amount1 = $request->getParam("amount1", null);
        $amount2 = $request->getParam("amount2", null);
        if (!empty($amount1) && !empty($amount2))
        {
            $this->achHelper->verify($customerId, $bankAccountId, $amount1, $amount2);
        }

        $page = $this->resultPageFactory->create();
        return $page;
    }
}
