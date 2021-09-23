<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Observer;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\ReadHandler;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Model\Order;

class SalesOrderLoadAfter implements ObserverInterface
{
    /**
     * @var ReadHandler
     */
    private $orderReadHandler;

    public function __construct(
        ReadHandler $orderReadHandler
    ) {
        $this->orderReadHandler = $orderReadHandler;
    }

    public function execute(Observer $observer)
    {
        /** @var Order $order */
        $order = $observer->getEvent()->getOrder();

        if ($order->canUnhold()) {
            return;
        }

        if ($order->isCanceled() || $order->getState() === Order::STATE_CLOSED) {
            return;
        }
        $this->orderReadHandler->loadAttributes($order);
        $gCardOrder = $order->getExtensionAttributes()->getAmGiftcardOrder();

        if ($gCardOrder->getBaseInvoiceGiftAmount() - $gCardOrder->getBaseRefundGiftAmount() >= 0.0001) {
            $order->setForcedCanCreditmemo(true);
        }
    }
}
