<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Api;

use Magento\Framework\Exception\CouldNotSaveException;

/**
 * @api
 */
interface RuleRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\Stockstatus\Api\Data\RuleInterface $rule
     *
     * @return \Amasty\Stockstatus\Api\Data\RuleInterface
     */
    public function save(\Amasty\Stockstatus\Api\Data\RuleInterface $rule);

    /**
     * Get by id
     *
     * @param int $id
     * @param bool $withExtensions
     * @return \Amasty\Stockstatus\Api\Data\RuleInterface
     */
    public function getById($id, bool $withExtensions = false);

    /**
     * @return \Amasty\Stockstatus\Api\Data\RuleInterface
     */
    public function getNew();

    /**
     * Delete
     *
     * @param \Amasty\Stockstatus\Api\Data\RuleInterface $rule
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Stockstatus\Api\Data\RuleInterface $rule);

    /**
     * Delete by id
     *
     * @param int $id
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($id);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @param bool $withExtensions
     *
     * @return \Amasty\Stockstatus\Api\Data\RuleSearchResultsInterface
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria,
        bool $withExtensions = false
    );

    /**
     * Return instance of new rule.
     *
     * @param \Amasty\Stockstatus\Api\Data\RuleInterface $rule
     * @return \Amasty\Stockstatus\Api\Data\RuleInterface
     * @throws CouldNotSaveException
     */
    public function duplicate(
        \Amasty\Stockstatus\Api\Data\RuleInterface $rule
    ): \Amasty\Stockstatus\Api\Data\RuleInterface;
}
