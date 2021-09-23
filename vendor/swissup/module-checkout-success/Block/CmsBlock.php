<?php
namespace Swissup\CheckoutSuccess\Block;

use Magento\Framework\View\Element\Template;
use Magento\Widget\Block\BlockInterface;
use Magento\Store\Model\ScopeInterface;

class CmsBlock extends Template implements BlockInterface
{
    /**
     * Path to additional block's store config
     *
     * @var string
     */
    const CONFIG_PATH = 'success_page/blocks/';

    /**
     * Render block HTML
     *
     * @return string
     */
    protected function _toHtml()
    {
        $html = '';
        $id = $this->_scopeConfig->getValue(
            self::CONFIG_PATH . $this->getBlockType(),
            ScopeInterface::SCOPE_STORE
        );
        if ($id) {
            $html = $this->getLayout()->createBlock(
                'Magento\Cms\Block\Block'
            )->setBlockId(
                $id
            )->toHtml();
        }
        return $html;
    }
}
