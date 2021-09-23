<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email;

class Block extends \Magento\Framework\View\Element\Template
{
    /**
     * @var null|\Magetrend\Email\Model\Varmanager
     */
    private $varManager = null;

    /**
     * @var \Magetrend\Email\Model\Varmanager
     */
    public $varManagerModel;

    /**
     * @var \Magetrend\Email\Helper\Data
     */
    public $moduleHelper;

    /**
     * Block constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magetrend\Email\Model\Varmanager $varmanager
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magetrend\Email\Model\Varmanager $varmanager,
        \Magetrend\Email\Helper\Data $moduleHelper,
        array $data = []
    ) {
        $this->varManagerModel = $varmanager;
        $this->moduleHelper = $moduleHelper;
        parent::__construct($context, $data);
    }

    /**
     * Set template file before rendering
     *
     * @return $this
     */
    //@codingStandardsIgnoreLine
    protected function _beforeToHtml()
    {
        //replace theme
        $template = $this->getTemplate();
        $theme = $this->getTheme();
        if ($theme != 'default') {
            $template = str_replace('/default/', '/'.$theme.'/', $template);
            $this->setTemplate($template);
        }
        return parent::_beforeToHtml();
    }

    /**
     * Returns variable manager model
     *
     * @return \ Magetrend\Email\Model\Varmanager|null
     */
    public function getVarModel()
    {
        if ($this->varManager == null) {
            $this->varManager = $this->varManagerModel;
            $this->varManager->setTemplateId($this->getTemplateId());
            $this->varManager->setBlockId($this->getBlockId());
            $this->varManager->setBlockName($this->getBlockName());
        }
        return $this->varManager;
    }

    /**
     * Is direction rtl or not
     *
     * @return bool
     */
    public function isRTL()
    {
        return $this->getDirection() == 'rtl';
    }

    /**
     * Returns email template text direction
     *
     * @return string
     */
    public function getDirection()
    {
        return $this->_scopeConfig->getValue(
            \Magetrend\Email\Helper\Data::XML_PATH_DIRECTION,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->getVarModel()->getTemplate()->getStoreId()
        );
    }

    /**
     * Returns single template mode status
     *
     * @return string
     */
    public function isSingleTemplateMode()
    {
        return $this->moduleHelper->isSingleTemplateMode();
    }
}
