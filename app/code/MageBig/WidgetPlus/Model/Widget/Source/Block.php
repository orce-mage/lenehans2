<?php

namespace MageBig\WidgetPlus\Model\Widget\Source;

use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;

class Block implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var array
     */
    protected $options;

    /**
     * @var CollectionFactory
     */
    protected $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    public function toOptionArray()
    {
        $collection = $this->collectionFactory->create()->load();
        $blocks = [];
        foreach ($collection as $item) {
            $blocks[] = [
                'value' => $item->getIdentifier(),
                'label' => $item->getTitle(),
            ];
        }

        return $blocks;
    }
}
