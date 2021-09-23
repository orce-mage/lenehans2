<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */


namespace Amasty\GiftCardAccount\Api;

interface GiftCardQuoteRepositoryInterface
{
    /**
     * @param int $entityId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $entityId): \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface;

    /**
     * @param int $quoteId
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getByQuoteId(int $quoteId): \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface;

    /**
     * @param \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface $quote
     *
     * @return \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    public function save(
        \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface $quote
    ): \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface;

    /**
     * @param \Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface $quote
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\GiftCardAccount\Api\Data\GiftCardQuoteInterface $quote): bool;
}
