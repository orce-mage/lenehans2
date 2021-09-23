<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model;

class ConfigProvider extends \Amasty\Base\Model\ConfigProviderAbstract
{
    protected $pathPrefix = 'amgiftcard/';

    /**#@+
     * Constants defined for xpath of system configuration
     */
    const XPATH_ENABLED = 'general/active';
    const ALLOWED_PRODUCT_TYPES = 'general/allowed_product_types';
    const SHIPPING_PAID_ALLOWED = 'general/allow_to_paid_for_shipping';
    const TAX_PAID_ALLOWED = 'general/allow_to_paid_for_tax';
    const EXTRA_FEE_PAID_ALLOWED = 'general/allow_to_paid_for_amasty_extra_fee';

    const GIFT_CARD_FIELDS = 'display_options/fields';
    const SHOW_OPTIONS_IN_CART_CHECKOUT = 'display_options/show_options_in_cart_checkout';
    const GIFT_CARD_TIMEZONE = 'display_options/gift_card_timezone';
    const ALLOW_USER_IMAGES = 'display_options/allow_user_images';
    const IMAGE_UPLOAD_TOOLTIP = 'display_options/image_upload_tooltip';

    const LIFETIME = 'card/lifetime';
    const ALLOW_USE_THEMSELVES = 'card/allow_use_themselves';
    const NOTIFY_EXPIRES_DATE = 'card/notify_expires_date';
    const NOTIFY_EXPIRES_DATE_DAYS = 'card/notify_expires_date_days';

    const EMAIL_SENDER = 'email/email_sender';
    const EMAIL_TEMPLATE = 'email/email_template';
    const EMAIL_RECIPIENTS = 'email/email_recipients';
    const EMAIL_EXPIRATION_TEMPLATE = 'email/email_expiration_template';
    const SEND_CONFIRMATION_TO_SENDER = 'email/send_confirmation_to_sender';
    const EMAIL_SENDER_CONFIRMATION_TEMPLATE = 'email/email_sender_confirmation_template';
    const ATTACH_PDF_GIFT_CARD = 'email/attach_pdf_gift_card';
    /**#@-*/

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isEnabled($storeId = null): bool
    {
        return $this->isSetFlag(self::XPATH_ENABLED, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return array
     */
    public function getAllowedProductTypes($storeId = null): array
    {
        $allowedTypes = [];

        if ($value = $this->getValue(self::ALLOWED_PRODUCT_TYPES, $storeId)) {
            $allowedTypes = explode(',', $value);
        }

        return $allowedTypes;
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isShippingPaidAllowed($storeId = null): bool
    {
        return $this->isSetFlag(self::SHIPPING_PAID_ALLOWED, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isTaxPaidAllowed($storeId = null): bool
    {
        return $this->isSetFlag(self::TAX_PAID_ALLOWED, $storeId);
    }

    public function isExtraFeePaidAllowed($storeId = null): bool
    {
        return $this->isSetFlag(self::EXTRA_FEE_PAID_ALLOWED, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return array
     */
    public function getGiftCardFields($storeId = null): array
    {
        $fieldsArray = [];

        if ($fields = $this->getValue(self::GIFT_CARD_FIELDS, $storeId)) {
            $fieldsArray = explode(',', $fields);
        }

        return $fieldsArray;
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isShowOptionsInCartAndCheckout($storeId = null): bool
    {
        return $this->isSetFlag(self::SHOW_OPTIONS_IN_CART_CHECKOUT, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return array
     */
    public function getGiftCardTimezone($storeId = null): array
    {
        $timezonesArray = [];

        if ($timezones = $this->getValue(self::GIFT_CARD_TIMEZONE, $storeId)) {
            $timezonesArray = explode(',', $timezones);
        }

        return $timezonesArray;
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isAllowUserImages($storeId = null): bool
    {
        return $this->isSetFlag(self::ALLOW_USER_IMAGES, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string|null
     */
    public function getImageUploadTooltip($storeId = null): string
    {
        return (string)$this->getValue(self::IMAGE_UPLOAD_TOOLTIP, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getLifetime($storeId = null): int
    {
        return (int)$this->getValue(self::LIFETIME, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isAllowUseThemselves($storeId = null): bool
    {
        return $this->isSetFlag(self::ALLOW_USE_THEMSELVES, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isNotifyExpiresDate($storeId = null): bool
    {
        return $this->isSetFlag(self::NOTIFY_EXPIRES_DATE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return int
     */
    public function getNotifyExpiresDateDays($storeId = null): int
    {
        return (int)$this->getValue(self::NOTIFY_EXPIRES_DATE_DAYS, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getEmailSender($storeId = null): string
    {
        return (string)$this->getValue(self::EMAIL_SENDER, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getEmailTemplate($storeId = null): string
    {
        return (string)$this->getValue(self::EMAIL_TEMPLATE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return array
     */
    public function getEmailRecipients($storeId = null): array
    {
        $recipientsArray = [];

        if ($recipients = $this->getValue(self::EMAIL_RECIPIENTS, $storeId)) {
            $recipientsArray = array_filter(array_map('trim', preg_split('/\n|\r\n?/', $recipients)));
        }

        return $recipientsArray;
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getEmailExpirationTemplate($storeId = null): string
    {
        return (string)$this->getValue(self::EMAIL_EXPIRATION_TEMPLATE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isSendConfirmationToSender($storeId = null): bool
    {
        return $this->isSetFlag(self::SEND_CONFIRMATION_TO_SENDER, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return string
     */
    public function getSenderConfirmationTemplate($storeId = null): string
    {
        return (string)$this->getValue(self::EMAIL_SENDER_CONFIRMATION_TEMPLATE, $storeId);
    }

    /**
     * @param int|null $storeId
     *
     * @return bool
     */
    public function isAttachPdfGiftCard($storeId = null): bool
    {
        return $this->isSetFlag(self::ATTACH_PDF_GIFT_CARD, $storeId);
    }
}
