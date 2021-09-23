<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\GiftCardAccount\ResourceModel;

use Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;
use Amasty\GiftCardAccount\Model\GiftCardAccount\GiftCardCartProcessor;
use Amasty\GiftCardAccount\Model\GiftCardExtension\Order\ResourceModel\CollectionFactory as GCardOrderCollectionFactory;

class OrderHistoryCollectionGenerator
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var \Amasty\GiftCardAccount\Model\GiftCardExtension\Order\ResourceModel\Collection
     */
    private $gCardOrderCollectionFactory;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory $orderCollectionFactory,
        GCardOrderCollectionFactory $gCardOrderCollectionFactory
    ) {
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->gCardOrderCollectionFactory = $gCardOrderCollectionFactory;
    }

    /**
     * @param int $accountId
     *
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrderCollectionByAccountId(
        int $accountId
    ): \Magento\Sales\Model\ResourceModel\Order\Grid\Collection {
        $orderIds = $this->getOrderIds($accountId);
        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', ['in' => $orderIds]);

        return $collection;
    }

    /**
     * @param int $accountId
     *
     * @return array
     */
    protected function getOrderIds(int $accountId): array
    {
        if (!$accountId) {
            return [];
        }
        $orderIds = [];
        /** @var \Amasty\GiftCardAccount\Model\GiftCardExtension\Order\ResourceModel\Collection $gCardOrderCollection */
        $gCardOrderCollection = $this->gCardOrderCollectionFactory->create();
        $gCardOrderCollection->addFieldToSelect([GiftCardOrderInterface::ORDER_ID, GiftCardOrderInterface::GIFT_CARDS]);

        /** @var \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface $orderCard */
        foreach ($gCardOrderCollection->getItems() as $orderCard) {
            foreach ($orderCard->getGiftCards() as $card) {
                if ($card[GiftCardCartProcessor::GIFT_CARD_ID] == $accountId) {
                    $orderIds[] = $orderCard->getOrderId();
                    break;
                }
            }
        }

        return $orderIds;
    }
}
