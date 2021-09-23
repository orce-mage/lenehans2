<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\MbFrame\Controller\Adminhtml\Theme;

class Index extends \Magento\Backend\App\Action
{
    /**
     * Index action.
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_setActiveMenu('MageBig_MbFrame::magebig_config');
        $this->_view->renderLayout();
    }

    /**
     * Check the permission to run it
     *
     * @return boolean
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageBig_MbFrame::themes_config');
    }
}
