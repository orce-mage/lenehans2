<?php

namespace StripeIntegration\Payments\Model\Method;

class Sofort extends \StripeIntegration\Payments\Model\Method\Api\Sources
{
    const METHOD_CODE = 'stripe_payments_sofort';

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    protected $type = 'sofort';

    public function adjustParamsForMethod(&$params, $payment, $order, $quote)
    {
        if (empty($params[$this->type]))
            $params[$this->type] = [];

        // Add the country
        $params[$this->type] += [
            'country' => $order->getBillingAddress()->getCountryId()
        ];
    }
}
