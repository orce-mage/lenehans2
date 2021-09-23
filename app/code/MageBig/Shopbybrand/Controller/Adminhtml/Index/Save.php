<?php
/**
 * Copyright © 2020 MageBig, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MageBig\Shopbybrand\Controller\Adminhtml\Index;
use Magento\Backend\App\Action;
use Magento\Framework\View\Result\PageFactory;
use Magento\TestFramework\ErrorLog\Logger;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{
	protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('MageBig_Shopbybrand::save');
    }
	public function execute()
    {
        $data = $this->getRequest()->getPostValue();
		$storeId = (int)$this->getRequest()->getParam('store');

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */



        $resultRedirect = $this->resultRedirectFactory->create();

        if ($data) {
			$id = $this->getRequest()->getParam('entity_id');
            $model = $this->_objectManager->create('MageBig\Shopbybrand\Model\Brand');

			$this->_eventManager->dispatch(
				'product_brand_prepare_save',
				['product_brand' => $model, 'request' => $this->getRequest()]
			);

            $connection = $model->getResource()->getConnection();
            $select = $connection->select()->from(
                $model->getResource()->getTable('eav_attribute_option'), 'attribute_id'
            )->where('option_id = '.$data['option_id'])->limit(1);
            $attributeId = $connection->fetchOne($select);
            $data['attribute_id'] = $attributeId;

			$model->setStoreId($storeId);
			$model->setOptionId($data['option_id']);

			if ($id) {
                $model->load($id);
				if ($id != $model->getId()) {
					throw new LocalizedException(__('Wrong brand specified.'));
				}
            }

			$model->addData($data);

            if ($useDefaults = $this->getRequest()->getPost('use_default')) {
                foreach ($useDefaults as $attributeCode) {
                    $model->setData($attributeCode, false);
                }
            }

            try {
                $model->save();
                $this->messageManager->addSuccess(__('You saved this brand.'));
				$this->_objectManager->get('Magento\Backend\Model\Session')->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [
                        'entity_id' => $model->getId(),
                        '_current' => true,
                        'store' => $storeId,
                        'option_id' => $data['option_id']
                    ]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addError($e->getMessage());
            } catch (\Exception $e) {
				$this->messageManager->addError($e->getMessage());
                $this->messageManager->addException($e, __('Something went wrong while saving the label.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['option_id' => $this->getRequest()->getParam('option_id'), 'entity_id' => $model->getId() ]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
