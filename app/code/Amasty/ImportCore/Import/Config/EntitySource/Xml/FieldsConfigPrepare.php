<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Import\Config\EntitySource\Xml;

use Amasty\ImportCore\Api\Config\Entity\Field\ActionInterfaceFactory;
use Amasty\ImportCore\Api\Config\Entity\Field\Configuration\IdentificationInterface;
use Amasty\ImportCore\Api\Config\Entity\Field\Configuration\PreselectedInterface;
use Amasty\ImportCore\Api\Config\Entity\Field\Configuration\SyncFieldInterface;
use Amasty\ImportCore\Api\Config\Entity\Field\FieldInterfaceFactory;
use Amasty\ImportCore\Api\Config\Entity\Field\ValidationInterfaceFactory;
use Amasty\ImportCore\Api\Config\Entity\FieldsConfigInterface;
use Amasty\ImportCore\Api\Config\Entity\FieldsConfigInterfaceFactory;
use Amasty\ImportCore\Api\Config\Entity\Row\ValidationInterface as RowValidationInterface;
use Amasty\ImportCore\Api\Config\Entity\Row\ValidationInterfaceFactory as RowValidationInterfaceFactory;
use Amasty\ImportCore\Api\Config\Entity\SampleData\RowInterfaceFactory;
use Amasty\ImportCore\Api\Config\Entity\SampleData\ValueInterfaceFactory;
use Amasty\ImportCore\Api\Modifier\FieldModifierInterface;
use Amasty\ImportCore\Api\Validation\FieldValidatorInterface;
use Amasty\ImportCore\Api\Validation\RowValidatorInterface;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterfaceFactory;
use Amasty\ImportExportCore\Config\ConfigClass\Factory as ObjectFactory;
use Amasty\ImportExportCore\Config\Xml\ArgumentsPrepare;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\ObjectManagerInterface;

class FieldsConfigPrepare
{
    /**
     * @var FieldsConfigInterfaceFactory
     */
    private $fieldsConfigFactory;

    /**
     * @var FieldInterfaceFactory
     */
    private $fieldFactory;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ActionInterfaceFactory
     */
    private $actionFactory;

    /**
     * @var ValidationInterfaceFactory
     */
    private $validationFactory;

    /**
     * @var ConfigClassInterfaceFactory
     */
    private $configClassFactory;

    /**
     * @var ObjectFactory
     */
    private $objectFactory;

    /**
     * @var ArgumentsPrepare
     */
    private $argumentsPrepare;

    /**
     * @var RowInterfaceFactory
     */
    private $sampleRowFactory;

    /**
     * @var ValueInterfaceFactory
     */
    private $sampleFieldFactory;

    /**
     * @var RowValidationInterfaceFactory
     */
    private $rowValidationFactory;

    public function __construct(
        FieldsConfigInterfaceFactory $fieldsConfigFactory,
        FieldInterfaceFactory $fieldFactory,
        ActionInterfaceFactory $actionFactory,
        ValidationInterfaceFactory $validationFactory,
        ConfigClassInterfaceFactory $configClassFactory,
        ObjectFactory $objectFactory,
        RowInterfaceFactory $sampleRowFactory,
        ValueInterfaceFactory $sampleFieldFactory,
        ArgumentsPrepare $argumentsPrepare,
        ObjectManagerInterface $objectManager,
        RowValidationInterfaceFactory $rowValidationFactory
    ) {
        $this->fieldsConfigFactory = $fieldsConfigFactory;
        $this->fieldFactory = $fieldFactory;
        $this->objectManager = $objectManager;
        $this->actionFactory = $actionFactory;
        $this->validationFactory = $validationFactory;
        $this->configClassFactory = $configClassFactory;
        $this->objectFactory = $objectFactory;
        $this->argumentsPrepare = $argumentsPrepare;
        $this->sampleRowFactory = $sampleRowFactory;
        $this->sampleFieldFactory = $sampleFieldFactory;
        $this->rowValidationFactory = $rowValidationFactory;
    }

    public function execute(array $xmlFieldsConfig): FieldsConfigInterface
    {
        $fieldsConfig = $this->fieldsConfigFactory->create();
        if (!empty($xmlFieldsConfig['rowActionClass'])) {
            $fieldsConfig->setRowActionClass($xmlFieldsConfig['rowActionClass']);
        }
        if (!empty($xmlFieldsConfig['rowValidation']['class'])) {
            $rowValidation = $this->getRowValidation($xmlFieldsConfig['rowValidation']);
            $fieldsConfig->setRowValidation($rowValidation);
        }

        $fieldsConfig->setFields($this->getFields($xmlFieldsConfig['fields'] ?? []));
        if (!empty($xmlFieldsConfig['sampleData'])) {
            $fieldsConfig->setSampleData($this->getSampleData($xmlFieldsConfig['sampleData']));
        }

        $fieldsConfigClass = $xmlFieldsConfig['fieldsClass']['class'] ?? null;
        if ($fieldsConfigClass) {
            $fieldsConfig = $this->getFieldsConfigInstance(
                $fieldsConfigClass,
                $xmlFieldsConfig['fieldsClass']['arguments'] ?? []
            )->execute($fieldsConfig);
        }

        return $fieldsConfig;
    }

