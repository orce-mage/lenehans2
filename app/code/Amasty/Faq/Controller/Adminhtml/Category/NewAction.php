<?php

namespace Amasty\Faq\Controller\Adminhtml\Category;

class NewAction extends \Amasty\Faq\Controller\Adminhtml\Category
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
