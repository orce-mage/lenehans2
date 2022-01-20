<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Model\Source\ApplePay;

class ButtonTheme implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'dark', 'label' => __('Dark')],
            ['value' => 'light', 'label' => __('Light')],
            ['value' => 'light-outline', 'label' => __('Light Outline')]
        ];
    }
}
