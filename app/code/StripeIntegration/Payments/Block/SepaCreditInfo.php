<?php

namespace StripeIntegration\Payments\Block;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;
use StripeIntegration\Payments\Gateway\Response\FraudHandler;

class SepaCreditInfo extends ConfigurableInfo
{
    protected $_template = 'form/sepa_credit_info.phtml';
    protected $source = null;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Payment\Gateway\ConfigInterface $config,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        \StripeIntegration\Payments\Helper\Generic $paymentsHelper,
        array $data = []
    ) {
        parent::__construct($context, $config, $data);

        $this->pricingHelper = $pricingHelper;
        $this->storeManager = $context->getStoreManager();
        $this->paymentsHelper = $paymentsHelper;
    }

    public function getSource()
    {
        if (!empty($this->source))
            return $this->source;

        $sourceId = $this->getInfo()->getAdditionalInformation("source_id");
        $this->source = \Stripe\Source::retrieve($sourceId);

        return $this->source;
    }

    public function getSpecificInformation()
    {
        $order = $this->getInfo()->getOrder();

        $source = $this->getSource();

        $fields = [
            "Bank Name" => $source->sepa_credit_transfer->bank_name,
            "IBAN" => $source->sepa_credit_transfer->iban,
            "BIC" => $source->sepa_credit_transfer->bic,
            "Reference" => $order->getIncrementId(),
            "Amount Received" => $this->paymentsHelper->getFormattedStripeAmount($source->receiver->amount_received, $source->currency, $order)
        ];

        if ($this->paymentsHelper->isAdmin())
        {
            $amountCharged = $this->paymentsHelper->convertStripeAmountToOrderAmount($source->receiver->amount_charged, $source->currency, $order);
            if ($amountCharged > $order->getGrandTotal())
            {
                $incrementId = $order->getIncrementId();
                $mode = ($source->livemode? "" : "test/");
                $fields["Overpayment"] = '<a href="https://dashboard.stripe.com/' . $mode . 'search?query=Overpayment%20for%20order%20%23' . $incrementId . '" target="_blank" rel="noopener noreferrer">View in Stripe</a>';
            }

            if (!empty($source->sepa_credit_transfer->refund_account_holder_name))
                $fields["Sender Name"] = $source->sepa_credit_transfer->refund_account_holder_name;

            if (!empty($source->sepa_credit_transfer->refund_iban))
                $fields["Sender IBAN"] = $source->sepa_credit_transfer->refund_iban;
        }

        return $fields;
    }

    public function getFormattedGrandTotal()
    {
        return $this->paymentsHelper->addCurrencySymbol($this->getInfo()->getOrder()->getGrandTotal(), $this->getInfo()->getOrder()->getOrderCurrencyCode());
    }

    public function getFormattedDueAmount()
    {
        return $this->paymentsHelper->addCurrencySymbol($this->getInfo()->getOrder()->getTotalDue(), $this->getInfo()->getOrder()->getOrderCurrencyCode());
    }
}
