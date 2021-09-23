<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Plugin\Sales;

use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\ReadHandler;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\Handlers\SaveHandler;
use Magento\Framework\Api\SearchResultsInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderRepositoryPlugin
{
    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @var SaveHandler
     */
    private $saveHandler;

    /**
     * @var \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface|null
     */
    private $orderExtension = null;

    public function __construct(
        ReadHandler $readHandler,
        SaveHandler $saveHandler
    ) {
        $this->readHandler = $readHandler;
        $this->saveHandler = $saveHandler;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     */
    public function afterGet(OrderRepositoryInterface $subject, OrderInterface $order): OrderInterface
    {
        $this->readHandler->loadAttributes($order);

        return $order;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param SearchResultsInterface $searchResult
     *
     * @return SearchResultsInterface
     */
    public function afterGetList(
        OrderRepositoryInterface $subject,
        SearchResultsInterface $searchResult
    ): SearchResultsInterface {
        foreach ($searchResult->getItems() as $order) {
            $this->readHandler->loadAttributes($order);
        }

        return $searchResult;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     */
    public function beforeSave(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ) {
        if ($order->getExtensionAttributes() && $order->getExtensionAttributes()->getAmGiftcardOrder()) {
            $this->orderExtension = $order->getExtensionAttributes()->getAmGiftcardOrder();
        }
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $order
     *
     * @return OrderInterface
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function afterSave(
        OrderRepositoryInterface $subject,
        OrderInterface $order
    ): OrderInterface {
        if ($gCardOrder = $this->orderExtension) {
            $extension = $order->getExtensionAttributes();
            $gCardOrder->setOrderId((int)$order->getId());
            $extension->setAmGiftcardOrder($gCardOrder);
            $order->setExtensionAttributes($extension);
            $this->saveHandler->saveAttributes($order);
        }

        return $order;
    }
}
