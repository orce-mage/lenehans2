<?php

namespace Amasty\Faq\Controller\Adminhtml\Question;

class NewAction extends \Amasty\Faq\Controller\Adminhtml\AbstractQuestion
{
    public function execute()
    {
        $this->_forward('edit');
    }
}
