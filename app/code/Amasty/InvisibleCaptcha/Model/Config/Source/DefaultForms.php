<?php

declare(strict_types=1);

namespace Amasty\InvisibleCaptcha\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class DefaultForms implements OptionSourceInterface
{
    const CUSTOMER_CREATE = 'customer/account/createpost';
    const CUSTOMER_LOGIN = 'customer/account/loginPost';
    const CUSTOMER_FORGOTPASSWORD = 'customer/account/forgotpasswordpost';
    const CUSTOMER_RESETPASSWORD = 'customer/account/resetpasswordpost';
    const NEWSLETTER_SUBSCRIBE = 'newsletter/subscriber/new';
    const PRODUCT_REVIEW = 'review/product/post';
    const CONTACT_US = 'contact/index/post';
    const CHECKOUT_PAYMENTS = 'checkout_payment_captcha';

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        return [
            [
                'value' => self::CUSTOMER_CREATE,
                'label' => __('Customer Create Account')
            ],
            [
                'value' => self::CUSTOMER_LOGIN,
                'label' => __('Customer Login')
            ],
            [
                'value' => self::NEWSLETTER_SUBSCRIBE,
                'label' => __('News Letter Subscription')
            ],
            [
                'value' => self::CONTACT_US,
                'label' => __('Contact Us')
            ],
            [
                'value' => self::CUSTOMER_FORGOTPASSWORD,
                'label' => __('Customer Forgot Password')
            ],
            [
                'value' => self::PRODUCT_REVIEW,
                'label' => __('Product Review')
            ],
            [
                'value' => self::CUSTOMER_RESETPASSWORD,
                'label' => __('Change Password')
            ],
            [
                'value' => self::CHECKOUT_PAYMENTS,
                'label' => __('Checkout Payments')
            ],
        ];
    }
}
