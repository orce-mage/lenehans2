<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Model\Source\ApplePay;

class ButtonType implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => "default", 'label' => __('Default')],
            ['value' => "donate", 'label' => __('Donate')],
            ['value' => "buy", 'label' => __('Buy')]
        ];
    }
}
