<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Validation;

use Amasty\ImportCore\Api\Config\Entity\Field\ValidationInterfaceFactory;
use Amasty\ImportCore\Api\Validation\FieldValidatorInterface;
use Amasty\ImportCore\Import\Config\Entity\Field\Validation;
use Amasty\ImportCore\Import\Validation\ValueValidator\Integer;
use Amasty\ImportCore\Import\Validation\ValueValidator\Number;
use Amasty\ImportExportCore\Api\Config\ConfigClass\ConfigClassInterfaceFactory;
use Magento\Eav\Api\Data\AttributeInterface;

class FieldValidationResolver
{
    const NAME = 'name';
    const ERROR = 'error';

    /**
     * @var ConfigClassInterfaceFactory
     */
    private $configFactory;

    /**
     * @var ValidationInterfaceFactory
     */
    private $validationFactory;

    /**
     * Storage for created validations
     *
     * @var array
     */
    private $validationStorage = [];

    public function __construct(
        ConfigClassInterfaceFactory $configFactory,
        ValidationInterfaceFactory $validationFactory
    ) {
        $this->configFactory = $configFactory;
        $this->validationFactory = $validationFactory;
    }

    public function resolveByDbColumnInfo(array $fieldDetails, array $existingValidations): array
    {
        $newValidations = $this->getValidationsByDbColInfo($fieldDetails);

        return $this->mergeValidations($newValidations, $existingValidations);
    }

    private function getValidationsByDbColInfo(array $fieldDetails): array
    {
        $validationsData = [];
        switch ($fieldDetails['DATA_TYPE']) {
            case 'smallint':
            case 'int':
                $validationsData[] = [
                    self::NAME => Integer::class,
                    self::ERROR => __('Incorrect integer value for column %1')->render()
                ];
                break;
            case 'decimal':
                $validationsData[] = [
                    self::NAME => Number::class,
                    self::ERROR => __('Non-numeric value for column %1 found')->render()
                ];
                break;
        }

        return $this->getValidations($validationsData);
    }

    public function resolveByEavAttribute(AttributeInterface $attribute, array $existingValidations): array
    {
        $newValidations = $this->getValidationsByByEavAttr($attribute);

        return $this->mergeValidations($newValidations, $existingValidations);
    }

    private function getValidationsByByEavAttr(AttributeInterface $attribute): array
    {
        $validationsData = [];
        switch ($attribute->getBackendType()) {
            case 'int':
            case 'integer':
                $validationsData[] = [
                    self::NAME => Integer::class,
                    self::ERROR => __('Incorrect integer value for column %1')->render()
                ];
                break;
            case 'decimal':
                $validationsData[] = [
                    self::NAME => Number::class,
                    self::ERROR => __('Non-numeric value for column %1 found')->render()
                ];
                break;
        }

        return $this->getValidations($validationsData);
    }

    private function getValidations(array $validationsData): array
    {
        $validations = [];
        foreach ($validationsData as $validationData) {
            if (!isset($this->validationStorage[$validationData[self::NAME]])) {
                $validation = $this->validationFactory->create();
                $config = $this->configFactory->create([
                    'baseType' => FieldValidatorInterface::class,
                    'name' => $validationData[self::NAME]
                ]);
                $validation->setConfigClass($config);
                $validation->setError($validationData[self::ERROR]);
                $this->validationStorage[$validationData[self::NAME]] = $validation;

            }
            $validations[] = $this->validationStorage[$validationData[self::NAME]];
        }

        return $validations;
    }

    private function mergeValidations(array $newValidations, array $existingValidations): array
    {
        $existingValidationNames = $this->getValidationNames($existingValidations);

        /** @var Validation $validation */
        foreach ($newValidations as $validation) {
            if (in_array($validation->getConfigClass()->getName(), $existingValidationNames)) {
                continue;
            }
            $existingValidations[] = $validation;
        }

        return $existingValidations;
    }

    private function getValidationNames(array $validations): array
    {
        $names = [];

        foreach ($validations as $validation) {
            $names[] = $validation->getConfigClass()->getName();
        }

        return $names;
    }
}
