<?php
/**
 * Copyright © 2019 Magenest. All rights reserved.
 * See COPYING.txt for license details.
 *
 * Stripe extension
 * NOTICE OF LICENSE
 *
 * @category Magenest
 * @package Magenest_Stripe
 */

namespace Magenest\StripePayment\Model\Source;


use Magento\Framework\Option\ArrayInterface;

class IntentsThreeDSecure implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => '1',
                'label' => __('Affected by Settings of Stripe Payment')
            ],
        ];
    }
}