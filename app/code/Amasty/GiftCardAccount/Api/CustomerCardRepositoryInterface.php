<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Api;

interface CustomerCardRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\GiftCardAccount\Api\Data\CustomerCardInterface $customerCard
     *
     * @return \Amasty\GiftCardAccount\Api\Data\CustomerCardInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(
        \Amasty\GiftCardAccount\Api\Data\CustomerCardInterface $customerCard
    ): \Amasty\GiftCardAccount\Api\Data\CustomerCardInterface;

    /**
     * Get by id
     *
     * @param int $customerCardId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\CustomerCardInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $customerCardId): \Amasty\GiftCardAccount\Api\Data\CustomerCardInterface;

    /**
     * @param int $accountId
     * @param int $customerId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\CustomerCardInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByAccountAndCustomerId(
        int $accountId,
        int $customerId
    ): \Amasty\GiftCardAccount\Api\Data\CustomerCardInterface;

    /**
     * Delete
     *
     * @param \Amasty\GiftCardAccount\Api\Data\CustomerCardInterface $customerCard
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\GiftCardAccount\Api\Data\CustomerCardInterface $customerCard): bool;

    /**
     * Delete by id
     *
     * @param int $customerCardId
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $customerCardId): bool;

    /**
     * @param int $accountId
     *
     * @return bool false if account wasn't claimed
     */
    public function hasCardForAccountId(int $accountId): bool;
}
