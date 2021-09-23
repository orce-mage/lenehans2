<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Plugin\Klarna\Orderline\Items;

use Klarna\Base\Model\Api\Parameter;
use Klarna\Base\Model\Checkout\Orderline\DataHolder;
use Klarna\Base\Model\Checkout\Orderline\Items\Giftcard;
use Magento\Quote\Api\Data\CartInterface;

class GiftCardPlugin
{
    /**
     * @var array
     */
    private $data;

    public function afterCollectPrePurchase(
        Giftcard $subject,
        Giftcard $result,
        Parameter $parameter,
        DataHolder $dataHolder,
        CartInterface $quote
    ): Giftcard {
        $this->collect($dataHolder, $quote);

        return $subject;
    }

    public function afterFetch(Giftcard $subject, Giftcard $result, Parameter $checkout): Giftcard
    {
        if (isset($this->data['total_amount'])) {
            $checkout->addOrderLine(
                [
                    'type' => \Klarna\Base\Model\Checkout\Orderline\Items\Giftcard::ITEM_TYPE_GIFTCARD,
                    'reference' => $this->data['reference'] ?? '',
                    'name' => $this->data['title'] ?? '',
                    'quantity' => 1,
                    'unit_price' => $this->data['unit_price'] ?? 0,
                    'tax_rate' => 0,
                    'total_amount' => $this->data['total_amount'] ?? 0,
                    'total_tax_amount' => 0,
                ]
            );
        }

        return $subject;
    }

    protected function toApiFloat(float $float): float
    {
        return round($float * 100);
    }

    private function collect(DataHolder $dataHolder, CartInterface $quote): void
    {
        $totals = $dataHolder->getTotals();

        if (!is_array($totals) || !isset($totals['amasty_giftcard'])) {
            return;
        }
        $total = $totals['amasty_giftcard'];

        if ($total->getValue() !== 0
            && $quote->getExtensionAttributes()
            && $quote->getExtensionAttributes()->getAmGiftcardQuote()
        ) {
            $gCardQuote = $quote->getExtensionAttributes()->getAmGiftcardQuote();
            $value = -1 * $this->toApiFloat($gCardQuote->getGiftAmountUsed());

            $this->data = [
                'unit_price' => $value,
                'total_amount' => $value,
                'title' => $total->getTitle(),
                'reference' => $total->getCode()
            ];
        }
    }
}
