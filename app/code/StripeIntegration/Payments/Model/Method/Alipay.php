<?php

namespace StripeIntegration\Payments\Model\Method;

class Alipay extends \StripeIntegration\Payments\Model\Method\Api\Sources
{
    const METHOD_CODE = 'stripe_payments_alipay';

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    protected $type = 'alipay';
}
