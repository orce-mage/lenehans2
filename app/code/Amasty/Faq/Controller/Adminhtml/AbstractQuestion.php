<?php

namespace Amasty\Faq\Controller\Adminhtml;

use Magento\Backend\App\Action;

abstract class AbstractQuestion extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Faq::question';
}
