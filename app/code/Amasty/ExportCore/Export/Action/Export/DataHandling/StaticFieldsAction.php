<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Action\Export\DataHandling;

use Amasty\ExportCore\Api\ActionInterface;
use Amasty\ExportCore\Api\Config\Profile\FieldInterface;
use Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface;
use Amasty\ExportCore\Api\ExportProcessInterface;

class StaticFieldsAction implements ActionInterface
{
    public function execute(ExportProcessInterface $exportProcess)
    {
        $data = $exportProcess->getData();
        $this->addStaticFields($exportProcess->getProfileConfig()->getFieldsConfig(), $data);
        $exportProcess->setData($data);
    }

    protected function addStaticFields(FieldsConfigInterface $fieldsConfig, array &$data)
    {
        $staticFields = [];
        if (!empty($fieldsConfig->getFields())) {
            foreach ($fieldsConfig->getFields() as $field) {
                if ($field->getType() !== FieldInterface::STATIC_TYPE) {
                    continue;
                }
                $staticFields[$field->getName()] = $field->getExtensionAttributes()->getValue();
            }
        }

        foreach ($data as &$row) {
            if (!empty($staticFields)) {
                $row += $staticFields;
            }
            if (!empty($fieldsConfig->getSubEntitiesFieldsConfig())) {
                foreach ($fieldsConfig->getSubEntitiesFieldsConfig() as $subEntityConfig) {
                    if (empty($row[$subEntityConfig->getName()])) {
                        continue;
                    }
                    $this->addStaticFields($subEntityConfig, $row[$subEntityConfig->getName()]);
                }
            }
        }
    }

    //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    public function initialize(ExportProcessInterface $exportProcess)
    {
    }
}
