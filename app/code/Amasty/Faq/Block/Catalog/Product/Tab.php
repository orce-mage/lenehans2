<?php

namespace Amasty\Faq\Block\Catalog\Product;

use Amasty\Faq\Model\ConfigProvider;
use Magento\Framework\View\Element\Template;

class Tab extends \Amasty\Faq\Block\AbstractBlock implements \Magento\Framework\DataObject\IdentityInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        Template\Context $context,
        ConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->configProvider = $configProvider;
        $this->setData('sort_order', $this->configProvider->getTabPosition());
    }

    /**
     * @return int
     */
    public function getShortAnswerBehavior()
    {
        return (int)$this->configProvider->getProductPageShortAnswerBehavior();
    }

    /**
     * @return bool
     */
    public function isShowQuestionForm()
    {
        return $this->configProvider->isShowAskQuestionOnProductPage();
    }

    /**
     * @return bool
     */
    public function showAskQuestionForm()
    {
        return $this->configProvider->isShowAskQuestionOnProductPage();
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $count = $this->getLayout()->getBlock('amasty_faq_questions')->getCollection()->count();
        $tabName = $this->configProvider->getTabName()
            ? $this->configProvider->getTabName()
            : __('Product Questions') . ' {count}';

        if (strpos($tabName, '{count}') !== false) {
            $tabName = str_replace('{count}', (($count) ? '(' . $count .')' : ''), $tabName);
        }
        $this->setTitle($this->escapeHtml($tabName));

        return parent::_toHtml();
    }

    /**
     * Return identifiers for produced content
     *
     * @return array
     */
    public function getIdentities()
    {
        return [\Amasty\Faq\Model\ResourceModel\Question\Collection::CACHE_TAG];
    }
}
