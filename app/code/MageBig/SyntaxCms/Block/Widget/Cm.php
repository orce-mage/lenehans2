<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageBig\SyntaxCms\Block\Widget;

/**
 * CM Widget
 *
 */
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Widget\Block\BlockInterface;

/**
 * Class Cm
 * @package MageBig\SyntaxCms\Block\Widget
 */
class Cm extends Template implements BlockInterface
{
    const CFRONTEND = 'magebig_syntaxcms/general/frontend';

    protected $_htmlId = null;

    /**
     * @return null|string
     */
    public function getHtmlId()
    {
        if ($this->_htmlId === null) {
            static $counter = 0;
            $counter++;
            $this->_htmlId = 'snm_cm_' . $counter;

        }
        return $this->_htmlId;
    }

    /**
     * @return string
     */
    public function toHtml()
    {
        if (!$this->isEnabled()) {
            return '';
        }
        return parent::toHtml();
    }

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
        //    $this->pageConfig->addPageAsset('MageBig_SyntaxCms/cm/lib/snm.css');
        return parent::_prepareLayout();
    }

    /**
     * @return string
     */
    public function getDecodeCode()
    {
        $code = $this->getCode();
        $code = urldecode($code);
        $code = str_replace(['\{', '\}', '\"', "\'"], ['{', '}', '"', "'"], $code);
        return '' . $code;
    }

}
