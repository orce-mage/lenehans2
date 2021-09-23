<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Controller\Adminhtml\Rules;

/**
 * Save action
 */
class Save extends \Wyomind\MassProductImport\Controller\Adminhtml\RulesAbstract
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        try {
            $replacementResource = $this->replacementResource->create();
            $rulesResource = $this->rulesFactory->create();
            $params = $this->getRequest()->getParam('general');
            $id = ($params["id"] != '') ? $params["id"] : null;
            $rulesResource->setData(["id" => $id, "name" => $params["name"], "use_regexp" => $params["use_regexp"]])->save();
            if ($id == null) {
                $id = $rulesResource->getId();
            }

            $replacementResource->deleteValues($params["id"]);
            if (isset($params['replacement_container'])) {
                $mappingValuesData = $params['replacement_container'];


                if (is_array($mappingValuesData) && !empty($mappingValuesData)) {
                    foreach ($mappingValuesData as $value) {
                        $model = $this->replacementFactory->create();
                        unset($value['id']);
                        $value["rule_id"] = $id;

                        $model->addData($value);
                        $model->save();
                    }
                }
            }

            $this->messageManager->addSuccessMessage(__("The replacement rule has been saved successfully"));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
            $this->_redirect("*/*/index/");
        }

        $this->_redirect('*/*/edit/id/' . $id);
    }
}
