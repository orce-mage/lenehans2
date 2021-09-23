<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Quote;

use Magento\Framework\DataObject;

class AllowedTotalCalculator
{
    /**
     * @var bool
     */
    private $isTaxAllowed = false;

    /**
     * @var bool
     */
    private $isShippingAllowed = false;

    /**
     * @var bool
     */
    private $isExtraFeeAllowed = false;

    public function __construct(
        \Amasty\GiftCard\Model\ConfigProvider $configProvider
    ) {
        $this->isShippingAllowed = $configProvider->isShippingPaidAllowed();
        $this->isTaxAllowed = $configProvider->isTaxPaidAllowed();
        $this->isExtraFeeAllowed = $configProvider->isExtraFeePaidAllowed();
    }

    /**
     * @param DataObject $from
     *
     * @return float|string
     */
    public function getAllowedSubtotal(DataObject $from)
    {
        $extraFeeAmount = $this->isExtraFeeAllowed ? (float)$from->getAmastyExtrafeeAmount() : 0.0;

        if (($this->isTaxAllowed) && ($this->isShippingAllowed)) {
            return $from->getSubtotal()
                + $from->getTaxAmount()
                + $from->getDiscountAmount()
                + $from->getShippingAmount()
                + $from->getDiscountTaxCompensationAmount()
                + $extraFeeAmount;
        } elseif ((!$this->isTaxAllowed) && ($this->isShippingAllowed)) {
            return $from->getSubtotalWithDiscount()
                + $from->getDiscountTaxCompensationAmount()
                + $from->getShippingAmount()
                + $from->getShippingDiscountTaxCompensationAmount()
                + $extraFeeAmount;
        } elseif (($this->isTaxAllowed) && (!$this->isShippingAllowed)) {
            return $from->getSubtotalWithDiscount()
                + $from->getDiscountTaxCompensationAmount()
                + $from->getShippingDiscountAmount()
                + $from->getTaxAmount()
                + $from->getShippingDiscountTaxCompensationAmount()
                + $extraFeeAmount;
        } else {
            return $from->getSubtotalWithDiscount()
                + $from->getDiscountTaxCompensationAmount()
                + $from->getShippingDiscountAmount()
                + $extraFeeAmount;
        }
    }

    /**
     * @param DataObject $from
     *
     * @return float|string
     */
    public function getAllowedBaseSubtotal(DataObject $from)
    {
        $extraFeeAmount = $this->isExtraFeeAllowed ? (float)$from->getAmastyExtrafeeAmount() : 0.0;

        if (($this->isTaxAllowed) && ($this->isShippingAllowed)) {
            return $from->getBaseSubtotal()
                + $from->getBaseTaxAmount()
                + $from->getBaseDiscountAmount()
                + $from->getBaseShippingAmount()
                + $from->getBaseDiscountTaxCompensationAmount()
                + $extraFeeAmount;
        } elseif ((!$this->isTaxAllowed) && ($this->isShippingAllowed)) {
            return $from->getBaseSubtotalWithDiscount()
                + $from->getBaseDiscountTaxCompensationAmount()
                + $from->getBaseShippingAmount()
                + $from->getBaseShippingDiscountTaxCompensationAmount()
                + $extraFeeAmount;
        } elseif (($this->isTaxAllowed) && (!$this->isShippingAllowed)) {
            return $from->getBaseSubtotalWithDiscount()
                + $from->getBaseDiscountTaxCompensationAmount()
                + $from->getBaseShippingDiscountAmount()
                + $from->getBaseTaxAmount()
                + $from->getBaseShippingDiscountTaxCompensationAmount()
                + $extraFeeAmount;
        } else {
            return $from->getBaseSubtotalWithDiscount()
                + $from->getBaseDiscountTaxCompensationAmount()
                + $from->getBaseShippingDiscountAmount()
                + $extraFeeAmount;
        }
    }
}
