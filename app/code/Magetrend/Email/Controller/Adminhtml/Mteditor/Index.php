<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Controller\Adminhtml\Mteditor;

class Index extends \Magetrend\Email\Controller\Adminhtml\Mteditor
{
    public function execute()
    {
        $template = $this->_initTemplate('id');
        $this->_setEditMode();
        if ($template->getId()) {
            if (!$this->_validateConfig($template->getStoreId())) {
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('adminhtml/email_template/index');
            }

            $this->_objectManager->get('Magetrend\Email\Model\Template')
                ->deleteTmpVariables($template);
        }

        $this->_view->loadLayout();
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('MT Editor / Magento Admin'));

        $this->_view->renderLayout();
    }
}
