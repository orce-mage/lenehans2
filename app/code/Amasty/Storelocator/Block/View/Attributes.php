<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Block\View;

use Magento\Framework\View\Element\Template;

/**
 * Class Attributes
 */
class Attributes extends Template
{
    /**
     * Show attributes
     *
     * @return string
     */
    public function toHtml()
    {
        if (!$this->getLocationAttributes()) {
            return '';
        }

        return parent::toHtml();
    }

    public function getLocationAttributes()
    {
        return $this->getLocation()->getAttributes();
    }
}
