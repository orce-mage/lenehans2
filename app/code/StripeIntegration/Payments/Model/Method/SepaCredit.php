<?php

namespace StripeIntegration\Payments\Model\Method;

use Magento\Framework\DataObject;
use Magento\Quote\Api\Data\PaymentInterface;
use Magento\Framework\Exception\LocalizedException;

class SepaCredit extends \StripeIntegration\Payments\Model\Method\Api\Sources
{
    const METHOD_CODE = 'stripe_payments_sepa_credit';

    protected $_code = self::METHOD_CODE;

    protected $type = 'sepa_credit_transfer';

    protected $_formBlockType = 'StripeIntegration\Payments\Block\SepaCredit';
    protected $_infoBlockType = 'StripeIntegration\Payments\Block\SepaCreditInfo';

    protected $_canUseInternal = true;

    protected $saveSourceOnCustomer = true;
    protected $canReuseSource = true;

    public function assignData(DataObject $data)
    {
        if (!$data instanceof DataObject)
            $data = new DataObject($data);

        $additionalData = $data->getData(PaymentInterface::KEY_ADDITIONAL_DATA);
        if (!is_object($additionalData))
            $additionalData = new DataObject($additionalData ?: []);

        $info = $this->getInfoInstance();
        $info->setAdditionalInformation('sender_iban', $additionalData->getSenderIban());
        $info->setAdditionalInformation('sender_name', $additionalData->getSenderName());

        return $this;
    }

    public function initialize($paymentAction, $stateObject)
    {
        $info = $this->getInfoInstance();
        $order = $info->getOrder();
        $session = $this->checkoutHelper->getCheckout();
        $session->setStripePaymentsSepaCreditBankName(null);
        $session->setStripePaymentsSepaCreditIban(null);
        $session->setStripePaymentsSepaCreditBic(null);

        parent::initialize($paymentAction, $stateObject);

        if (!empty($this->source))
        {
            $session->setStripePaymentsSepaCreditBankName($this->source->sepa_credit_transfer->bank_name);
            $session->setStripePaymentsSepaCreditIban($this->source->sepa_credit_transfer->iban);
            $session->setStripePaymentsSepaCreditBic($this->source->sepa_credit_transfer->bic);

            $source = $this->sourceFactory->create();
            $source->setSourceId($this->source->id);
            $source->setOrderIncrementId($order->getIncrementId());
            if ($this->stripeCustomer)
                $source->setStripeCustomerId($this->stripeCustomer->id);
            $source->save();

            $comment = __("A payment is pending for this order. Source ID: %1", $this->source->id);
            $order->addStatusToHistory($status = \Magento\Sales\Model\Order::STATE_PENDING_PAYMENT, $comment, $isCustomerNotified = false);

            $order->setCanSendNewEmailFlag(true);
            $comment = __("Your order pending! To complete the payment, please transfer %1 to the bank account with IBAN %2 and BIC code %3.", $order->formatPriceTxt($order->getGrandTotal()), $this->source->sepa_credit_transfer->iban, $this->source->sepa_credit_transfer->bic);
            $order->setCustomerNote($comment);
        }
    }

    public function validate()
    {
        parent::validate();

        $info = $this->getInfoInstance();

        $collectCustomerAccount = $this->getConfigData("customer_bank_account");
        $isRequired = ($collectCustomerAccount == 2);

        if ($isRequired && !$this->helper->isAdmin())
        {
            $iban = $info->getAdditionalInformation('sender_iban');
            $name = $info->getAdditionalInformation('sender_name');

            if (empty($iban))
                throw new LocalizedException(__('Please provide an IBAN.'));

            if (empty($name))
                throw new LocalizedException(__('Please provide an account holder name.'));
        }

        return $this;
    }

    public function adjustParamsForMethod(&$params, $payment, $order, $quote)
    {
        if (empty($params[$this->type]))
            $params[$this->type] = [];

        $info = $this->getInfoInstance();
        $iban = $info->getAdditionalInformation('sender_iban');
        $name = $info->getAdditionalInformation('sender_name');

        if (!empty($iban) && !empty($name))
        {
            $params['receiver']['refund_attributes_method'] = "manual";
            $params[$this->type]['sender_iban'] = $iban;
            $params[$this->type]['sender_name'] = $name;
        }
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
