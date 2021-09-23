<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Api;

/**
 * @api
 */
interface ImageRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return \Amasty\GiftCard\Api\Data\ImageInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): \Amasty\GiftCard\Api\Data\ImageInterface;

    /**
     * @param \Amasty\GiftCard\Api\Data\ImageInterface $image
     *
     * @return \Amasty\GiftCard\Api\Data\ImageInterface
     */
    public function save(\Amasty\GiftCard\Api\Data\ImageInterface $image): \Amasty\GiftCard\Api\Data\ImageInterface;

    /**
     * @param \Amasty\GiftCard\Api\Data\ImageInterface $image
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function delete(\Amasty\GiftCard\Api\Data\ImageInterface $image): bool;

    /**
     * @param int $id
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function deleteById(int $id): bool;

    /**
     * @return \Amasty\GiftCard\Api\Data\ImageInterface[]
     */
    public function getList(): array;
}
