<?php

namespace MageBig\WidgetPlus\Controller\Adminhtml\Widget;

class Categories extends \Magento\Widget\Controller\Adminhtml\Widget\Instance
{
    /**
     * @var \Magento\Framework\View\Layout
     */
    protected $layout;

    /**
     * @param \Magento\Backend\App\Action\Context          $context
     * @param \Magento\Framework\Registry                  $coreRegistry
     * @param \Magento\Widget\Model\Widget\InstanceFactory $widgetFactory
     * @param \Psr\Log\LoggerInterface                     $logger
     * @param \Magento\Framework\Math\Random               $mathRandom
     * @param \Magento\Framework\Translate\InlineInterface $translateInline
     * @param \Magento\Framework\View\Layout               $layout
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Widget\Model\Widget\InstanceFactory $widgetFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Math\Random $mathRandom,
        \Magento\Framework\Translate\InlineInterface $translateInline,
        \Magento\Framework\View\Layout $layout
    ) {
        $this->layout = $layout;
        parent::__construct($context, $coreRegistry, $widgetFactory, $logger, $mathRandom, $translateInline);
    }

    /**
     * Categories chooser Action (Ajax request).
     *
     * @return \Magento\Framework\Controller\Result\Raw
     */
    public function execute()
    {
        $selected = $this->getRequest()->getParam('selected', '');
        $isAnchorOnly = $this->getRequest()->getParam('is_anchor_only', 0);

        $chooser = $this->layout->createBlock('MageBig\WidgetPlus\Block\Adminhtml\Widget\Category\Chooser')
            ->setUseMassaction(true)
            ->setId($this->getRequest()->getParam('id', ''))
            ->setIsAnchorOnly($isAnchorOnly)
            ->setSelectedCategories(explode(',', $selected));

        /** @var \Magento\Framework\Controller\Result\Raw $resultRaw */
        $resultRaw = $this->resultFactory->create(\Magento\Framework\Controller\ResultFactory::TYPE_RAW);

        return $resultRaw->setContents($chooser->toHtml());
    }
}
