<?php

namespace StripeIntegration\Payments\Model\Method;

use Magento\Framework\Exception\CouldNotSaveException;

class Klarna extends \StripeIntegration\Payments\Model\Method\Api\Sources
{
    const METHOD_CODE = 'stripe_payments_klarna';
    protected $_code = self::METHOD_CODE;
    protected $type = 'klarna';
    protected $_isInitializeNeeded = false;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canInvoiceFromAdmin = true;

    public function assignData(\Magento\Framework\DataObject $data)
    {
        parent::assignData($data);

        $info = $this->getInfoInstance();
        $sourceId = $data->getAdditionalData("source_id");
        $info->setAdditionalInformation('source_id', $sourceId);

        return $this;
    }

    public function associateSourceWithOrder($payment)
    {
        $order = $payment->getOrder();
        $info = $this->getInfoInstance();
        $sourceId = $info->getAdditionalInformation("source_id");

        // Due to the nature of Klarna authorizing the payment at the front-end, we don't have an Order # in the Source
        // metadata, so we instead save it in the cache for 1 hour
        $this->cache->save($data = $order->getIncrementId(), $key = $sourceId, $tags = ["stripe_payments"], $lifetime = 12 * 60 * 60);
    }

    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->associateSourceWithOrder($payment);

        return parent::authorize($payment, $amount);
    }

    public function capture(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        $this->associateSourceWithOrder($payment);

        if ($amount > 0)
        {
            $token = $payment->getTransactionId();
            if (empty($token))
                $token = $payment->getLastTransId(); // In case where the transaction was not created during the checkout, i.e. with a Stripe Webhook redirect

            if ($token)
            {
                // Capture an authorized payment from the admin area
                $this->helper->capture($token, $payment, $amount, false);
            }
        }

        return parent::capture($payment, $amount);
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        if (parent::isAvailable($quote) === false) {
            return false;
        }

        if (!$this->isActive($quote ? $quote->getStoreId() : null)) {
            return false;
        }

        if (!$quote) {
            return false;
        }

        // Check if the currency is supported
        $allowedCurrencies = \StripeIntegration\Payments\Helper\Klarna::getSupportedCurrencies();

        if (!in_array($quote->getQuoteCurrencyCode(), $allowedCurrencies))
            return false;

        // Check if the country is supported
        $billingAddress = $quote->getBillingAddress();
        if (empty($billingAddress))
            return false;

        $countryId = $billingAddress->getCountryId();
        if (empty($countryId))
            return false;

        $allowedCountries = explode(",", $this->config->getConfigData('specificcountry', 'klarna'));
        if (empty($allowedCountries))
            $allowedCountries = \StripeIntegration\Payments\Helper\Klarna::getSupportedCountries();

        if (!in_array($countryId, $allowedCountries))
            return false;

        $availablePaymentOptions = $this->getAvailablePaymentOptions($quote);
        if (empty($availablePaymentOptions["paymentOptions"]))
        {
            return false;
        }
        else
        {
            $this->sessionManager->setKlarnaAvailablePaymentOptions($availablePaymentOptions);
            $this->sessionManager->setKlarnaSourceId($availablePaymentOptions["sourceId"]);
        }

        return true;
    }

    public function getAvailablePaymentOptions($quote = null)
    {
        try
        {
            $sourceId = $this->sessionManager->getKlarnaSourceId();

            if ($sourceId)
            {
                try
                {
                    $source = $this->klarnaHelper->updateSource($sourceId, $quote);
                }
                catch (\Exception $e)
                {
                    $source = $this->klarnaHelper->createSource($quote);
                }
            }
            else
                $source = $this->klarnaHelper->createSource($quote);

            $paymentOptions = $this->klarnaHelper->getPaymentOptions($source);

            return [
                "sourceId" => $source->id,
                "clientSecret" => $source->client_secret,
                "clientToken" => $source->klarna->client_token,
                "paymentOptions" => $paymentOptions
            ];
        }
        catch (\Exception $e)
        {
            $this->helper->logError("Klarna payment options could not be retrieved: " . $e->getMessage());
            return [];
        }
    }
}
