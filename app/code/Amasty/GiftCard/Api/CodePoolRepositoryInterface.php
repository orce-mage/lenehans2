<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Api;

interface CodePoolRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return \Amasty\GiftCard\Api\Data\CodePoolInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): \Amasty\GiftCard\Api\Data\CodePoolInterface;

    /**
     * @param int $id
     *
     * @return \Amasty\GiftCard\Api\Data\CodePoolRuleInterface|null
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getRuleByCodePoolId(int $id);

    /**
     * @param \Amasty\GiftCard\Api\Data\CodePoolInterface $codePool
     *
     * @return \Amasty\GiftCard\Api\Data\CodePoolInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(
        \Amasty\GiftCard\Api\Data\CodePoolInterface $codePool
    ): \Amasty\GiftCard\Api\Data\CodePoolInterface;

    /**
     * @param \Amasty\GiftCard\Api\Data\CodePoolInterface $codePool
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\GiftCard\Api\Data\CodePoolInterface $codePool): bool;

    /**
     * @param int $id
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $id): bool;

    /**
     * @return \Amasty\GiftCard\Api\Data\CodePoolInterface[]
     */
    public function getList(): array;
}
