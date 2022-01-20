<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class Locale implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'auto',
                'label' => __('Auto'),
            ],
            [
                'value' => 'en',
                'label' => __('English')
            ],
            [
                'value' => 'zh',
                'label' => __('Simplified Chinese')
            ],
            [
                'value' => 'da',
                'label' => __('Danish')
            ],
            [
                'value' => 'nl',
                'label' => __('Dutch')
            ],
            [
                'value' => 'fi',
                'label' => __('Finnish')
            ],
            [
                'value' => 'fr',
                'label' => __('French')
            ],
            [
                'value' => 'de',
                'label' => __('German')
            ],
            [
                'value' => 'it',
                'label' => __('Italian')
            ],
            [
                'value' => 'ja',
                'label' => __('Japanese')
            ],
            [
                'value' => 'no',
                'label' => __('Norwegian')
            ],
            [
                'value' => 'es',
                'label' => __('Spanish')
            ],
            [
                'value' => 'sv',
                'label' => __('Swedish')
            ]
        ];
    }
}
