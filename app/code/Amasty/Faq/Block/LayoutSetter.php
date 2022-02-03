<?php

namespace Amasty\Faq\Block;

use Amasty\Faq\Model\ConfigProvider;
use Magento\Framework\View\Element\Template;

class LayoutSetter extends Template
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        ConfigProvider $configProvider,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->pageConfig->setPageLayout($this->configProvider->getPageLayout());

        return parent::_prepareLayout();
    }
}
