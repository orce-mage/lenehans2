<?php

namespace Amasty\Faq\Model\Config;

use Magento\Cms\Api\Data\PageInterface;
use Magento\Framework\Option\ArrayInterface;

class CmsPage implements ArrayInterface
{
    /**
     * @var array
     */
    private $pages;

    /**
     * @var \Magento\Cms\Model\ResourceModel\Page\CollectionFactory
     */
    private $collectionFactory;

    public function __construct(
        \Magento\Cms\Model\ResourceModel\Page\CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->pages) {
            $collection = $this->collectionFactory->create();
            $collection->addFieldToSelect([PageInterface::PAGE_ID, PageInterface::TITLE]);
            foreach ($collection->getData() as $page) {
                $this->pages[] = ['value' => $page[PageInterface::PAGE_ID], 'label' => $page[PageInterface::TITLE]];
            }
        }

        return $this->pages;
    }
}
