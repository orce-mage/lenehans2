<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 15:02
 */

namespace Magenest\StripePayment\Controller\Checkout\Bancontact;

class Response extends \Magenest\StripePayment\Controller\Checkout\Response
{
    protected function setSourceAdditionalInformation($source, $payment)
    {
        parent::setSourceAdditionalInformation($source, $payment);
        $bankName = $source->bancontact->bank_name;
        $bankCode = $source->bancontact->bank_code;
        $bic = $source->bancontact->bic;
        $ibanLast4 = $source->bancontact->iban_last4;
        $sourceAdditionalInformation = [];
        $sourceAdditionalInformation[] = [
            'label' => "Payment Method",
            'value' => "bancontact"
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
        if ($ibanLast4) {
            $sourceAdditionalInformation[] = [
                'label' => "IBAN last4",
                'value' => $ibanLast4
            ];
        }
        $payment->setAdditionalInformation("stripe_source_additional_information", json_encode($sourceAdditionalInformation));
    }
}
