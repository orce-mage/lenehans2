<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\DataPrepare\DataHandling;

use Amasty\ImportCore\Api\ActionInterface;
use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Api\Modifier\FieldModifierInterface;
use Amasty\ImportCore\Api\Modifier\RelationModifierInterface;
use Amasty\ImportCore\Api\Modifier\RowModifierInterface;
use Amasty\ImportCore\Import\Source\SourceDataStructure;

class DataHandlingAction implements ActionInterface
{
    const GROUP_AFTER_VALIDATE = 'afterValidate';
    const GROUP_BEFORE_VALIDATE = 'beforeValidate';

    const MODIFIERS_FOR_UNSET_KEYS = [
        \Amasty\ImportCore\Import\DataHandling\FieldModifier\DefaultValue::class
    ];

    /**
     * @var DataHandlerProvider
     */
    private $dataHandlerProvider;

    /**
     * @var string
     */
    private $modifiersGroup;

    /**
     * @var FieldModifierInterface[][]|null
     */
    private $fieldModifiersRegistry = null;

    /**
     * @var RelationModifierInterface[][]|null
     */
    private $relationModifiersRegistry = null;

    /**
     * @var RowModifierInterface[]|null
     */
    private $rowModifiersRegistry = null;

    public function __construct(
        DataHandlerProvider $dataHandlerProvider,
        $modifiersGroup = self::GROUP_BEFORE_VALIDATE
    ) {
        $this->dataHandlerProvider = $dataHandlerProvider;
        $this->modifiersGroup = $modifiersGroup;
    }

    public function execute(ImportProcessInterface $importProcess): void
    {
        if (empty($this->fieldModifiersRegistry)
            && empty($this->relationModifiersRegistry)
            && empty($this->rowModifiersRegistry)
        ) {
            return;
        }

        $entityCode = $importProcess->getEntityConfig()->getEntityCode();

        $data = $importProcess->getData();
        foreach ($data as &$row) {
            $this->modifyFields(
                $importProcess,
                $this->fieldModifiersRegistry[$entityCode] ?? [],
                $row
            );
            $this->modifyRow(
                $importProcess,
                $this->rowModifiersRegistry[$entityCode] ?? null,
                $row
            );
            $this->modifyRelations(
                $importProcess,
                $this->relationModifiersRegistry[$entityCode] ?? [],
                $row
            );
        }
        $importProcess->setData($data);
    }

    public function initialize(ImportProcessInterface $importProcess): void
    {
        $entitiesConfig = $importProcess->getProfileConfig()->getEntitiesConfig();

        $this->fieldModifiersRegistry = $this->dataHandlerProvider->getFieldModifiersRegistry(
            $entitiesConfig,
            $this->modifiersGroup
        );
        $this->relationModifiersRegistry = $this->dataHandlerProvider->getRelationModifiersRegistry(
            $entitiesConfig,
            $this->modifiersGroup
        );
        $this->rowModifiersRegistry = $this->dataHandlerProvider->getRowModifiersRegistry(
            $entitiesConfig,
            $this->modifiersGroup
        );
    }

    /**
     * Apply field modifiers to data row
     *
     * @param ImportProcessInterface $importProcess
     * @param FieldModifierInterface[][] $fieldModifiers
     * @param array $row
     * @return void
     */
    private function modifyFields(
        ImportProcessInterface $importProcess,
        array $fieldModifiers,
        array &$row
    ): void {
        foreach ($row as $field => $value) {
            if ($field == SourceDataStructure::SUB_ENTITIES_DATA_KEY) {
                foreach ($row[$field] as $entityCode => &$subEntityRows) {
                    $subEntityFieldModifiers = $this->fieldModifiersRegistry[$entityCode] ?? [];

                    foreach ($subEntityRows as &$subEntityRow) {
                        $this->modifyFields(
                            $importProcess,
                            $subEntityFieldModifiers,
                            $subEntityRow
                        );
                    }
                }
            } elseif (isset($fieldModifiers[$field])) {
                foreach ($fieldModifiers[$field] as $fieldModifier) {
                    try {
                        $row[$field] = $fieldModifier->transform($row[$field]);
                    } catch (\Throwable $throwable) {
                        $importProcess->addErrorMessage($throwable->getMessage());
                        if ($importProcess->getImportResult()->isImportTerminated()) {
                            throw new \RuntimeException(__('Import process was terminated.')->getText());
                        }
                    }
                }
            }
        }
        $this->modifyFieldsByUnsetKeys($row, $fieldModifiers);
    }

    private function modifyFieldsByUnsetKeys(array &$row, $fieldModifiers)
    {
        $fieldModifiers = array_diff_key($fieldModifiers, $row);
        foreach ($fieldModifiers as $field => $modifiers) {
            foreach ($modifiers as $modifier) {
                if (in_array(get_class($modifier), self::MODIFIERS_FOR_UNSET_KEYS)
                    && !isset($row[$field])
                ) {
                    $row[$field] = $modifier->transform('');
                }
            }
        }
    }

    /**
     * Apply relation modifiers to data row
     *
     * @param ImportProcessInterface $importProcess
     * @param RelationModifierInterface[] $relationModifiers
     * @param array $row
     * @return void
     */
    private function modifyRelations(
        ImportProcessInterface $importProcess,
        array $relationModifiers,
        array &$row
    ): void {
        foreach ($relationModifiers as $entityCode => $relationModifier) {
            if (isset($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY][$entityCode])) {
                $subEntityRows = &$row[SourceDataStructure::SUB_ENTITIES_DATA_KEY][$entityCode];
                $relationModifier->transform($row, $subEntityRows);
            }
        }

        if (isset($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY])) {
            foreach ($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY] as $entityCode => $subRows) {
                if (isset($this->relationModifiersRegistry[$entityCode])) {
                    foreach ($subRows as &$subRow) {
                        $this->modifyRelations(
                            $importProcess,
                            $this->relationModifiersRegistry[$entityCode],
                            $subRow
                        );
                    }
                }
            }
        }
    }

    /**
     * Apply row modifiers to data row
     *
     * @param ImportProcessInterface $importProcess
     * @param RowModifierInterface|null $rowModifier
     * @param array $row
     * @return void
     */
    private function modifyRow(
        ImportProcessInterface $importProcess,
        ?RowModifierInterface $rowModifier,
        array &$row
    ): void {
        if ($rowModifier) {
            try {
                $row = $rowModifier->transform($row);
            } catch (\Throwable $throwable) {
                $importProcess->addErrorMessage($throwable->getMessage());
                if ($importProcess->getImportResult()->isImportTerminated()) {
                    throw new \RuntimeException(__('Import process was terminated.')->getText());
                }
            }
        }

        if (isset($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY])) {
            foreach ($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY] as $entityCode => &$subEntityRows) {
                foreach ($subEntityRows as &$subEntityRow) {
                    $this->modifyRow(
                        $importProcess,
                        $this->rowModifiersRegistry[$entityCode] ?? null,
                        $subEntityRow
                    );
                }
            }
        }
    }
}
