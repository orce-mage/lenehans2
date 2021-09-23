<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */


namespace Amasty\StorePickupWithLocator\Api;

use Amasty\StorePickupWithLocator\Api\Data\QuoteSearchResultsInterface;
use Amasty\StorePickupWithLocator\Api\Data\QuoteInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;

interface QuoteRepositoryInterface
{
    /**
     * @param QuoteInterface $quoteModel
     * @return QuoteInterface
     * @throws CouldNotSaveException
     */
    public function save(QuoteInterface $quoteModel);

    /**
     * @param int $itemId
     * @return QuoteInterface
     * @throws NoSuchEntityException
     */
    public function get($itemId);

    /**
     * @param QuoteInterface $quoteModel
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(QuoteInterface $quoteModel);

    /**
     * @param int $itemId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function deleteById($itemId);

    /**
     * @param int $addressId
     * @return QuoteInterface
     */
    public function getByAddressId($addressId);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return QuoteSearchResultsInterface
     */
    public function getList(SearchCriteriaInterface $searchCriteria);
}
