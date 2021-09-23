<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Controller\Adminhtml\Profiles;

class NewAction extends \Amasty\Orderexport\Controller\Adminhtml\Profiles
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
