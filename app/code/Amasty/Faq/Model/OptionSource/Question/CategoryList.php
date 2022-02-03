<?php

namespace Amasty\Faq\Model\OptionSource\Question;

use Amasty\Faq\Model\ResourceModel\Category\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

class CategoryList implements OptionSourceInterface
{
    /**
     * @var \Amasty\Faq\Model\ResourceModel\Category\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function toOptionArray()
    {
        $categories = [];
        $collection = $this->collectionFactory->create();
        foreach ($collection as $category) {
            $categories[] = [
                'value' => $category->getCategoryId(),
                'label' => $category->getTitle()
            ];
        }

        return $categories;
    }
}
