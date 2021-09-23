<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageBig\SyntaxCms\Block\Adminhtml;

/**
 * Form fieldset renderer
 */
use MageBig\SyntaxCms\Plugin\Cms\Model\Wysiwyg\Config;
use Magento\Backend\Block\Template;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Show
 * @package MageBig\SyntaxCms\Block\Adminhtml
 */
class Show extends Template
{

    /**
     * @var string
     */
    protected $_template = 'show.phtml';

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        if ($this->isEnabled() && $this->getRequest()->getModuleName() != 'nwdthemes_revslider') {
            $this->pageConfig->addPageAsset('MageBig_SyntaxCms::cm/lib/codemirror.css');
            $this->pageConfig->addPageAsset('MageBig_SyntaxCms::cm/addon/hint/show-hint.css');
            $this->pageConfig->addPageAsset('MageBig_SyntaxCms::cm/addon/dialog/dialog.css');
            $this->pageConfig->addPageAsset('MageBig_SyntaxCms::cm/lib/snm.css');
        }
        return parent::_prepareLayout();
    }

    /**
     * @return array|mixed
     */
    public function getElements()
    {
        $value = $this->_scopeConfig->getValue(
            Config::BGELEMENTS,
            ScopeInterface::SCOPE_STORE
        );
        $value = json_decode($value, true);
        if (is_array($value)) {
            return $value;
        }
        return [];
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->_scopeConfig->isSetFlag(
            Config::ENABLED,
            ScopeInterface::SCOPE_STORE
        );
    }
    public function getJsonOption()
    {
        $option = [];
        $option['lineWrapping'] = (int)$this->_scopeConfig->getValue(
            'magebig_syntaxcms/general/line_wrapping',
            ScopeInterface::SCOPE_STORE
        );
        $option['theme'] = $this->_scopeConfig->getValue(
            'magebig_syntaxcms/general/theme',
            ScopeInterface::SCOPE_STORE
        );
        return json_encode($option);
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->isEnabled() && $this->getRequest()->getModuleName() != 'nwdthemes_revslider') {
            return parent::_toHtml();
        }
        return '';
    }
}
