<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Controller\Adminhtml\Rules;

/**
 * Class Import
 * @package Wyomind\MassStockUpdate\Controller\Adminhtml\Profiles
 */
class Import extends \Wyomind\MassProductImport\Controller\Adminhtml\RulesAbstract
{
    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Exception
     */
    public function execute()
    {
        try {
            $this->_uploader = new \Magento\Framework\File\Uploader("file");
            $ruleId = $this->getRequest()->getParam("rule_id");
            if ($this->_uploader->getFileExtension() != "csv") {
                $this->messageManager->addError(__("Wrong file type (") . $this->_uploader->getFileExtension() . __(").<br>Choose a .csv file."));
            } else {
                $rootDir = $this->directoryListFactory->getPath(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
                $this->_uploader->save($rootDir . "/var/tmp", "import-file.csv");
                // récuperer le contenu
                $file = new \Magento\Framework\Filesystem\Driver\File;
                $import = new \Magento\Framework\File\Csv($file);
                $data = $import->getData($rootDir . "/var/tmp/" . $this->_uploader->getUploadedFileName());
                foreach ($data as $line) {
                    list($input, $output) = $line;
                    $model = $this->replacementFactory->create();
                    $values = ["rule_id" => $ruleId, "input" => $input, "output" => $output];
                    $model->addData($values);
                    $model->save();
                }
                $this->messageManager->addSuccessMessage(__("%1 rows have been successfully imported.", [count($data)]));
            }

            $result = $this->_resultRedirectFactory->create()->setPath("*/*/edit/id/" . $ruleId);
            return $result;
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__($e->getMessage()));
            $this->_redirect("*/*/edit/id/" . $ruleId);
        }
    }
}
