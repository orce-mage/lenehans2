<?php

namespace StripeIntegration\Payments\Model\Method;

class Multibanco extends \StripeIntegration\Payments\Model\Method\Api\Sources
{
    const METHOD_CODE = 'stripe_payments_multibanco';

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    protected $type = 'multibanco';

    /**
     * @return bool|mixed|string
     */
    public function getTestEmail()
    {
        $config     = $this->config->getConfigData('test_payment', 'multibanco');
        $storeEmail = $this->scopeConfig->getValue('trans_email/ident_general/name');
        if ($config == 1)
        {
            $fillNever = 'multibanco+fill_never@stripe.com';
            if ($this->isEmailValid($storeEmail))
                return str_replace('@', '+fill_never@', $storeEmail);
            return $fillNever;
        }
        else if ($config == 2)
        {
            $fillNow = 'multibanco+fill_now@stripe.com';
            if ($this->isEmailValid($storeEmail))
                return str_replace('@', '+fill_now@', $storeEmail);
            return $fillNow;
        }
        return false;
    }
}
