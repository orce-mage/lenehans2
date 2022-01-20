<?php

namespace StripeIntegration\Payments\Model\Method;

class Bancontact extends \StripeIntegration\Payments\Model\Method\Api\Sources
{
    const METHOD_CODE = 'stripe_payments_bancontact';

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    protected $type = 'bancontact';
}