    private function getFields(array $fieldsConfig): array
    {
        $fields = [];
        foreach ($fieldsConfig as $name => $fieldConfig) {
            $field = $this->fieldFactory->create();
            $field->setName($name);
            $field->setIsIdentity($fieldConfig['isIdentity']);
            if (!empty($fieldConfig['map'])) {
                $field->setMap($fieldConfig['map']);
            }
            if (!empty($fieldConfig['isFile'])) {
                $field->setIsFile($fieldConfig['isFile']);
            }
            if (!empty($fieldConfig['actions'])) {
                $actions = [];
                foreach ($fieldConfig['actions'] as $actionConfig) {
                    $action = $this->actionFactory->create();
                    $class = $this->configClassFactory->create([
                        'baseType'  => FieldModifierInterface::class,
                        'name'      => $actionConfig['class'],
                        'arguments' => $this->argumentsPrepare->execute($actionConfig['arguments'])
                    ]);

                    $action->setConfigClass($class)
                        ->setGroup($actionConfig['apply']);

                    $actions[] = $action;
                }
                if (!empty($actions)) {
                    $field->setActions($actions);
                }
            }
            if (!empty($fieldConfig['validation'])) {
                $validations = [];
                foreach ($fieldConfig['validation'] as $validationConfig) {
                    $validation = $this->validationFactory->create();
                    $class = $this->configClassFactory->create([
                        'baseType'  => FieldValidatorInterface::class,
                        'name'      => $validationConfig['class'],
                        'arguments' => $this->argumentsPrepare->execute($validationConfig['arguments'])
                    ]);

                    $validation->setConfigClass($class);
                    if (!empty($validationConfig['error'])) {
                        $validation->setError($validationConfig['error']);
                    }
                    if (!empty($validationConfig['includeBehaviors'])) {
                        $validation->setIncludeBehaviors($validationConfig['includeBehaviors']);
                    } elseif (!empty($validationConfig['excludeBehaviors'])) {
                        $validation->setExcludeBehaviors($validationConfig['excludeBehaviors']);
                    }
                    if (!empty($validationConfig['rootOnly'])) {
                        $validation->setIsApplyToRootEntityOnly($validationConfig['rootOnly']);
                    }
                    $validations[] = $validation;
                }
                if (!empty($validations)) {
                    $field->setValidations($validations);
                }
            }

            if (!empty($fieldConfig['preselected'])) {
                $preselectedConfig = $fieldConfig['preselected'];
                /** @var PreselectedInterface $preselected */
                $preselected = $this->objectManager->create(PreselectedInterface::class);
                $preselected->setIsRequired($preselectedConfig['isRequired']);

                if (isset($preselectedConfig['behaviors']['excludeBehaviors'])) {
                    $preselected->setExcludeBehaviors($preselectedConfig['behaviors']['excludeBehaviors']);
                } elseif (isset($preselectedConfig['behaviors']['includeBehaviors'])) {
                    $preselected->setIncludeBehaviors($preselectedConfig['behaviors']['includeBehaviors']);
                }
                $field->setPreselected($preselected);
            }
            if (!empty($fieldConfig['identification'])) {
                $identificationConfig = $fieldConfig['identification'];
                /** @var IdentificationInterface $identification */
                $identification = $this->objectManager->create(IdentificationInterface::class);
                $identification->setIsIdentifier($identificationConfig['isIdentifier']);
                $identification->setLabel($identificationConfig['label']);
                $field->setIdentification($identification);
            }
            if (!empty($fieldConfig['remove'])) {
                $field->setRemove($fieldConfig['remove']);
            }

            if (!empty($fieldConfig['synchronization'])) {
                $synchronizationConfig = $fieldConfig['synchronization'];
                $synchronizationFields = [];

                foreach ($synchronizationConfig as $syncFieldConfig) {
                    /** @var SyncFieldInterface $syncField */
                    $syncField = $this->objectManager->create(SyncFieldInterface::class);
                    $syncField->setEntityName($syncFieldConfig['entityName']);
                    $syncField->setFieldName($syncFieldConfig['fieldName']);
                    $synchronizationFields[] = $syncField;
                }
                $field->setSynchronization($synchronizationFields);
            }

            $fields[] = $field;
        }

        return $fields;
    }

    private function getSampleData(array $sampleDataConfig): array
    {
        $sampleDataRows = [];
        foreach ($sampleDataConfig as $rowData) {
            $row = $this->sampleRowFactory->create();
            $fields = [];
            foreach ($rowData as $key => $value) {
                $field = $this->sampleFieldFactory->create();
                $field->setField($key);
                $field->setValue($value);

                $fields[] = $field;
            }
            $row->setValues($fields);
            $sampleDataRows[] = $row;
        }

        return $sampleDataRows;
    }

    private function getFieldsConfigInstance(string $className, array $arguments = []): FieldsClassInterface
    {
        $fieldsConfig = $this->configClassFactory->create(
            [
                'name' => $className,
                'arguments' => $this->argumentsPrepare->execute($arguments)
            ]
        );

        $fieldsConfigInstance = $this->objectFactory->createObject($fieldsConfig);
        if (!is_subclass_of($fieldsConfigInstance, FieldsClassInterface::class)) {
            throw new LocalizedException(
                __(
                    'Fields Class %1 doesn\'t implement %2 interface',
                    $className,
                    FieldsClassInterface::class
                )
            );
        }

        return $fieldsConfigInstance;
    }

    private function getRowValidation(array $rowValidationData = []): RowValidationInterface
    {
        /** @var RowValidationInterface $validation */
        $rowValidation = $this->rowValidationFactory->create();
        if (!empty($rowValidationData['includeBehaviors'])) {
            $rowValidation->setIncludeBehaviors($rowValidationData['includeBehaviors']);
        } elseif (!empty($rowValidationData['excludeBehaviors'])) {
            $rowValidation->setExcludeBehaviors($rowValidationData['excludeBehaviors']);
        }

        $configClass = $this->configClassFactory->create(
            [
                'baseType' => RowValidatorInterface::class,
                'name' => $rowValidationData['class']
            ]
        );
        $rowValidation->setConfigClass($configClass);

        return $rowValidation;
    }
}
