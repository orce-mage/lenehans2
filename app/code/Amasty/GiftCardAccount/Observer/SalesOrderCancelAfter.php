<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Observer;

use Amasty\GiftCardAccount\Model\GiftCardExtension\GiftCardExtensionResolver;
use Amasty\GiftCardAccount\Model\RefundStrategy;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Psr\Log\LoggerInterface;

class SalesOrderCancelAfter implements ObserverInterface
{
    /**
     * @var RefundStrategy
     */
    private $refundStrategy;

    /**
     * @var GiftCardExtensionResolver
     */
    private $gCardExtensionResolver;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        RefundStrategy $refundStrategy,
        GiftCardExtensionResolver $gCardExtensionResolver,
        LoggerInterface $logger
    ) {
        $this->refundStrategy = $refundStrategy;
        $this->gCardExtensionResolver = $gCardExtensionResolver;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();

        try {
            $this->refundGiftCardAccounts($order);
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    private function refundGiftCardAccounts(OrderInterface $order): void
    {
        $gCardOrder = $this->gCardExtensionResolver->resolve($order);

        if (!$gCardOrder || $gCardOrder->getBaseGiftAmount() < .0) {
            return;
        }
        $refundedAccounts = $this->refundStrategy->refundToAccount($order, $gCardOrder->getBaseGiftAmount());
        $refundedAmount = array_sum(array_column($refundedAccounts, RefundStrategy::KEY_AMOUNT));
        $refundedCodes = implode(',', array_column($refundedAccounts, RefundStrategy::KEY_CODE));

        $order->addCommentToStatusHistory(__(
            '%1 (in store\'s base currency) has been returned to Gift Card Account(s) %2 due to order cancellation',
            [
                $order->getBaseCurrency()->formatTxt($refundedAmount),
                $refundedCodes
            ]
        ))->setIsCustomerNotified(false);
    }
}
