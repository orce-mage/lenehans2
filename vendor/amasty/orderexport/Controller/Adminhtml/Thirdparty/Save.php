<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Controller\Adminhtml\Thirdparty;

class Save extends \Amasty\Orderexport\Controller\Adminhtml\Thirdparty
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            try {
                /** @var \Amasty\Orderexport\Model\Thirdparty $model */
                $model = $this->_objectManager->create('Amasty\Orderexport\Model\Thirdparty');
                $data  = $this->getRequest()->getPostValue();
                $id    = $this->getRequest()->getParam('entity_id');

                if ($id) {
                    $model->load($id);
                    if ($id != $model->getId()) {
                        throw new \Magento\Framework\Exception\LocalizedException(__('This 3rd Party Link does not exist'));
                    }
                }

               if (isset($data['mapping_delete'])) {
                    foreach ($data['mapping_delete'] as $del_id => $del_val) {
                        if ($del_val) {
                            unset($data['mapping_options'][$del_id]);
                        }
                    }
                }

                $mappings = [];
                if (isset($data['mapping_options'])) {
                    foreach ($data['mapping_options'] as $map_id => $map_val) {
                        $mappings[$map_id] = [
                            'id'     => $map_id,
                            'option' => $map_val,
                            'value'  => isset($data['mapping_values'][$map_id]) ? $data['mapping_values'][$map_id] : '',
                            'order'  => isset($data['mapping_order'][$map_id]) ? $data['mapping_order'][$map_id] : 0
                        ];
                    }
                }
                $data['mapping'] = serialize($mappings);

                $model->setData($data);
                $session = $this->_objectManager->get('Magento\Backend\Model\Session');
                $session->setPageData($model->getData());
                $model->save();

                $this->messageManager->addSuccessMessage(__('The 3rd Party Link has been saved'));
                $session->setPageData(false);
                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('amasty_orderexport/*/edit', ['id' => $model->getId()]);

                    return;
                }
                $this->_redirect('amasty_orderexport/*/');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                $id = (int)$this->getRequest()->getParam('id');

                if (!empty($id)) {
                    $this->_redirect('amasty_orderexport/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('amasty_orderexport/*/new');
                }

                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving the item data. Please review the error log.')
                );
                $this->_objectManager->get('Psr\Log\LoggerInterface')->critical($e);
                $this->_redirect('amasty_orderexport/*/');

                return;
            }
        }
        $this->_redirect('amasty_orderexport/*/');
    }
}
