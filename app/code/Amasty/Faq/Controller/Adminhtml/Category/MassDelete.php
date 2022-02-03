<?php

namespace Amasty\Faq\Controller\Adminhtml\Category;

use Amasty\Faq\Api\Data\CategoryInterface;

class MassDelete extends \Amasty\Faq\Controller\Adminhtml\AbstractCategoryMassAction
{
    /**
     * @param CategoryInterface $question
     */
    protected function itemAction(CategoryInterface $category)
    {
        $this->repository->deleteById($category->getCategoryId());
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function getErrorMessage()
    {
        return __('We can\'t delete item right now. Please review the log and try again.');
    }

    /**
     * @param int $collectionSize
     *
     * @return \Magento\Framework\Phrase
     */
    protected function getSuccessMessage($collectionSize = 0)
    {
        if ($collectionSize) {
            return __('A total of %1 record(s) have been deleted.', $collectionSize);
        }

        return __('No records have been deleted.');
    }
}
