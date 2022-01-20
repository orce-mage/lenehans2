<?php
/**
 * Created by Magenest JSC.
 * Author: Jacob
 * Date: 10/01/2019
 * Time: 9:41
 */

namespace Magenest\StripePayment\Model\Source;

use Magento\Framework\Option\ArrayInterface;

class ThreedSecureAction implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'recommended',
                'label' => __('3D Secure is recommended')
            ],
            [
                'value' => 'optional',
                'label' => __('3D Secure is optional')
            ],
        ];
    }
}
