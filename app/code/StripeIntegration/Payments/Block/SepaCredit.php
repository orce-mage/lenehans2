<?php

namespace StripeIntegration\Payments\Block;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;
use StripeIntegration\Payments\Gateway\Response\FraudHandler;

class SepaCredit extends \StripeIntegration\Payments\Block\Info
{
    protected $_template = 'form/sepa_credit.phtml';

    public $collectCustomerBankAccount;

    public function isBankAccountOptional()
    {
        $this->getBankAccountConfig();
        return ($this->collectCustomerBankAccount == 1);
    }

    public function isBankAccountRequired()
    {
        $this->getBankAccountConfig();
        return ($this->collectCustomerBankAccount == 2);
    }

    protected function getBankAccountConfig()
    {
        return ($this->collectCustomerBankAccount = 0);

        // if (!isset($this->collectCustomerBankAccount))
        // {
        //     $storeId = $this->_helper->getStoreId();
        //     $this->collectCustomerBankAccount = $this->_scopeConfig->getValue("payment/stripe_payments_sepa_credit/customer_bank_account", ScopeInterface::SCOPE_STORE, $storeId);
        // }
    }
}
