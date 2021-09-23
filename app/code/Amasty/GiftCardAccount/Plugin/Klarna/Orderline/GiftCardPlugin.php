<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Plugin\Klarna\Orderline;

use Klarna\Core\Api\BuilderInterface;
use Klarna\Core\Model\Checkout\Orderline\Giftcard;

class GiftCardPlugin
{
    const ITEM_TYPE_DISCOUNT = 'discount';

    /**
     * @param Giftcard $subject
     * @param Giftcard $result
     * @param BuilderInterface $checkout
     *
     * @return Giftcard
     */
    public function afterCollect(Giftcard $subject, Giftcard $result, BuilderInterface $checkout)
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $checkout->getObject();
        $totals = $quote->getTotals();

        if (!is_array($totals) || !isset($totals['amasty_giftcard'])) {
            return $result;
        }
        $total = $totals['amasty_giftcard'];
        $amount = $total->getValue();

        if ($amount !== 0) {
            $value = $this->toApiFloat($amount);

            $checkout->addData(
                [
                    'amasty_giftcard_unit_price' => $value,
                    'amasty_giftcard_tax_rate' => 0,
                    'amasty_giftcard_total_amount' => $value,
                    'amasty_giftcard_tax_amount' => 0,
                    'amasty_giftcard_title' => $total->getTitle(),
                    'amasty_giftcard_reference' => $total->getCode()
                ]
            );
        }

        return $result;
    }

    /**
     * @param Giftcard $subject
     * @param Giftcard $result
     * @param BuilderInterface $checkout
     *
     * @return Giftcard
     */
    public function afterFetch(Giftcard $subject, Giftcard $result, BuilderInterface $checkout)
    {
        if ($checkout->getAmastyGiftcardTotalAmount()) {
            $checkout->addOrderLine(
                [
                    'type' => self::ITEM_TYPE_DISCOUNT,
                    'reference' => $checkout->getAmastyGiftcardReference(),
                    'name' => $checkout->getAmastyGiftcardTitle(),
                    'quantity' => 1,
                    'unit_price' => $checkout->getAmastyGiftcardUnitPrice(),
                    'tax_rate' => $checkout->getAmastyGiftcardTaxRate(),
                    'total_amount' => $checkout->getAmastyGiftcardTotalAmount(),
                    'total_tax_amount' => $checkout->getAmastyGiftcardTaxAmount(),
                ]
            );
        }

        return $result;
    }

    /**
     * @param $float
     *
     * @return float
     */
    protected function toApiFloat($float)
    {
        return round($float * 100);
    }
}
