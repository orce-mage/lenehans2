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

class CountrySpecified implements ArrayInterface
{
    public function toOptionArray()
    {
        return [
            [
                'value' => 'EA',
                'label' => __('UNITED ARAB EMIRATES'),
            ],
            [
                'value' => 'AT',
                'label' => __('AUSTRIA')
            ],
            [
                'value' => 'AU',
                'label' => __('AUSTRALIA')
            ],
            [
                'value' => 'BE',
                'label' => __('BELGIUM')
            ],
            [
                'value' => 'BR',
                'label' => __('BRAZIL')
            ],
            [
                'value' => 'CA',
                'label' => __('CANADA')
            ],
            [
                'value' => 'CH',
                'label' => __('SWITZERLAND')
            ],
            [
                'value' => 'CZ',
                'label' => __('CZECH REPUBLIC')
            ],
            [
                'value' => 'DE',
                'label' => __('GERMANY')
            ],
            [
                'value' => 'DK',
                'label' => __('DENMARK')
            ],
            [
                'value' => 'EE',
                'label' => __('ESTONIA')
            ],
            [
                'value' => 'ES',
                'label' => __('SPAIN')
            ],
            [
                'value' => 'FI',
                'label' => __('FINLAND')
            ],
            [
                'value' => 'FR',
                'label' => __('FRANCE')
            ],
            [
                'value' => 'GB',
                'label' => __('UNITED KINGDOM')
            ],
            [
                'value' => 'GR',
                'label' => __('GREECE')
            ],
            [
                'value' => 'HK',
                'label' => __('HONG KONG')
            ],
            [
                'value' => 'IE',
                'label' => __('IRELAND')
            ],
            [
                'value' => 'IN',
                'label' => __('INDIA')
            ],
            [
                'value' => 'IT',
                'label' => __('ITALY')
            ],
            [
                'value' => 'JP',
                'label' => __('JAPAN')
            ],
            [
                'value' => 'LT',
                'label' => __('LITHUANIA')
            ],
            [
                'value' => 'LU',
                'label' => __('LUXEMBOURG')
            ],
            [
                'value' => 'LV',
                'label' => __('LATVIA')
            ],
            [
                'value' => 'MX',
                'label' => __('MEXICO')
            ],
            [
                'value' => 'MY',
                'label' => __('MALAYSIA')
            ],
            [
                'value' => 'NL',
                'label' => __('NETHERLANDS')
            ],
            [
                'value' => 'NO',
                'label' => __('NORWAY')
            ],
            [
                'value' => 'NZ',
                'label' => __('NEW ZEALAND')
            ],
            [
                'value' => 'PH',
                'label' => __('PHILIPPINES')
            ],
            [
                'value' => 'PL',
                'label' => __('POLAND')
            ],
            [
                'value' => 'PT',
                'label' => __('PORTUGAL')
            ],
            [
                'value' => 'RO',
                'label' => __('ROMANIA')
            ],
            [
                'value' => 'SE',
                'label' => __('SWEDEN')
            ],
            [
                'value' => 'SG',
                'label' => __('SINGAPORE')
            ],
            [
                'value' => 'SI',
                'label' => __('SLOVENIA')
            ],
            [
                'value' => 'SK',
                'label' => __('SLOVAKIA')
            ],
            [
                'value' => 'US',
                'label' => __('UNITED STATES')
            ]
        ];
    }
}