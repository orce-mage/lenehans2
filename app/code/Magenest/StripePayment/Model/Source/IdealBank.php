<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class IdealBank implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => __("Please Select"),
            ],
            [
                'value' => 'abn_amro',
                'label' => 'ABN AMRO',
            ],
            [
                'value' => 'asn_bank',
                'label' => 'ASN Bank'
            ],
            [
                'value' => 'bunq',
                'label' => 'Bunq'
            ],
            [
                'value' => 'ing',
                'label' => 'ING'
            ],
            [
                'value' => 'knab',
                'label' => 'Knab'
            ],
            [
                'value' => 'moneyou',
                'label' => 'Moneyou'
            ],
            [
                'value' => 'rabobank',
                'label' => 'Rabobank'
            ],
            [
                'value' => 'regiobank',
                'label' => 'RegioBank'
            ],
            [
                'value' => 'sns_bank',
                'label' => 'SNS Bank (De Volksbank)	'
            ],
            [
                'value' => 'triodos_bank',
                'label' => 'Triodos Bank'
            ],
            [
                'value' => 'van_lanschot',
                'label' => 'Van Lanschot'
            ]
        ];
    }
}
