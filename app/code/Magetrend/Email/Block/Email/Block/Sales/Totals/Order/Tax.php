<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales\Totals\Order;

class Tax extends \Magento\Tax\Block\Sales\Order\Tax
{
    protected function _beforeToHtml()
    {
        parent::_beforeToHtml();
        $design = $this->getParentBlock()->getTheme();
        $template = $this->getTemplate();
        $template = str_replace('/default/', '/'.$design.'/', $template);
        $this->setTemplate($template);
        return $this;
    }
}
