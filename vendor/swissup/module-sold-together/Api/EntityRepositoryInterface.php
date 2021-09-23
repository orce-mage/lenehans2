<?php
namespace Swissup\SoldTogether\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * SoldTogether customer CRUD interface.
 * @api
 */
interface EntityRepositoryInterface
{
    /**
     * Save data.
     *
     * @param \Swissup\SoldTogether\Api\Data\EntityInterface $customer
     * @return \Swissup\SoldTogether\Api\Data\EntityInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Swissup\SoldTogether\Api\Data\EntityInterface $entity);

    /**
     * Retrieve data.
     *
     * @param int $relationId
     * @return \Swissup\SoldTogether\Api\Data\EntityInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($relationId);

    /**
     * Delete data.
     *
     * @param \Swissup\SoldTogether\Api\Data\EntityInterface $customer
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Swissup\SoldTogether\Api\Data\EntityInterface $customer);

    /**
     * Delete data by ID.
     *
     * @param int $customerId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($customerId);
}
