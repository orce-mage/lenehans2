<?php

namespace Amasty\Faq\Api;

/**
 * @api
 */
interface CategoryRepositoryInterface
{
    /**
     * Save FAQ category
     *
     * @param \Amasty\Faq\Api\Data\CategoryInterface $category
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     */
    public function save(\Amasty\Faq\Api\Data\CategoryInterface $category);

    /**
     * Get FAQ category by id
     *
     * @param int $categoryId
     * @return \Amasty\Faq\Api\Data\CategoryInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($categoryId);

    /**
     * Delete FAQ category
     *
     * @param \Amasty\Faq\Api\Data\CategoryInterface $category
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Faq\Api\Data\CategoryInterface $category);

    /**
     * Delete FAQ category by id
     *
     * @param int $categoryId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($categoryId);

    /**
     * Get FAQ categories list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Amasty\Faq\Api\Data\CategorySearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
