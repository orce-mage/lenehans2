<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Controller\Adminhtml\Rules;

/**
 * Delete action
 */
class Delete extends \Wyomind\MassProductImport\Controller\Adminhtml\RulesAbstract
{
    /**
     * Execute action
     * @return void
     */
    public function execute()
    {

        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                $model = $this->_objectManager->create("Wyomind\MassProductImport\Model\Rules");
                $model->setId($id);
                $model->delete();
                $this->messageManager->addSuccess(__("The replacement rule has been deleted."));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError(__("This replacement rule doesn't exist anymore."));
        }

        $return = $this->_resultRedirectFactory->create()->setPath("massproductimport/rules/index");
        return $return;
    }
}
