<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Api;

/**
 * @api
 */
interface GiftCardAccountRepositoryInterface
{
    /**
     * Get account by id
     *
     * @param int $id
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

    /**
     * Get account by code
     *
     * @param string $code
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByCode(string $code): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

    /**
     * Save account
     *
     * @param \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface $account
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(
        \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface $account
    ): \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface;

    /**
     * Delete account
     *
     * @param \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface $account
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface $account): bool;

    /**
     * Delete account by id
     *
     * @param int $id
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $id): bool;

    /**
     * @param int $customerId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAccountsByCustomerId(int $customerId);

    /**
     * Get all accounts
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardAccountInterface[]
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(): array;
}
