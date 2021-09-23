<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

use mysql_xdevapi\Exception;

/**
 * Class Save
 * @package Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
 */
class Save extends \Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
{

    /**
     * @var string
     */
    public $module = "MassStockUpdate";

    /**
     * @return \Magento\Backend\Model\View\Result\Forward|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {


        // check if data sent
        $data = $this->getRequest()->getPost();
        if ($data) {
            $model = $this->_objectManager->create('Wyomind\\' . $this->module . '\Model\Profiles');

            $id = $this->getRequest()->getParam('id');

            if (!$this->getRequest()->getParam('file_is_valid')) {
                $this->messageManager->addNotice(__('The source file must be a valid source file.'));
                unset($data["mapping"]);
                unset($data["file_path"]);
            }
            if ($id) {
                $model->load($id);
            }


            foreach ($data as $index => $value) {
                if (is_array($value)) {
                    $value = implode(",", $value);
                }
                $model->setData($index, $value);
            }

            if (!$this->_validatePostData($data)) {
                return $this->_resultRedirectFactory->create()->setPath($this->module . '/profiles/edit', ['id' => $model->getId(), "_current" => true]);
            }

            try {
                $model->save();
                $this->messageManager->addSuccess(__('The profile %1 [ID:%2] has been saved.', $model->getName(), $model->getId()));
                $this->_objectManager->get('Magento\Backend\Model\Session')->setFormData(false);

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect(strtolower($this->module) . '/profiles/edit', ['id' => $model->getId(), '_current' => true]);
                    return;
                }

                if ($this->getRequest()->getParam('run_i')) {
                    $this->getRequest()->setParam('profile_id', $model->getId());
                    return $this->_resultForwardFactory->create()->forward("run");
                }

                $this->_getSession()->setFormData($data);
                return $this->_resultRedirectFactory->create()->setPath(strtolower($this->module) . '/profiles/index');
            } catch (\Exception $e) {
                $this->messageManager->addError(__('Unable to save the profile.') . '<br/><br/>' . $e->getMessage());
                return $this->_resultRedirectFactory->create()->setPath(strtolower($this->module) . '/profiles/edit', ['id' => $model->getId(), "_current" => true]);
            }
        }
        return $this->_resultRedirectFactory->create()->setPath(strtolower($this->module) . '/profiles/index');
    }

    /**
     * @param $data
     * @return bool
     */
    protected function _validatePostData($data)
    {
        $errorNo = true;
        if (!empty($data['layout_update_xml']) || !empty($data['custom_layout_update_xml'])) {
            $validatorCustomLayout = $this->_objectManager->create('Magento\Core\Model\Layout\Update\Validator');
            if (!empty($data['layout_update_xml']) && !$validatorCustomLayout->isValid($data['layout_update_xml'])) {
                $errorNo = false;
            }
            if (!empty($data['custom_layout_update_xml']) && !$validatorCustomLayout->isValid(
                $data['custom_layout_update_xml']
            )
            ) {
                $errorNo = false;
            }
            foreach ($validatorCustomLayout->getMessages() as $message) {
                $this->_messageManager->addError($message);
            }
        }
        return $errorNo;
    }
}
