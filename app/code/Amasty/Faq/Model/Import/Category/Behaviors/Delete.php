<?php

namespace Amasty\Faq\Model\Import\Category\Behaviors;

use Amasty\Faq\Api\ImportExport\CategoryInterface;
use Magento\Framework\Exception\CouldNotDeleteException;

class Delete extends AbstractBehavior
{
    /**
     * @param array $importData
     *
     * @return void
     */
    public function execute(array $importData)
    {
        foreach ($importData as $category) {
            if (!empty($category[CategoryInterface::CATEGORY_ID])) {
                try {
                    $this->repository->deleteById((int)$category[CategoryInterface::CATEGORY_ID]);
                } catch (CouldNotDeleteException $e) {
                    null;
                }
            }
        }
    }
}
