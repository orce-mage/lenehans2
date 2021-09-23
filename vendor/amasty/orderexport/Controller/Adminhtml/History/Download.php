<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */

namespace Amasty\Orderexport\Controller\Adminhtml\History;

class Download extends \Amasty\Orderexport\Controller\Adminhtml\History
{
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        if ($id) {
            try {
                // load entry
                $model = $this->_objectManager->create('Amasty\Orderexport\Model\History');
                $model->load($id);

                // add .zip for archived file downloads
                if($this->getRequest()->getParam('type') == 'zip'){
                    $model->setData('file_path', $model->getData('file_path').'.zip');
                }

                $filePath = BP . DIRECTORY_SEPARATOR . $model->getData('file_path');

                if ($model->getData('file_path') && file_exists($filePath)) {
                    /**@var \Magento\Framework\App\Response\Http\FileFactory $fileFactory */
                    $fileFactory = $this->_objectManager->create('\Magento\Framework\App\Response\Http\FileFactory');
                    $file = $fileFactory->create(
                        $model->getData('file_path'),
                        file_get_contents($filePath)
                    );

                    return $file;
                } else{
                    // send messages & redirect back
                    $this->messageManager->addSuccessMessage(__('File is no longer exists on server.'));
                    $this->_redirect('amasty_orderexport/*/');
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Can\'t download item right now. Please review the log and try again.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('amasty_orderexport/*/edit', ['id' => $id]);

                return false;
            }
        }
        $this->messageManager->addErrorMessage(__('Can\'t find a item to download.'));
        $this->_redirect('amasty_orderexport/*/');
    }
}
