<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Model\GiftCard;

/**
 * Storage for keys of gift card attributes
 */
class Attributes
{
    const GIFTCARD_PRICES = 'am_giftcard_prices';
    const ALLOW_OPEN_AMOUNT = 'am_allow_open_amount';
    const OPEN_AMOUNT_MIN = 'am_open_amount_min';
    const OPEN_AMOUNT_MAX = 'am_open_amount_max';
    const FEE_ENABLE = 'am_giftcard_fee_enable';
    const FEE_TYPE = 'am_giftcard_fee_type';
    const FEE_VALUE = 'am_giftcard_fee_value';
    const GIFTCARD_TYPE = 'am_giftcard_type';
    const GIFTCARD_LIFETIME = 'am_giftcard_lifetime';
    const EMAIL_TEMPLATE = 'am_email_template';
    const CODE_SET = 'am_giftcard_code_set';
    const IMAGE = 'am_giftcard_code_image';

    const ATTRIBUTE_CONFIG_VALUE = '-1';
}
