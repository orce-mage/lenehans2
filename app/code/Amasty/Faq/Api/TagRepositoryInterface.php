<?php

namespace Amasty\Faq\Api;

/**
 * @api
 */
interface TagRepositoryInterface
{
    /**
     * Save FAQ tag
     *
     * @param \Amasty\Faq\Api\Data\TagInterface $tag
     * @return \Amasty\Faq\Api\Data\TagInterface
     */
    public function save(\Amasty\Faq\Api\Data\TagInterface $tag);

    /**
     * Get FAQ tag by id
     *
     * @param int $tagId
     * @return \Amasty\Faq\Api\Data\TagInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($tagId);

    /**
     * Delete FAQ tag
     *
     * @param \Amasty\Faq\Api\Data\TagInterface $tag
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Faq\Api\Data\TagInterface $tag);

    /**
     * Delete FAQ tag by id
     *
     * @param int $tagId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($tagId);

    /**
     * Get FAQ tags list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Amasty\Faq\Api\Data\TagSearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
