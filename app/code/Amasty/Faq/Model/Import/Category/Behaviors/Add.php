<?php

namespace Amasty\Faq\Model\Import\Category\Behaviors;

use Magento\Framework\Exception\CouldNotSaveException;

class Add extends AbstractBehavior
{

    /**
     * @param array $importData
     *
     * @return void
     */
    public function execute(array $importData)
    {
        $this->setStores();
        foreach ($importData as $categoryData) {
            $category = $this->categoryFactory->create();
            $this->setCategoryData($category, $categoryData);
            try {
                $this->repository->save($category);
            } catch (CouldNotSaveException $e) {
                null;
            }
        }
    }
}
