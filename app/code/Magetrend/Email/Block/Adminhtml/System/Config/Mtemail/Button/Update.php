<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Adminhtml\System\Config\Mtemail\Button;

class Update extends \Magetrend\Email\Block\Adminhtml\System\Config\Mtemail\Button
{
    /**
     * Returns action url
     * @return string
     */
    public function getActionUrl()
    {
        return $this->getUrl('mtemail/mtemail/update');
    }

    /**
     * Returns button frontend label
     * @return \Magento\Framework\Phrase
     */
    public function getFrontLabel()
    {
        return __('Update System Config');
    }
}
