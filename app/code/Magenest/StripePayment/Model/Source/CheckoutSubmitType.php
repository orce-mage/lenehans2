<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class CheckoutSubmitType implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'auto',
                'label' => 'Auto',
            ],
            [
                'value' => 'pay',
                'label' => 'Pay',
            ],
            [
                'value' => 'book',
                'label' => 'Book',
            ],
            [
                'value' => 'donate',
                'label' => 'Donate',
            ],
        ];
    }
}
