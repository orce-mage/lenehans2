<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Quote;

class Items extends \Magetrend\Email\Block\Email\Block\Sales\Items
{
    protected function _beforeToHtml()
    {
        return $this;
    }
}
