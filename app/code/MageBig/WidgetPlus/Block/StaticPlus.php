<?php

namespace MageBig\WidgetPlus\Block;

class StaticPlus extends \Magento\Framework\View\Element\Template implements \Magento\Widget\Block\BlockInterface
{
    const CACHE_GROUP = 'WIDGETPLUS_STATICPLUS';

    /**
     * @var \Magento\Cms\Model\Template\FilterProvider
     */
    protected $_filterProvider;

    /**
     * Block factory
     *
     * @var \Magento\Cms\Model\BlockFactory
     */
    protected $_blockFactory;

    /**
     * StaticPlus constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager
     * @param \Magento\Cms\Model\Template\FilterProvider       $filterProvider
     * @param \Magento\Cms\Model\BlockFactory                  $blockFactory
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magento\Cms\Model\BlockFactory $blockFactory,
        array $data = []
    ) {
        $this->_filterProvider = $filterProvider;
        $this->_blockFactory   = $blockFactory;
        parent::__construct($context, $data);

        $this->addData([
            'cache_tags' => [self::CACHE_GROUP, \Magento\Framework\View\Element\Template::CACHE_GROUP],
        ]);
    }

    public function getStatic()
    {
        $blockId = $this->getData('block_id');
        $html    = '';

        if ($blockId) {
            $storeId = $this->_storeManager->getStore()->getId();
            /** @var \Magento\Cms\Model\Block $block */
            $block = $this->_blockFactory->create();
            $block->setStoreId($storeId)->load($blockId);
            if ($block->isActive()) {
                $html = $this->_filterProvider->getBlockFilter()->setStoreId($storeId)->filter($block->getContent());
            }
        }

        return $html;
    }

    public function getWidgetId()
    {
        $widgetId = crc32($this->getData('block_id'));
        $widgetId = 'widgetplus-block-'.$widgetId;

        return $widgetId;
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Magento\Cms\Model\Block::CACHE_TAG . '_' . $this->getData('block_id')];
    }

    /**
     * Get key pieces for caching block content
     *
     * @return array
     */
    public function getCacheKeyInfo()
    {
        return [
            self::CACHE_GROUP,
            $this->_storeManager->getStore()->getId(),
            $this->_design->getDesignTheme()->getId(),
            $this->getWidgetId(),
            serialize($this->getRequest()->getParams()),
        ];
    }
}
