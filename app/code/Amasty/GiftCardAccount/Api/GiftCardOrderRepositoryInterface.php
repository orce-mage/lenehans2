<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Api;

interface GiftCardOrderRepositoryInterface
{
    /**
     * @param int $entityId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $entityId): \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;

    /**
     * @param int $orderId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByOrderId(int $orderId): \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;

    /**
     * @param \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface $order
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(
        \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface $order
    ): \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface;

    /**
     * @param \Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface $order
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\GiftCardAccount\Api\Data\GiftCardOrderInterface $order): bool;
}
