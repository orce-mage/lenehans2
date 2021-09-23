<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Api;

interface GiftCardCreditmemoRepositoryInterface
{
    /**
     * @param int $entityId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $entityId): \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface;

    /**
     * @param int $creditmemoId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByCreditmemoId(int $creditmemoId): \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface;

    /**
     * @param \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface $creditmemo
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(
        \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface $creditmemo
    ): \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface;

    /**
     * @param \Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface $creditmemo
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\GiftCardAccount\Api\Data\GiftCardCreditmemoInterface $creditmemo): bool;
}
