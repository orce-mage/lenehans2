<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace  Magetrend\Email\Block\Adminhtml\Email\Template\Grid\Column\Renderer;

class Yesno extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{

    /**
     * Render grid column
     *
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase
     */
    public function render(\Magento\Framework\DataObject $row)
    {

        if ($row->getIsMtEmail() == 1) {
            $str = 'Yes';
        } else {
            $str = 'No';
        }

        return __($str);
    }
}
