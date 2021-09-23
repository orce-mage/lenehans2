<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Action\Export\Mapping;

use Amasty\ExportCore\Api\ActionInterface;
use Amasty\ExportCore\Api\Config\Profile\FieldsConfigInterface;
use Amasty\ExportCore\Api\ExportProcessInterface;
use Amasty\ExportCore\Export\Config\EntityConfigProvider;
use Amasty\ExportCore\Export\Config\RelationConfigProvider;

class MappingAction implements ActionInterface
{
    /**
     * @var array
     */
    private $mapping;

    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    /**
     * @var RelationConfigProvider
     */
    private $relationConfigProvider;

    public function __construct(
        EntityConfigProvider $entityConfigProvider,
        RelationConfigProvider $relationConfigProvider
    ) {
        $this->entityConfigProvider = $entityConfigProvider;
        $this->relationConfigProvider = $relationConfigProvider;
    }

    public function execute(
        ExportProcessInterface $exportProcess
    ) {
        if (!$exportProcess->isChildProcess() && $exportProcess->getCurrentBatchIndex() === 1) {
            $exportProcess->addInfoMessage('The data is being mapped.');
        }
        $exportProcess->setData($this->processMapping($this->mapping, $exportProcess->getData()));
    }

    public function renderHeader($mapping): array
    {
        $result = [];
        foreach ($mapping['fields'] as $field => $map) {
            $result[!empty($map) ? $map : $field] = '';
        }
        if (!empty($mapping['subentities'])) {
            foreach ($mapping['subentities'] as $field => $subentity) {
                $result[!empty($subentity['map']) ? $subentity['map'] : $field] = $this->renderHeader(
                    $subentity
                );
            }
        }

        return $result;
    }

    public function processMapping($mapping, $data)
    {
        $result = [];

        foreach ($data as $row) {
            $outputRow = [];
            foreach ($mapping['fields'] as $field => $map) {
                $outputRow[!empty($map) ? $map : $field] = $row[$field] ?? '';
            }
            if (!empty($mapping['subentities'])) {
                foreach ($mapping['subentities'] as $field => $subentity) {
                    $outputRow[!empty($subentity['map']) ? $subentity['map'] : $field] = $this->processMapping(
                        $subentity,
                        $row[$field] ?? []
                    );
                }
            }
            $result[] = $outputRow;
        }

        return $result;
    }

    public function initialize(ExportProcessInterface $exportProcess)
    {
        $this->mapping = $this->getProfileFields($exportProcess->getProfileConfig()->getFieldsConfig());
        //TODO this is temporary solution because i don't know how to do it right
        $exportProcess->getExtensionAttributes()->getHelper()->setHeaderStructure($this->renderHeader($this->mapping));
    }

    public function getProfileFields(FieldsConfigInterface $fieldsConfig): array
    {
        $result = [];
        $result['fields'] = [];
        if ($fieldsConfig->getFields()) {
            foreach ($fieldsConfig->getFields() as $field) {
                $result['fields'][$field->getName()] = $field->getMap();
            }
        }
        if (!empty($fieldsConfig->getMap())) {
            $result['map'] = $fieldsConfig->getMap();
        }
        if ($fieldsConfig->getSubEntitiesFieldsConfig()) {
            foreach ($fieldsConfig->getSubEntitiesFieldsConfig() as $subEntityFieldConfig) {
                $result['subentities'][$subEntityFieldConfig->getName()] =
                    $this->getProfileFields($subEntityFieldConfig);
            }
        }

        return $result;
    }
}
