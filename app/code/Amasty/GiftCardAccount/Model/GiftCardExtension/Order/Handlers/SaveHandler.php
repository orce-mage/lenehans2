<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Repository;
use Magento\Sales\Api\Data\OrderInterface;

class SaveHandler
{
    /**
     * @var Repository
     */
    private $repository;

    public function __construct(
        Repository $repository
    ) {
        $this->repository = $repository;
    }

    /**
     * @param OrderInterface $order
     *
     * @return OrderInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function saveAttributes(OrderInterface $order): OrderInterface
    {
        if (!$order->getExtensionAttributes() || !$order->getExtensionAttributes()->getAmGiftcardOrder()) {
            return $order;
        }
        $gCardOrder = $order->getExtensionAttributes()->getAmGiftcardOrder();

        if ($gCardOrder->getGiftCards()) {
            $this->repository->save($gCardOrder);
        } elseif ($gCardOrder->getEntityId()) {
            $this->repository->delete($gCardOrder);
        }

        return $order;
    }
}
