<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales\Totals;

class Invoice extends \Magento\Sales\Block\Order\Invoice\Totals
{
    private $mainNode = null;

    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        $design = $this->getTheme();
        $template = $this->getTemplate();
        $template = str_replace('/default/', '/'.$design.'/', $template);
        $this->setTemplate($template);
        return $this;
    }

    public function getVarModel()
    {
        return $this->getMainNode()->getVarModel();
    }

    public function getMainNode()
    {
        if ($this->mainNode == null) {
            $this->mainNode = $this->getParentBlock()->getParentBlock();
        }

        return $this->mainNode;
    }
}
