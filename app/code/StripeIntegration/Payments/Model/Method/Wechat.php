<?php

namespace StripeIntegration\Payments\Model\Method;

class Wechat extends \StripeIntegration\Payments\Model\Method\Api\Sources
{
    const METHOD_CODE = 'stripe_payments_wechat';

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    protected $type = 'wechat';

    public function getRedirectUrlFrom($source)
    {
        if (!empty($source->redirect->url))
            return $source->redirect->url;
        else if (!empty($source->wechat->qr_code_url))
            return $source->wechat->qr_code_url;

        return null;
    }
}
