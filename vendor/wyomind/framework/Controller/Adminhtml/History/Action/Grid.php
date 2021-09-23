<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\Framework\Controller\Adminhtml\History\Action;

class Grid extends \Wyomind\Framework\Controller\Adminhtml\History\ActionHistory
{
    /**
     * Action history ajax action
     * @return void
     */
    public function execute()
    {
        $module = $this->getRequest()->getParam('module');
        $entity = $this->getRequest()->getParam('entity');

        $this->initCurrentAction();

        return $this->getResponse()->setBody(
            $this->_view->getLayout()->createBlock(
                'Wyomind\\' . $module . '\Block\Adminhtml' . ($entity ? '\\' . $entity : '') . '\Edit\Tab\ActionHistory'
            )->toHtml()
        );
    }
}
