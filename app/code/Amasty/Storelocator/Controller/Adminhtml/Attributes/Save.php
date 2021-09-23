<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Controller\Adminhtml\Attributes;

use Magento\Framework\Exception\LocalizedException;

/**
 * Class Save
 */
class Save extends \Amasty\Storelocator\Controller\Adminhtml\Attributes
{
    public function execute()
    {
        if ($this->getRequest()->getPostValue()) {
            $data = $this->getRequest()->getPostValue();

            try {
                /** For Magento Version >= 2.2.6 */
                if (!empty($data['serialized_options']) && empty($data['option'])) {
                    $serializedOptions = json_decode($data['serialized_options'], JSON_OBJECT_AS_ARRAY);
                    foreach ($serializedOptions as $serializedOption) {
                        $option = [];
                        //@codingStandardsIgnoreStart
                        parse_str($serializedOption, $option);
                        //@codingStandardsIgnoreEnd
                        $data = array_replace_recursive($data, $option);
                    }
                }
                /** @var \Amasty\Storelocator\Model\Attribute $model */
                $model = $this->attributeFactory->create();

                $id = (int)$this->getRequest()->getParam('attribute_id');

                if (isset($data['attribute_code'])) {
                    $this->attributeResourceModel->load($model, $data['attribute_code'], 'attribute_code');
                    if ($model->getId()) {
                        throw new LocalizedException(__('Attribute with the same attribute code exists.'));
                    }
                }

                if ($id) {
                    $this->attributeResourceModel->load($model, $id);
                    if ($id != $model->getId()) {
                        throw new LocalizedException(__('The wrong data is specified.'));
                    }
                }

                $frontendLabels = [];
                if (is_array($data)) {
                    $frontendLabels = $data['frontend_label'];
                    $defaultLabel = null;
                    if (isset($frontendLabels[0])) {
                        $defaultLabel = $frontendLabels[0];
                        unset($frontendLabels[0]);
                    }
                    $data['frontend_label'] = $defaultLabel;
                }
                $data['label_serialized'] = $this->serializer->serialize($frontendLabels);

                $model->setData($data);

                $this->attributeResourceModel->save($model);

                $this->attributeResourceModel->saveOptions($data, $model->getId());

                $this->messageManager->addSuccessMessage(__('Record has been successfully saved'));

                if ($this->getRequest()->getParam('back')) {
                    $this->_redirect('*/*/edit', ['id' => $model->getId()]);

                    return;
                }
                $this->_redirect('*/*/');

                return;
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $id = (int)$this->getRequest()->getParam('attribute_id');
                if (!empty($id)) {
                    $this->_redirect('*/*/edit', ['id' => $id]);
                } else {
                    $this->_redirect('*/*/index');
                }

                return;
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(
                    __('Something went wrong while saving data. Please review the error log.')
                );
                $this->logInterface->critical($e);
                $this->session->setPageData($data);
                $this->_redirect('*/*/edit', ['id' => (int)$this->getRequest()->getParam('attribute_id')]);

                return;
            }
        }
    }
}
