<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class SofortLanguage implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => '',
                'label' => __('Auto')
            ],
            [
                'value' => 'en',
                'label' => __('English')
            ],
            [
                'value' => 'de',
                'label' => __('German'),
            ],
            [
                'value' => 'es',
                'label' => __('Spanish')
            ],
            [
                'value' => 'it',
                'label' => __('Italian')
            ],
            [
                'value' => 'fr',
                'label' => __('French')
            ],
            [
                'value' => 'nl',
                'label' => __('Dutch')
            ],
            [
                'value' => 'pl',
                'label' => __('Polish')
            ]
        ];
    }
}
