<?php

namespace StripeIntegration\Payments\Model\Method;

use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\Exception\LocalizedException;

class Sepa extends \StripeIntegration\Payments\Model\Method\Api\Sources
{
    const METHOD_CODE = 'stripe_payments_sepa';

    /**
     * Payment code
     *
     * @var string
     */
    protected $_code = self::METHOD_CODE;

    protected $type = 'sepa_debit';

    /**
     * @var string
     */
    protected $_formBlockType = 'StripeIntegration\Payments\Block\Sepa';

    /**
     * Payment Method feature
     *
     * @var bool
     */
    protected $_canUseInternal = true;

    protected $saveSourceOnCustomer = true;

    /**
     * Assign data to info model instance
     *
     * @param DataObject|mixed $data
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function assignData(DataObject $data)
    {
        if (!$data instanceof DataObject) {
            $data = new DataObject($data);
        }

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_object($additionalData)) {
            $additionalData = new DataObject($additionalData ?: []);
        }

        /** @var \Magento\Quote\Model\Quote\Payment $info */
        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('iban', $additionalData->getIban());

        return $this;
    }

    /**
     * Validate payment method information object
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function validate()
    {
        parent::validate();

        /** @var \Magento\Quote\Model\Quote\Payment $info */
        $info = $this->getInfoInstance();

        $iban = $info->getAdditionalInformation('iban');
        if (empty($iban)) {
            throw new LocalizedException(__('Invalid IBAN provided.'));
        }

        return $this;
    }

    public function adjustParamsForMethod(&$params, $payment, $order, $quote)
    {
        if (empty($params[$this->type]))
            $params[$this->type] = [];

        // Make the source reusable
        unset($params['amount']);

        // Add IBAN
        $iban = $payment->getAdditionalInformation('iban');
        if (empty($iban)) {
            throw new LocalizedException(__('No IBAN provided.'));
        }

        $params[$this->type] += [
            'iban' => $iban
        ];
    }

    public function getRedirectUrlFrom($source)
    {
        $redirectUrl = $this->urlBuilder->getUrl('stripe/payment/index',
            [
                '_secure' => $this->request->isSecure(),
                'source' => $source->id,
                'client_secret' => $source->client_secret
            ]
        );

        return $redirectUrl;
    }
}
