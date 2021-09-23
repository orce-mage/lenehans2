<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Location;

use Magento\Framework\App\Filesystem\DirectoryList;

class Save extends \Amasty\Storelocator\Controller\Adminhtml\Location
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                $data = $this->getRequest()->getPostValue();
                $inputFilter = new \Zend_Filter_Input(
                    [],
                    [],
                    $data
                );
                $data = $inputFilter->getUnescaped();
                $id = (int)$this->getRequest()->getParam('id');
                if ($id) {
                    $model = $this->locationModel->load($id);
                    if ($id != $model->getId()) {
                        $this->messageManager->addErrorMessage(__('The wrong item is specified.'));
                        $this->_redirect('*/*/');

                        return;
                    }
                }
                if (isset($data['rule']['actions'])) {
                    $data['actions'] = $data['rule']['actions'];
                }
                if (isset($data['stores']) && !array_filter($data['stores'])) {
                    $data['stores'] = ',0,';
                }
                if (isset($data['stores']) && is_array($data['stores'])) {
                    $data['stores'] = ',' . implode(',', array_filter($data['stores'])) . ',';
                }

                if (isset($data['state_id']) && $data['state_id']) {
                    $data['state'] = $data['state_id'];
                }

                $this->filterData($data);

                unset($data['rule']);

                $this->locationModel->addData($data);
                $this->locationModel->loadPost($data); // rules

                $data['actions_serialize'] = $this->serializer->serialize(
                    $this->locationModel->getActions()->asArray()
                );

                $this->_prepareForSave($this->locationModel);

                $session = $this->sessionModel->setPageData($this->locationModel->getData());
                $this->locationModel->save();

                $this->_eventManager->dispatch(
                    'controller_amasty_location_form_save',
                    ['location_data' => $data, 'location_id' => $this->locationModel->getId()]
                );

                $this->messageManager->addSuccessMessage(__('You saved the item.'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $this->locationModel->getId()]);

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $id = (int)$this->getRequest()->getParam('id');
                if (!empty($id)) {
                    $this->_redirect('*/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('*/*/new');
                }

                return;
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
                $this->messageManager->addErrorMessage(
                    __($errorMessage)
                );
                $this->logger->critical($e);
                $this->sessionModel->setPageData($data);
                $this->_redirect('*/*/edit', ['id' => (int)$this->getRequest()->getParam('id')]);

                return;
            }
        }
        $this->_redirect('*/*/');
    }

    /**
     * @param array $data
     */
    private function filterData(&$data)
    {
        if (isset($data['marker_img']) && is_array($data['marker_img'])) {
            if (isset($data['marker_img'][0]['name'])) {
                $data['marker_img'] = $data['marker_img'][0]['name'];
            }
        } else {
            $data['marker_img'] = null;
        }
    }

    protected function _prepareForSave($model)
    {
        //upload images
        $data = $this->getRequest()->getPost();
        $path = $this->filesystem->getDirectoryRead(
            DirectoryList::MEDIA
        )->getAbsolutePath(
            'amasty/amlocator/'
        );

        $imagesTypes = ['store', 'marker'];
        foreach ($imagesTypes as $type) {
            $field = $type . '_img';

            $files = $this->getRequest()->getFiles();

            $isRemove = isset($data['remove_' . $field]);
            $fileData = $this->getRequest()->getFiles($field);
            $hasNew = !empty($fileData['name']);

            try {
                // remove the old file
                if ($isRemove || $hasNew) {
                    $oldName = isset($data['old_' . $field]) ? $data['old_' . $field] : '';
                    if ($oldName) {
                        $this->ioFile->rm($path . $oldName);
                        $model->setData($field, '');
                    }
                }

                // upload a new if any
                if (!$isRemove && $hasNew) {
                    //find the first available name
                    $locationId = $model->getId();
                    $newName = $locationId . preg_replace('/[^a-zA-Z0-9_\-\.]/', '', $files[$field]['name']);
                    if (substr($newName, 0, 1) == '.') {
                        $newName = 'label' . $newName;
                    }
                    $uploader = $this->fileUploaderFactory->create(['fileId' => $field]);
                    $uploader->setAllowedExtensions(['jpg', 'jpeg', 'gif', 'png']);
                    $uploader->setAllowRenameFiles(true);
                    $uploader->save($path, $newName);

                    $model->setData($field, $newName);
                }
            } catch (\Exception $e) {
                if ($e->getCode() != \Magento\MediaStorage\Model\File\Uploader::TMP_NAME_EMPTY) {
                    $this->logger->critical($e);
                }
            }
        }

        return true;
    }
}
