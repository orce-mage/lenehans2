<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api;

/**
 * @api
 */
interface RangeRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\Stockstatus\Api\Data\RangeInterface $range
     *
     * @return \Amasty\Stockstatus\Api\Data\RangeInterface
     */
    public function save(
        \Amasty\Stockstatus\Api\Data\RangeInterface $range
    ): \Amasty\Stockstatus\Api\Data\RangeInterface;

    /**
     * @return \Amasty\Stockstatus\Api\Data\RangeInterface
     */
    public function getNew(): \Amasty\Stockstatus\Api\Data\RangeInterface;

    /**
     * Get by id
     *
     * @param int $id
     *
     * @return \Amasty\Stockstatus\Api\Data\RangeInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById(int $id): \Amasty\Stockstatus\Api\Data\RangeInterface;

    /**
     * Delete
     *
     * @param \Amasty\Stockstatus\Api\Data\RangeInterface $range
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Stockstatus\Api\Data\RangeInterface $range): bool;

    /**
     * Delete by id
     *
     * @param int $id
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById(int $id): bool;

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    ): \Magento\Framework\Api\SearchResultsInterface;

    /**
     * Delete Ranges by Rule id
     *
     * @param int $ruleId
     *
     * @return bool true on success
     *
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function removeByRuleId(int $ruleId): bool;
}
