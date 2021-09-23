<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Controller\Adminhtml\History;

class Delete extends \Amasty\Orderexport\Controller\Adminhtml\History
{

    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                // load entry
                $model = $this->_objectManager->create('Amasty\Orderexport\Model\History');
                $model->load($id);

                $filePath = BP . DIRECTORY_SEPARATOR . $model->getData('file_path');

                // remove associated files
                if ($filePath) {
                    @unlink($filePath);
                    @unlink($filePath . 'zip');
                }

                // remove history entry
                $model->delete();

                // send messages & redirect back
                $this->messageManager->addSuccessMessage(__('This history entry is deleted.'));
                $this->_redirect('amasty_orderexport/*/');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Can\'t delete item right now. Please review the log and try again.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('amasty_orderexport/*/edit', ['id' => $id]);

                return;
            }
        }
        $this->messageManager->addError(__('Can\'t find a item to delete.'));
        $this->_redirect('amasty_orderexport/*/');
    }
}
