<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Controller\Adminhtml\Thirdparty;

class NewAction extends \Amasty\Orderexport\Controller\Adminhtml\Thirdparty
{

    public function execute()
    {
        $this->_forward('edit');
    }
}
