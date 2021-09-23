<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Api;

interface CodeRepositoryInterface
{
    /**
     * @param string $id
     *
     * @return \Amasty\GiftCard\Api\Data\CodeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): \Amasty\GiftCard\Api\Data\CodeInterface;

    /**
     * @param string $code
     *
     * @return \Amasty\GiftCard\Api\Data\CodeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByCode(string $code): \Amasty\GiftCard\Api\Data\CodeInterface;

    /**
     * @param \Amasty\GiftCard\Api\Data\CodeInterface $code
     *
     * @return \Amasty\GiftCard\Api\Data\CodeInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(\Amasty\GiftCard\Api\Data\CodeInterface $code): \Amasty\GiftCard\Api\Data\CodeInterface;

    /**
     * @param \Amasty\GiftCard\Api\Data\CodeInterface $code
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\GiftCard\Api\Data\CodeInterface $code): bool;

    /**
     * @param int $id
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteById(int $id): bool;
}
