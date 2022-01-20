<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Controller\Checkout\Giropay;

class Response extends \Magenest\StripePayment\Controller\Checkout\Response
{
    protected function setSourceAdditionalInformation($source, $payment)
    {
        parent::setSourceAdditionalInformation($source, $payment);
        $bankName = $source->giropay->bank_name;
        $bankCode = $source->giropay->bank_code;
        $bic = $source->giropay->bic;
        $sourceAdditionalInformation = [];
        $sourceAdditionalInformation[] = [
            'label' => "Payment Method",
            'value' => "Giropay"
        ];
        if ($bankName) {
            $sourceAdditionalInformation[] = [
                'label' => "Bank name",
                'value' => $bankName
            ];
        }
        if ($bankCode) {
            $sourceAdditionalInformation[] = [
                'label' => "Bank code",
                'value' => $bankCode
            ];
        }
        if ($bic) {
            $sourceAdditionalInformation[] = [
                'label' => "BIC",
                'value' => $bic
            ];
        }
        $payment->setAdditionalInformation("stripe_source_additional_information", json_encode($sourceAdditionalInformation));
    }
}
