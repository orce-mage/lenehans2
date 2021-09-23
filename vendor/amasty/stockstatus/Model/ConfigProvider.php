<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model;

use Amasty\Base\Model\ConfigProviderAbstract;

class ConfigProvider extends ConfigProviderAbstract
{
    const SHOW_DEFAULT_STATUS = 'general/display_default_status';
    const IS_ICON_ONLY = 'general/icon_only';
    const THRESHOLD_FOR_RANGE = 'general/use_threshold_for_range';
    const DISPLAY_ON_CATEGORY = 'display/display_on_category';
    const DISPLAY_ON_EMAIL = 'display/display_in_email';
    const DISPLAY_ON_CART = 'display/display_in_cart';
    const DISPLAY_ON_CHECKOUT = 'display/display_on_checkout';
    const IS_CHECKOUT_ITEMS_EDITABLE = 'amasty_checkout/general/allow_edit_options';
    const IS_DISPLAY_IN_DROPDOWNS = 'configurable_products/display_in_dropdowns';
    const IS_CHANGE_STATUS = 'configurable_products/change_custom_configurable_status';
    const OUTOFSTOCK_VISIBILITY = 'configurable_products/outofstock';
    const EXPECTED_DATE_ENABLED ='expected_date/expected_date_enabled';
    const EXPECTED_DATE_FORMAT = 'expected_date/format';
    const EXPECTED_DATE_EXPIRED = 'expected_date/expired';
    const INFO_TEXT = 'info/text';
    const INFO_ENABLED = 'info/enabled';
    const INFO_CMS_PAGE = 'info/cms';

    /**
     * @var string
     */
    protected $pathPrefix = 'amstockstatus/';

    public function isShowDefaultStatus(): bool
    {
        return $this->isSetFlag(self::SHOW_DEFAULT_STATUS);
    }

    public function isThresholdForRanges(): bool
    {
        return $this->isSetFlag(self::THRESHOLD_FOR_RANGE);
    }

    public function isDisplayInDropdowns(): bool
    {
        return $this->isSetFlag(self::IS_DISPLAY_IN_DROPDOWNS);
    }

    public function isChangeStatus(): bool
    {
        return $this->isSetFlag(self::IS_CHANGE_STATUS);
    }

    public function isIconOnly(): bool
    {
        return $this->isSetFlag(self::IS_ICON_ONLY);
    }

    public function isExpectedDateEnabled(): bool
    {
        return $this->isSetFlag(self::EXPECTED_DATE_ENABLED);
    }

    public function getExpectedDateFormat(): ?string
    {
        return $this->getValue(self::EXPECTED_DATE_FORMAT);
    }

    public function isExpectedDateCanBeExpired(): bool
    {
        return $this->isSetFlag(self::EXPECTED_DATE_EXPIRED);
    }

    public function isDisplayedOnCategory(): bool
    {
        return $this->isSetFlag(self::DISPLAY_ON_CATEGORY);
    }

    public function isDisplayedOnEmail(): bool
    {
        return $this->isSetFlag(self::DISPLAY_ON_EMAIL);
    }

    public function isDisplayedOnCart(): bool
    {
        return $this->isSetFlag(self::DISPLAY_ON_CART);
    }

    public function getOutofstockVisibility(): int
    {
        return (int) $this->getValue(self::OUTOFSTOCK_VISIBILITY);
    }

    public function isInfoEnabled(): bool
    {
        return $this->isSetFlag(self::INFO_ENABLED);
    }

    public function getInfoText(): string
    {
        return (string) $this->getValue(self::INFO_TEXT);
    }

    public function getInfoCmsPageId(): int
    {
        return (int) $this->getValue(self::INFO_CMS_PAGE);
    }

    public function isDisplayOnCheckout(): bool
    {
        return $this->isSetFlag(self::DISPLAY_ON_CHECKOUT)
            && !$this->scopeConfig->isSetFlag(self::IS_CHECKOUT_ITEMS_EDITABLE);
    }
}
