<?php

namespace Amasty\Faq\Block\Widgets;

use Amasty\Faq\Model\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class BackToProduct extends Template implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = "Amasty_Faq::back_to_product_wrapper.phtml";

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
     * @inheritdoc
     */
    public function toHtml()
    {
        if (!$this->configProvider->isEnabled()) {
            return '';
        }

        return parent::toHtml();
    }

    /**
     * @return string
     */
    public function getBackToProductAlign()
    {
        if (!$this->hasData('back_to_product_align')) {
            $this->setData('back_to_product_align', \Amasty\Faq\Model\Config\WidgetAlign::CENTER);
        }

        return $this->getData('back_to_product_align');
    }

    /**
     * @return string
     */
    public function getBackToProductHtml()
    {
        return $this->getLayout()->createBlock(\Amasty\Faq\Block\BackToProduct::class)->toHtml();
    }
}
