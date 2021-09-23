<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Plugin\Quote;

use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Amasty\GiftCardAccount\Model\OptionSource\AccountStatus;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote;

class TotalsCollector
{
    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Repository
     */
    private $accountRepository;

    public function __construct(
        PriceCurrencyInterface $priceCurrency,
        Repository $accountRepository
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->accountRepository = $accountRepository;
    }

    public function beforeCollectQuoteTotals(
        \Magento\Quote\Model\Quote\TotalsCollector $subject,
        Quote $quote
    ) {
        $this->collectGiftCardAmount($quote);
    }

    public function beforeCollect(
        \Magento\Quote\Model\Quote\TotalsCollector $subject,
        Quote $quote
    ) {
        $this->collectGiftCardAmount($quote);
    }

    private function collectGiftCardAmount(Quote $quote): void
    {
        if (!$quote->getExtensionAttributes() || !$quote->getExtensionAttributes()->getAmGiftcardQuote()) {
            return;
        }
        $extension = $quote->getExtensionAttributes();
        /** @var \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface $gCardQuote */
        $gCardQuote = $extension->getAmGiftcardQuote();

        $gCardQuote->setBaseGiftAmount(.0);
        $gCardQuote->setGiftAmount(.0);
        $gCardQuote->setGiftAmountUsed(.0);
        $gCardQuote->setBaseGiftAmountUsed(.0);

        $baseAmount = 0;
        $amount = 0;
        $giftCards = $gCardQuote->getGiftCards();

        foreach ($giftCards as $key => &$card) {
            try {
                $account = $this->accountRepository->getById((int)$card[GiftCardCartProcessor::GIFT_CARD_ID]);

                if ($account->getCurrentValue() == 0 || $account->getStatus() == AccountStatus::STATUS_EXPIRED) {
                    unset($giftCards[$key]);
                } elseif ($account->getCurrentValue() != $card[GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT]) {
                    $card[GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT] = $account->getCurrentValue();
                } else {
                    $card[GiftCardCartProcessor::GIFT_CARD_AMOUNT] = $this->priceCurrency->round(
                        $this->priceCurrency->convert(
                            $card[GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT],
                            $quote->getStore()
                        )
                    );
                    $baseAmount += $card[GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT];
                    $amount += $card[GiftCardCartProcessor::GIFT_CARD_AMOUNT];
                }
            } catch (NoSuchEntityException $e) {
                unset($giftCards[$key]);
            }
        }
        $gCardQuote->setGiftCards($giftCards);

        $gCardQuote->setBaseGiftAmount((float)$baseAmount);
        $gCardQuote->setGiftAmount((float)$amount);

        $extension->setAmGiftcardQuote($gCardQuote);
        $quote->setExtensionAttributes($extension);
    }
}
