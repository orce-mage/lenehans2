<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */


namespace Amasty\GiftCard\Api;

interface GiftCardPriceRepositoryInterface
{
    /**
     * @param int $id
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardPriceInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): \Amasty\GiftCard\Api\Data\GiftCardPriceInterface;

    /**
     * @param \Amasty\GiftCard\Api\Data\GiftCardPriceInterface $amount
     *
     * @return \Amasty\GiftCard\Api\Data\GiftCardPriceInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(
        \Amasty\GiftCard\Api\Data\GiftCardPriceInterface $amount
    ): \Amasty\GiftCard\Api\Data\GiftCardPriceInterface;

    /**
     * @param \Amasty\GiftCard\Api\Data\GiftCardPriceInterface $amount
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\GiftCard\Api\Data\GiftCardPriceInterface $amount): bool;
}
