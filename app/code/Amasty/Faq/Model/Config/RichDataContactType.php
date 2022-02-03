<?php

namespace Amasty\Faq\Model\Config;

use Magento\Framework\Option\ArrayInterface;

class RichDataContactType implements ArrayInterface
{
    const CUSTOMER_SERVICE = 'customer service';
    const TECHNICAL_SUPPORT = 'technical support';
    const BILLING_SUPPORT = 'billing support';
    const BILL_PAYMENT = 'bill payment';
    const SALES = 'sales';
    const RESERVATIONS = 'reservations';
    const CREDIT_CARD_SUPPORT = 'credit card support';
    const EMERGENCY = 'emergency';
    const BAGGAGE_TRACKING = 'baggage tracking';
    const ROADSIDE_ASSISTANCE = 'roadside assistance';
    const PACKAGE_TRACKING = 'package tracking';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::CUSTOMER_SERVICE, 'label' => __('Customer Service')],
            ['value' => self::TECHNICAL_SUPPORT, 'label' => __('Technical Support')],
            ['value' => self::BILLING_SUPPORT, 'label' => __('Billing Support')],
            ['value' => self::BILL_PAYMENT, 'label' => __('Bill Payment')],
            ['value' => self::SALES, 'label' => __('Sales')],
            ['value' => self::RESERVATIONS, 'label' => __('Reservations')],
            ['value' => self::CREDIT_CARD_SUPPORT, 'label' => __('Credit Card Support')],
            ['value' => self::EMERGENCY, 'label' => __('Emergency')],
            ['value' => self::BAGGAGE_TRACKING, 'label' => __('Baggage Tracking')],
            ['value' => self::ROADSIDE_ASSISTANCE, 'label' => __('Roadside Assistance')],
            ['value' => self::PACKAGE_TRACKING, 'label' => __('Package Tracking')]
        ];
    }
}
