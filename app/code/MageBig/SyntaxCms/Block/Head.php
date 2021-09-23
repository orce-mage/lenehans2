<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageBig\SyntaxCms\Block;

use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Head
 * @package MageBig\SyntaxCms\Block
 */
class Head extends Template
{
    const CFRONTEND = 'magebig_syntaxcms/general/frontend';

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_scopeConfig->isSetFlag(self::CFRONTEND, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->isEnabled()) {
            $this->pageConfig->addPageAsset('MageBig_SyntaxCms::cm/lib/codemirror.css');
            $this->pageConfig->addPageAsset('MageBig_SyntaxCms::cm/lib/snm.css');
            $this->pageConfig->addPageAsset('MageBig_SyntaxCms::js/snm_cm.js');
        }
        return parent::_prepareLayout();
    }
}
