<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount\Total\Quote;

use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor;

class GiftCard extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\AllowedTotalCalculator
     */
    private $allowedTotalCalculator;

    public function __construct(
        \Amasty\GiftCardAccount\Model\GiftCardExtension\Quote\AllowedTotalCalculator $allowedTotalCalculator
    ) {
        $this->allowedTotalCalculator = $allowedTotalCalculator;
    }

    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        if (!$quote->getExtensionAttributes() || !$quote->getExtensionAttributes()->getAmGiftcardQuote()) {
            return parent::collect($quote, $shippingAssignment, $total);
        }
        $extension = $quote->getExtensionAttributes();
        $gCardQuote = $extension->getAmGiftcardQuote();

        $baseAmountLeft = $gCardQuote->getBaseGiftAmount() - $gCardQuote->getBaseGiftAmountUsed();
        $amountLeft = $gCardQuote->getGiftAmount() - $gCardQuote->getGiftAmountUsed();

        if ($baseAmountLeft >= $this->allowedTotalCalculator->getAllowedBaseSubtotal($total)) {
            $baseUsed = $this->allowedTotalCalculator->getAllowedBaseSubtotal($total);
            $used = $this->allowedTotalCalculator->getAllowedSubtotal($total);

            $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseUsed);
            $total->setGrandTotal($total->getGrandTotal() - $used);
        } else {
            $baseUsed = $baseAmountLeft;
            $used = $amountLeft;

            $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseAmountLeft);
            $total->setGrandTotal($total->getGrandTotal() - $amountLeft);
        }
        $addressCards = [];
        $usedAddressCards = [];

        if ($baseUsed) {
            $quoteCards = $gCardQuote->getGiftCards();
            $skipped = 0;
            $baseSaved = 0;
            $saved = 0;

            foreach ($quoteCards as $quoteCard) {
                $card = $quoteCard;
                if ($quoteCard[GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT] + $skipped <=
                    $gCardQuote->getBaseGiftAmountUsed()
                ) {
                    $baseThisCardUsedAmount = $thisCardUsedAmount = 0;
                } elseif ($quoteCard[GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT] + $baseSaved > $baseUsed) {
                    $baseThisCardUsedAmount = min(
                        $quoteCard[GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT],
                        $baseUsed - $baseSaved
                    );
                    $thisCardUsedAmount = min(
                        $quoteCard[GiftCardCartProcessor::GIFT_CARD_AMOUNT],
                        $used - $saved
                    );
                    $baseSaved += $baseThisCardUsedAmount;
                    $saved += $thisCardUsedAmount;
                } elseif ($quoteCard[GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT] + $skipped + $baseSaved >
                    $gCardQuote->getBaseGiftAmountUsed()
                ) {
                    $baseThisCardUsedAmount = min(
                        $quoteCard[GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT],
                        $baseUsed
                    );
                    $thisCardUsedAmount = min(
                        $quoteCard[GiftCardCartProcessor::GIFT_CARD_AMOUNT],
                        $used
                    );

                    $baseSaved += $baseThisCardUsedAmount;
                    $saved += $thisCardUsedAmount;
                } else {
                    $baseThisCardUsedAmount = $thisCardUsedAmount = 0;
                }
                $card[GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT] = round($baseThisCardUsedAmount, 4);
                $card[GiftCardCartProcessor::GIFT_CARD_AMOUNT] = round($thisCardUsedAmount, 4);
                $addressCards[] = $card;

                if ($baseThisCardUsedAmount) {
                    $usedAddressCards[] = $card;
                }
                $skipped += $quoteCard[GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT];
            }
        }

        $baseTotalUsed = $gCardQuote->getBaseGiftAmountUsed() + $baseUsed;
        $totalUsed = $gCardQuote->getGiftAmountUsed() + $used;

        $gCardQuote->setBaseGiftAmountUsed((float)$baseTotalUsed);
        $gCardQuote->setGiftAmountUsed((float)$totalUsed);

        //separated gift card data for each total is used in ProcessOrderPlace observer and fetch for multi-shipping
        $total->setBaseAmGiftCardsAmount($baseUsed);
        $total->setAmGiftCardsAmount($used);
        $total->setAmGiftCards($addressCards);
        $total->setAmUsedGiftCards($usedAddressCards);

        $extension->setAmGiftcardQuote($gCardQuote);

        if ($addressCards) {
            $extension->setAmAppliedGiftCards($addressCards);
        }
        $quote->setExtensionAttributes($extension);

        return $this;
    }

    public function fetch(\Magento\Quote\Model\Quote $quote, \Magento\Quote\Model\Quote\Address\Total $total)
    {
        //if multi-shipping then each total contains own gift cards
        return $total->getAmGiftCards() ? $this->getGiftCardsFromTotal($total) : $this->getGiftCardsFromQuote($quote);
    }

    /**
     * @param \Magento\Quote\Model\Quote $quote
     *
     * @return array|null
     */
    protected function getGiftCardsFromQuote(\Magento\Quote\Model\Quote $quote)
    {
        if (!($extension = $quote->getExtensionAttributes())) {
            return null;
        }
        $gCardQuote = $extension->getAmGiftcardQuote();

        if (!$gCardQuote) {
            return null;
        }
        $giftCards = $gCardQuote->getGiftCards();
        $applyCodes = [];

        if ($giftCards) {
            foreach ($giftCards as $card) {
                $applyCodes[] = $card[GiftCardCartProcessor::GIFT_CARD_CODE];
            }
            return [
                'code' => $this->getCode(),
                'title' => __(implode(', ', $applyCodes)),
                'value' => -$gCardQuote->getGiftAmountUsed()
            ];
        }

        return null;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Address\Total $total
     *
     * @return array|null
     */
    protected function getGiftCardsFromTotal(\Magento\Quote\Model\Quote\Address\Total $total)
    {
        $giftCards = $total->getAmGiftCards();
        $applyCodes = [];
        $value = 0;

        if ($giftCards) {
            foreach ($giftCards as $card) {
                $applyCodes[] = $card[GiftCardCartProcessor::GIFT_CARD_CODE];
                $value += $card[GiftCardCartProcessor::GIFT_CARD_AMOUNT];
            }
            return [
                'code' => $this->getCode(),
                'title' => __(implode(', ', $applyCodes)),
                'value' => -$value
            ];
        }

        return null;
    }
}
