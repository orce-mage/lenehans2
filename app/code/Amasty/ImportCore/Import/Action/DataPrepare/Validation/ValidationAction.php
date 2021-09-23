<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\DataPrepare\Validation;

use Amasty\ImportCore\Api\ActionInterface;
use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Api\Source\SourceDataStructureInterface;
use Amasty\ImportCore\Api\Validation\RelationValidatorInterface;
use Amasty\ImportCore\Api\Validation\RowValidatorInterface;
use Amasty\ImportCore\Import\OptionSource\ValidationStrategy;
use Amasty\ImportCore\Import\Source\Data\DataStructureProvider;
use Amasty\ImportCore\Import\Source\SourceDataStructure;
use Amasty\ImportCore\Import\Validation\CompositeValidationProvider;

class ValidationAction implements ActionInterface
{
    const ERROR_TYPE = 'validation';

    /**
     * @var CompositeValidationProvider
     */
    private $validationProvider;

    /**
     * @var DataStructureProvider
     */
    private $dataStructureProvider;

    /**
     * @var FieldValidator[][]|null
     */
    private $fieldRulesRegistry = null;

    /**
     * @var RowValidatorInterface[]|null
     */
    private $rowRulesRegistry = null;

    /**
     * @var RelationValidatorInterface[][]|null
     */
    private $relationRulesRegistry = null;

    /**
     * @var SourceDataStructureInterface
     */
    private $dataStructure;

    public function __construct(
        CompositeValidationProvider $validationProvider,
        DataStructureProvider $dataStructureProvider
    ) {
        $this->validationProvider = $validationProvider;
        $this->dataStructureProvider = $dataStructureProvider;
    }

    public function execute(ImportProcessInterface $importProcess): void
    {
        if (empty($this->fieldRulesRegistry)
            && empty($this->rowRulesRegistry)
        ) {
            return;
        }

        $entityCode = $importProcess->getEntityConfig()->getEntityCode();
        $importData = $importProcess->getData();

        $fieldRules = $this->fieldRulesRegistry[$entityCode] ?? [];
        $rowRule = $this->rowRulesRegistry[$entityCode] ?? null;
        $relationRules = $this->relationRulesRegistry[$entityCode] ?? [];

        $rowNumber = 1;
        if ($importProcess->getBatchNumber() > 1) {
            $batchSize = $importProcess->getProfileConfig()->getOverflowBatchSize()
                ?:
                $importProcess->getProfileConfig()->getBatchSize();
            $step = $importProcess->getBatchNumber() - 1;
            $rowNumber += $batchSize * $step;
        }
        foreach ($importData as $key => $row) {
            $isRowValid = $this->isRowValid(
                $importProcess,
                $row,
                $fieldRules,
                $relationRules,
                $rowNumber,
                $rowRule
            );
            $rowNumber++;

            if (!$isRowValid) {
                unset($importData[$key]);
            }

            if (!$isRowValid && $this->isNeedToTerminate($importProcess)) {
                $importProcess->getImportResult()->terminateImport(true);

                return;
            }
        }
        $importProcess->setData(array_values($importData));

        if (!$importProcess->isHasNextBatch()) {
            $importProcess->addInfoMessage((string)__('The data is being validated.'));
        }
    }

    private function isNeedToTerminate(ImportProcessInterface $importProcess): bool
    {
        $importProcess->increaseErrorQuantity();
        if ($importProcess->getProfileConfig()->getValidationStrategy() == ValidationStrategy::STOP_ON_ERROR) {
            return true;
        } elseif ($importProcess->getErrorQuantity() >= $importProcess->getProfileConfig()->getAllowErrorsCount()) {
            return true;
        }

        return false;
    }

    public function initialize(ImportProcessInterface $importProcess): void
    {
        $this->fieldRulesRegistry = $this->validationProvider->getFieldValidators(
            $importProcess
        );
        $this->rowRulesRegistry = $this->validationProvider->getRowValidators(
            $importProcess
        );
        $this->relationRulesRegistry = $this->validationProvider->getRelationValidators(
            $importProcess
        );

        $this->dataStructure = $this->dataStructureProvider->getDataStructure(
            $importProcess->getEntityConfig(),
            $importProcess->getProfileConfig()
        );
    }

    /**
     * Validate data row
     *
     * @param ImportProcessInterface $importProcess
     * @param array $row
     * @param FieldValidator[] $fieldRules
     * @param RelationValidatorInterface[] $relationRules
     * @param RowValidatorInterface|null $rowRule
     * @param int $rowNumber
     * @return bool
     */
    private function isRowValid(
        ImportProcessInterface $importProcess,
        array $row,
        array $fieldRules,
        array $relationRules,
        int $rowNumber,
        $rowRule = null
    ): bool {
        $isFieldsValid = $this->isFieldsDataValid($importProcess, $row, $this->dataStructure, $fieldRules, $rowNumber);
        if (!$isFieldsValid) {
            return false;
        }

        $isRowValid = $this->isRowDataValid($importProcess, $row, $rowNumber, $rowRule);
        if (!$isRowValid) {
            return false;
        }

        $isRelationValid = $this->isRelationDataValid($importProcess, $row, $relationRules, $rowNumber);
        if (!$isRelationValid) {
            return false;
        }

        return true;
    }

