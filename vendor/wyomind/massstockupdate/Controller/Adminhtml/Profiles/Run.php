<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles;

/**
 * Full import process
 */
class Run extends \Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
{


    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $request = $this->getRequest();
        $id = $this->getRequest()->getParam('id');
        try {
            $data = $this->getRequest()->getPost();

            if ($this->getRequest()->getParam("isAjax")) {
                session_write_close();
            }


            if ($data) {
                $model = $this->_objectManager->create('Wyomind\\' . $this->module . '\Model\Profiles');


                if ($id) {
                    $model->load($id);
                }


                $rtn = $model->multipleImport();

                $this->messageManager->addSuccess(__('The profile %1 [ID:%2] has been processed.', $model->getName(), $model->getId()));


                if (is_string($rtn["success"])) {
                    $this->messageManager->addSuccess(__('%1', $rtn["success"]));
                }
                if (is_string($rtn["notice"])) {
                    $this->messageManager->addNotice(__('%1', $rtn["notice"]));
                }
                if (is_string($rtn["warning"])) {
                    $this->messageManager->addWarning(__('%1', $rtn["warning"]));
                }


                if ($model->getSql()) {
                    $url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB, false);
                    $this->messageManager->addSuccess(__('The SQL file has been generated (<a href="%1" target="_blank">%1</a>)', $url . $model->getSqlPath() . $model->getSqlFile()));
                }

                if ($request->getParam('run_i')) {
                    return $this->_resultRedirectFactory->create()->setPath(strtolower($this->module) . '/profiles/edit', ['id' => $model->getId(), "_current" => true]);
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
            if ($request->getParam('run_i')) {
                return $this->_resultRedirectFactory->create()->setPath(strtolower($this->module) . '/profiles/edit', ['id' => $id, "_current" => true]);
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
            if ($request->getParam('run_i')) {
                return $this->_resultRedirectFactory->create()->setPath(strtolower($this->module) . '/profiles/edit', ['id' => $id, "_current" => true]);
            }
        }
    }
}
