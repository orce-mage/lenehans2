<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 15:02
 */

namespace Magenest\StripePayment\Controller\Checkout\Ideal;

class Response extends \Magenest\StripePayment\Controller\Checkout\Response
{
    protected function setSourceAdditionalInformation($source, $payment)
    {
        parent::setSourceAdditionalInformation($source, $payment);
        $bank = $source->ideal->bank;
        $bic = $source->ideal->bic;
        $ibanLast4 = $source->ideal->iban_last4;
        $sourceAdditionalInformation = [];
        $sourceAdditionalInformation[] = [
            'label' => "Payment Method",
            'value' => "iDEAL"
        ];
        if ($bank) {
            $sourceAdditionalInformation[] = [
                'label' => "Bank",
                'value' => $bank
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
//        if($statementDescriptor){
//            $sourceAdditionalInformation[] = [
//                'label' => "statement_descriptor",
//                'value' => $statementDescriptor
//            ];
//        }
        $payment->setAdditionalInformation("stripe_source_additional_information", json_encode($sourceAdditionalInformation));
    }
}
