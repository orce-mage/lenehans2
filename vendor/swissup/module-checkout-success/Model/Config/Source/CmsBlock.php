<?php
namespace Swissup\CheckoutSuccess\Model\Config\Source;

use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;

class CmsBlock implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * Block collection factory
     *
     * @var CollectionFactory
     */
    protected $blockCollectionFactory;

    /**
     * Construct
     *
     * @param CollectionFactory $blockCollectionFactory
     */
    public function __construct(CollectionFactory $blockCollectionFactory)
    {
        $this->blockCollectionFactory = $blockCollectionFactory;
    }

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->blockCollectionFactory->create()->load()->toOptionArray();
        array_unshift($options, ['value' => '0', 'label' => __('No')]);
        return $options;
    }
}
