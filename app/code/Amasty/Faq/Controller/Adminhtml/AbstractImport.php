<?php

namespace Amasty\Faq\Controller\Adminhtml;

abstract class AbstractImport extends \Magento\Backend\App\Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Amasty_Faq::faq_import';
}
