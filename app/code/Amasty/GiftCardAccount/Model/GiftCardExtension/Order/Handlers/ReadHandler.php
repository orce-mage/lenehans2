<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Repository;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;

class ReadHandler
{
    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var OrderExtensionFactory
     */
    private $orderExtensionFactory;

    public function __construct(
        Repository $repository,
        OrderExtensionFactory $orderExtensionFactory
    ) {
        $this->repository = $repository;
        $this->orderExtensionFactory = $orderExtensionFactory;
    }

    /**
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function loadAttributes(OrderInterface $order): OrderInterface
    {
        $extension = $order->getExtensionAttributes();

        if ($extension === null) {
            $extension = $this->orderExtensionFactory->create();
        } elseif ($order->getExtensionAttributes()->getAmGiftcardOrder() !== null) {
            return $order;
        }
        $orderId = $order->getId();

        try {
            $giftCardOrder = $this->repository->getByOrderId((int)$orderId);
        } catch (NoSuchEntityException $e) {
            $giftCardOrder = $this->repository->getEmptyOrderModel();
            $giftCardOrder->setOrderId((int)$orderId);
        }
        $extension->setAmGiftcardOrder($giftCardOrder);
        $order->setExtensionAttributes($extension);

        return $order;
    }
}
