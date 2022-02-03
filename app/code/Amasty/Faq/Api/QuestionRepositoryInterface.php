<?php

namespace Amasty\Faq\Api;

/**
 * @api
 */
interface QuestionRepositoryInterface
{
    /**
     * Save FAQ question
     *
     * @param \Amasty\Faq\Api\Data\QuestionInterface $question
     * @return \Amasty\Faq\Api\Data\QuestionInterface
     */
    public function save(\Amasty\Faq\Api\Data\QuestionInterface $question);

    /**
     * Get FAQ question by id
     *
     * @param int $questionId
     * @return \Amasty\Faq\Api\Data\QuestionInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($questionId);

    /**
     * Delete FAQ question
     *
     * @param \Amasty\Faq\Api\Data\QuestionInterface $question
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\Faq\Api\Data\QuestionInterface $question);

    /**
     * Delete FAQ question by id
     *
     * @param int $questionId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($questionId);

    /**
     * Get FAQ questions list
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Amasty\Faq\Api\Data\QuestionSearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
