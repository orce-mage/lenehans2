<?php

namespace Amasty\Faq\Block\Widgets;

use Amasty\Faq\Model\ConfigProvider;
use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;

class SearchBox extends Template implements BlockInterface
{
    /**
     * @var string
     */
    protected $_template = 'Amasty_Faq::search_box.phtml';

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
    public function getSearchBoxWidth()
    {
        if (!$this->hasData('search_box_width')) {
            $this->setData('search_box_width', '60%');
        }

        return $this->getData('search_box_width');
    }

    /**
     * @return string
     */
    public function getSearchBoxAlign()
    {
        if (!$this->hasData('search_box_align')) {
            $this->setData('search_box_align', \Amasty\Faq\Model\Config\WidgetAlign::CENTER);
        }

        return $this->getData('search_box_align');
    }

    /**
     * @return string
     */
    public function getSearchBlockHtml()
    {
        return $this->getLayout()->createBlock(\Amasty\Faq\Block\Forms\Search::class)->toHtml();
    }
}
