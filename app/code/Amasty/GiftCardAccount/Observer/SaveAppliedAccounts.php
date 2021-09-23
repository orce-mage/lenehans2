<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


declare(strict_types=1);

namespace Amasty\GiftCardAccount\Observer;

use Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;

class SaveAppliedAccounts implements ObserverInterface
{
    /**
     * @var GiftCardAccount\Repository
     */
    private $accountRepository;

    public function __construct(GiftCardAccount\Repository $accountRepository)
    {
        $this->accountRepository = $accountRepository;
    }

    /**
     * @param Observer $observer
     * @return void|null
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        if ($order === null) {
            $orders = $observer->getEvent()->getOrders();
            //multiple shipping checkout
            foreach ($orders as $order) {
                $this->saveAccount($order);
            }
        } else {
            $this->saveAccount($order);
        }
    }

    private function saveAccount(OrderInterface $order): void
    {
        if (!$order->getExtensionAttributes() || !$order->getExtensionAttributes()->getAmGiftcardOrder()) {
            return;
        }

        /** @var GiftCardOrderInterface $gCardOrder */
        $gCardOrder = $order->getExtensionAttributes()->getAmGiftcardOrder();
        try {
            foreach ($gCardOrder->getAppliedAccounts() as $appliedAccount) {
                $this->accountRepository->save($appliedAccount);
            }
        } catch (\Exception $e) {
            null;
        }
    }
}
