<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Schedule;

/**
 * Class NewAction
 */
class NewAction extends \Amasty\Storelocator\Controller\Adminhtml\Schedule
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
