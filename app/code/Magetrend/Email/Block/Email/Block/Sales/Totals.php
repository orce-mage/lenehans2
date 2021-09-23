<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales;

class Totals extends \Magetrend\Email\Block\Email\Block\Template
{
    public function getTotalsHtml()
    {
        $block = null;
        if ($this->getParentBlock()->hasData('invoice')) {
            $block = $this->getChildBlock('invoice_totals');
        } elseif ($this->getParentBlock()->hasData('creditmemo')) {
            $block = $this->getChildBlock('creditmemo_totals');
        } elseif ($this->getParentBlock()->hasData('order')) {
            $block = $this->getChildBlock('order_totals');
        } elseif ($this->getParentBlock()->hasData('quote')) {
            $block = $this->getChildBlock('quote_totals');
        }

        if ($block === null) {
            return '';
        }

        $block->setTheme($this->getTheme());
        return $block->toHtml();
    }
}
