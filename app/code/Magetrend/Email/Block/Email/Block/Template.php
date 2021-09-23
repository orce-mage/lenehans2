<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block;

class Template extends \Magento\Framework\View\Element\Template
{
    protected function _beforeToHtml()
    {
        //replace theme
        $template = $this->getTemplate();
        $theme = $this->getParentBlock()->getTheme();
        if ($theme != 'default') {
            $template = str_replace('/default/', '/'.$theme.'/', $template);
            $this->setTemplate($template);
        }
        return parent::_beforeToHtml();
    }

    public function getVarModel()
    {
        return $this->getParentBlock()->getVarModel();
    }

    public function getDirection()
    {
        return $this->getParentBlock()->getDirection();
    }

    public function isRTL()
    {
        return $this->getParentBlock()->isRTL();
    }

    public function isSingleTemplateMode()
    {
        return $this->getParentBlock()->isSingleTemplateMode();
    }
}
