<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Observer;

use Amasty\GiftCardAccount\Api\GiftCardOrderRepositoryInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor;
use Amasty\GiftCardAccount\Model\GiftCardAccount\Repository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

class ProcessOrderPlace implements ObserverInterface
{
    /**
     * @var Repository
     */
    private $accountRepository;

    /**
     * @var GiftCardOrderRepositoryInterface
     */
    private $gCardOrderRepository;

    public function __construct(
        Repository $accountRepository,
        GiftCardOrderRepositoryInterface $gCardOrderRepository
    ) {
        $this->accountRepository = $accountRepository;
        $this->gCardOrderRepository = $gCardOrderRepository;
    }

    public function execute(Observer $observer)
    {
        /** @var OrderInterface $order */
        $order = $observer->getEvent()->getOrder();
        /** @var \Magento\Quote\Model\Quote\Address $address */
        $address = $observer->getEvent()->getAddress();

        if (!$address) {
            // Single address checkout.
            /** @var \Magento\Quote\Model\Quote $quote */
            $quote = $observer->getEvent()->getQuote();
            $address = $quote->isVirtual() ? $quote->getBillingAddress() : $quote->getShippingAddress();
        }
        $extension = $order->getExtensionAttributes();

        /** @var \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface $gCardOrder */
        $gCardOrder = $this->gCardOrderRepository->getEmptyOrderModel();
        $gCardOrder->setGiftCards($address->getAmGiftCards() ?? []);
        $gCardOrder->setGiftAmount((float)$address->getAmGiftCardsAmount());
        $gCardOrder->setBaseGiftAmount((float)$address->getBaseAmGiftCardsAmount());
        $amount = $baseAmount = 0;
        $appliedAccounts = [];

        foreach ($gCardOrder->getGiftCards() as $card) {
            try {
                $account = $this->accountRepository->getById((int)$card[GiftCardCartProcessor::GIFT_CARD_ID]);
                $account->setCurrentValue(
                    (float)($account->getCurrentValue() - $card[GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT])
                );
                $appliedAccounts[] = $account;
                $amount += $card[GiftCardCartProcessor::GIFT_CARD_AMOUNT];
                $baseAmount += $card[GiftCardCartProcessor::GIFT_CARD_BASE_AMOUNT];
            } catch (\Exception $e) {
                null;
            }
        }
        $gCardOrder->setGiftAmount((float)$amount);
        $gCardOrder->setBaseGiftAmount((float)$baseAmount);
        $gCardOrder->setAppliedAccounts($appliedAccounts);

        $extension->setAmGiftcardOrder($gCardOrder);
        $order->setExtensionAttributes($extension);
    }
}