    /**
     * Checks if fields data valid
     *
     * @param ImportProcessInterface $importProcess
     * @param array $row
     * @param SourceDataStructureInterface $dataStructure
     * @param array $fieldRules
     * @param int $rowNumber
     *
     * @return bool
     */
    private function isFieldsDataValid(
        ImportProcessInterface $importProcess,
        array $row,
        SourceDataStructureInterface $dataStructure,
        array $fieldRules,
        int $rowNumber
    ): bool {
        if (!empty($fieldRules)) {
            $isValid = true;
            $logEntityName = $this->getLogEntityName($dataStructure);
            foreach ($dataStructure->getFields() as $field) {
                $fieldName = $dataStructure->getFieldName($field)
                    ? $dataStructure->getFieldName($field)
                    : $field;
                if (isset($fieldRules[$fieldName])) {
                    /** @var FieldValidator $rule */
                    foreach ($fieldRules[$fieldName] as $rule) {
                        if (!$rule->validate($row, $fieldName)) {
                            $logFieldName = $this->getLogFieldName($field, $dataStructure);
                            $importProcess->addValidationError(
                                (string)__($rule->getErrorMessage(), $logFieldName),
                                $rowNumber,
                                $logEntityName
                            );

                            $isValid = false;
                        }
                    }
                }
            }
            if (!$isValid) {
                return false;
            }
        }

        $subEntityStructures = $dataStructure->getSubEntityStructures();
        foreach ($subEntityStructures as $subEntityStructure) {
            $entityCode = $subEntityStructure->getEntityCode();
            if (!isset($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY][$entityCode])) {
                continue;
            }

            foreach ($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY][$entityCode] as &$subEntityRow) {
                $isSubEntityFieldsValid = $this->isFieldsDataValid(
                    $importProcess,
                    $subEntityRow,
                    $subEntityStructure,
                    $this->fieldRulesRegistry[$entityCode] ?? [],
                    $rowNumber
                );

                if (!$isSubEntityFieldsValid) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get formatted for logging entity name
     *
     * @param SourceDataStructureInterface $dataStructure
     * @return string
     */
    private function getLogEntityName(SourceDataStructureInterface $dataStructure): string
    {
        if ($dataStructure->getMap() && $dataStructure->getMap() !== $dataStructure->getEntityCode()) {
            return $dataStructure->getEntityCode() . "[{$dataStructure->getMap()}]";
        }

        return $dataStructure->getEntityCode();
    }

    /**
     * Get formatted for logging field name
     *
     * @param string $field
     * @param SourceDataStructureInterface $dataStructure
     * @return string
     */
    private function getLogFieldName(string $field, SourceDataStructureInterface $dataStructure): string
    {
        if ($dataStructure->getFieldName($field)) {
            return $dataStructure->getFieldName($field) . "[$field]";
        }

        return $field;
    }

    /**
     * Check if row data valid
     *
     * @param ImportProcessInterface $importProcess
     * @param array $row
     * @param int $rowNumber
     * @param RowValidatorInterface|null $rowRule
     * @return bool
     */
    private function isRowDataValid(
        ImportProcessInterface $importProcess,
        array $row,
        int $rowNumber,
        $rowRule = null
    ): bool {
        if ($rowRule && !$rowRule->validate($row)) {
            $importProcess->addValidationError((string)__($rowRule->getMessage()), $rowNumber);

            return false;
        }

        if (isset($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY])) {
            foreach ($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY] as $entityCode => $subEntityRows) {
                if (isset($this->rowRulesRegistry[$entityCode])) {
                    foreach ($subEntityRows as &$subEntityRow) {
                        $isSubEntityRowValid = $this->isRowDataValid(
                            $importProcess,
                            $subEntityRow,
                            $rowNumber,
                            $this->rowRulesRegistry[$entityCode]
                        );

                        if (!$isSubEntityRowValid) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }

    /**
     * Check if relation data valid
     *
     * @param ImportProcessInterface $importProcess
     * @param array $row
     * @param RelationValidatorInterface[] $relationRules
     * @param int $rowNumber
     * @return bool
     */
    private function isRelationDataValid(
        ImportProcessInterface $importProcess,
        array $row,
        array $relationRules,
        int $rowNumber
    ): bool {
        foreach ($relationRules as $entityCode => $relationRule) {
            $subEntityRows = $row[SourceDataStructure::SUB_ENTITIES_DATA_KEY][$entityCode] ?? [];
            if (!$relationRule->validate($row, $subEntityRows)) {
                $importProcess->addValidationError((string)__($relationRule->getMessage()), $rowNumber);

                return false;
            }
        }

        if (isset($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY])) {
            foreach ($row[SourceDataStructure::SUB_ENTITIES_DATA_KEY] as $entityCode => $subEntityRows) {
                if (isset($this->relationRulesRegistry[$entityCode])) {
                    foreach ($subEntityRows as &$subEntityRow) {
                        $isSubEntityRelationValid = $this->isRelationDataValid(
                            $importProcess,
                            $subEntityRow,
                            $this->relationRulesRegistry[$entityCode],
                            $rowNumber
                        );

                        if (!$isSubEntityRelationValid) {
                            return false;
                        }
                    }
                }
            }
        }

        return true;
    }
}
