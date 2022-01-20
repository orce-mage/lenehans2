<?php

namespace StripeIntegration\Payments\Block\Ach;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;
use StripeIntegration\Payments\Gateway\Response\FraudHandler;

class Verification extends \Magento\Framework\View\Element\Template
{
    public $account = null;
    public $customer = null;
    public $customerId = null;
    public $bankAccountId = null;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \StripeIntegration\Payments\Helper\Generic $helper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $request = $this->helper->getRequest();
        $this->request = $helper->getRequest();
        $this->customerId = $request->getParam("customer", null);
        $this->bankAccountId = $request->getParam("account", null);
        $this->stripeCustomer = $helper->getCustomerModel();
    }

    public function getBankAccountLast4()
    {
        return $this->account->last4;
    }

    public function accountExists()
    {
        if (!empty($this->account))
            return true;

        try
        {
            if (empty($this->customer))
                $this->customer = $this->stripeCustomer->retrieveByStripeID($this->customerId);

            if (empty($this->customer))
                return false;

            try
            {
                $account = $this->customer->sources->retrieve($this->bankAccountId);
            }
            catch (\Exception $e)
            {
                return false;
            }

            if (isset($account->id))
                $this->account = $account;

            return true;
        }
        catch (\Exception $e)
        {
            $this->helper->dieWithError($e->getMessage());
        }

        return false;
    }

    public function accountVerified()
    {
        return $this->account->status == "verified";
    }
}
