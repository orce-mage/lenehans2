<?php

namespace Amasty\Faq\Block\Lists;

use Amasty\Faq\Model\ConfigProvider;
use Magento\Framework\View\Element\Template;

class NoSearchResult extends Template
{
    /**
     * @var ConfigProvider
     */
    protected $configProvider;

    public function __construct(
        Template\Context $context,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
    }

    public function _toHtml()
    {
        $parentBlock = $this->getParentBlock();
        $categoryBlock = $parentBlock->getChildBlock('amasty_faq_categories_search');
        $questionBlock = $parentBlock->getChildBlock('amasty_faq_questions');
        if ($parentBlock->getNameInLayout() == 'amasty_faq_search_view'
            && !count($categoryBlock->getCategories())
            && !$questionBlock->getCollection()->count()
        ) {
            return parent::_toHtml();
        }

        return '';
    }

    public function getNoItemsLabel()
    {
        if ($this->getParentBlock()->getNameInLayout() == 'amasty_faq_search_view') {
            return $this->configProvider->getNoItemsLabel();
        }

        return '';
    }
}
