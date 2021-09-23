<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\DataPrepare\Validation;

use Amasty\ImportCore\Api\Config\Profile\EntitiesConfigInterface;
use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Api\Validation\ValidationProviderInterface;
use Amasty\ImportCore\Import\Config\EntityConfigProvider;
use Amasty\ImportCore\Import\Config\RelationConfigProvider;
use Amasty\ImportExportCore\Config\ConfigClass\Factory as ConfigClassFactory;

class ValidationProvider implements ValidationProviderInterface
{
    /**
     * @var FieldValidatorFactory
     */
    private $fieldValidatorFactory;

    /**
     * @var ConfigClassFactory
     */
    private $configClassFactory;

    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    /**
     * @var RelationConfigProvider
     */
    private $relationConfigProvider;

    public function __construct(
        FieldValidatorFactory $fieldValidatorFactory,
        ConfigClassFactory $configClassFactory,
        EntityConfigProvider $entityConfigProvider,
        RelationConfigProvider $relationConfigProvider
    ) {
        $this->fieldValidatorFactory = $fieldValidatorFactory;
        $this->configClassFactory = $configClassFactory;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->relationConfigProvider = $relationConfigProvider;
    }

    public function getFieldValidators(
        ImportProcessInterface $importProcess,
        array &$validatorsForCollect = []
    ): array {
        $profileEntitiesConfig = $importProcess->getProfileConfig()->getEntitiesConfig();
        $this->markRoots($profileEntitiesConfig);
        $this->collectFieldValidators($profileEntitiesConfig, $validatorsForCollect);

        return $validatorsForCollect;
    }

    /**
     * Collect field validators
     *
     * @param EntitiesConfigInterface $profileEntitiesConfig
     * @param array $validators
     * @return void
     */
    private function collectFieldValidators(
        EntitiesConfigInterface $profileEntitiesConfig,
        array &$validators
    ) {
        $entityCode = $profileEntitiesConfig->getEntityCode();
        $entityConfig = $this->entityConfigProvider->get($entityCode);

        $fieldConfig = $entityConfig->getFieldsConfig()->getFields();
        if ($fieldConfig) {
            $currentBehavior = $profileEntitiesConfig->getBehavior();
            foreach ($fieldConfig as $field) {
                if (empty($field->getValidations())) {
                    continue;
                }

                foreach ($field->getValidations() as $validationConfig) {
                    $includeBehaviors = $validationConfig->getIncludeBehaviors();
                    $excludeBehaviors = $validationConfig->getExcludeBehaviors();
                    if (!$this->isBehaviorAllowed($currentBehavior, $includeBehaviors, $excludeBehaviors)) {
                        continue;
                    }
                    if (!$validationConfig->getConfigClass()) {
                        continue;
                    }
                    if ($validationConfig->getIsApplyToRootEntityOnly() && !$profileEntitiesConfig->getIsRoot()) {
                        continue;
                    }

                    /** @var FieldValidator $validator */
                    $validator = $this->configClassFactory->createObject(
                        $validationConfig->getConfigClass()
                    );

                    $validators[$entityCode][$field->getName()][] = $this->fieldValidatorFactory->create(
                        [
                            'validator' => $validator,
                            'errorMessage' => $validationConfig->getError()
                        ]
                    );
                }
            }
        }

        foreach ($profileEntitiesConfig->getSubEntitiesConfig() as $subEntitiesConfig) {
            $this->collectFieldValidators(
                $subEntitiesConfig,
                $validators
            );
        }
    }

    public function getRowValidators(
        ImportProcessInterface $importProcess,
        array &$validatorsForCollect = []
    ): array {
        $profileEntitiesConfig = $importProcess->getProfileConfig()->getEntitiesConfig();
        $this->collectRowValidators($profileEntitiesConfig, $validatorsForCollect);

        return $validatorsForCollect;
    }

    /**
     * Collect row validators
     *
     * @param EntitiesConfigInterface $profileEntitiesConfig
     * @param array $validators
     * @return void
     */
    private function collectRowValidators(
        EntitiesConfigInterface $profileEntitiesConfig,
        array &$validators
    ) {
        $entityCode = $profileEntitiesConfig->getEntityCode();
        $entityConfig = $this->entityConfigProvider->get($entityCode);

        $rowValidation = $entityConfig->getFieldsConfig()->getRowValidation();
        $currentBehavior = $profileEntitiesConfig->getBehavior();
        if ($rowValidation
            && $this->isBehaviorAllowed(
                $currentBehavior,
                $rowValidation->getIncludeBehaviors(),
                $rowValidation->getExcludeBehaviors()
            )
        ) {
            $configClass = $rowValidation->getConfigClass();
            $validators[$entityCode] = $this->configClassFactory->createObject($configClass);
        }

        foreach ($profileEntitiesConfig->getSubEntitiesConfig() as $subEntitiesConfig) {
            $this->collectRowValidators(
                $subEntitiesConfig,
                $validators
            );
        }
    }

    public function getRelationValidators(
        ImportProcessInterface $importProcess,
        array &$validatorsForCollect = []
    ): array {
        $profileEntitiesConfig = $importProcess->getProfileConfig()->getEntitiesConfig();
        if (!empty($profileEntitiesConfig->getFields())) {
            $this->collectRelationValidators($profileEntitiesConfig, $validatorsForCollect);
        }

        return $validatorsForCollect;
    }

    /**
     * Collect relation validators
     *
     * @param EntitiesConfigInterface $profileEntitiesConfig
     * @param array $validators
     * @return void
     */
    private function collectRelationValidators(
        EntitiesConfigInterface $profileEntitiesConfig,
        array &$validators
    ) {
        $entityCode = $profileEntitiesConfig->getEntityCode();
        $currentBehavior = $profileEntitiesConfig->getBehavior();

        $subEntityConfigs = $profileEntitiesConfig->getSubEntitiesConfig();
        foreach ($subEntityConfigs as $subEntityConfig) {
            $relationConfig = $this->relationConfigProvider->getExact(
                $entityCode,
                $subEntityConfig->getEntityCode()
            );

            if ($relationConfig) {
                $validation = $relationConfig->getValidation();
                if ($validation
                    && $this->isBehaviorAllowed(
                        $currentBehavior,
                        $validation->getIncludeBehaviors(),
                        $validation->getExcludeBehaviors()
                    )
                ) {
                    $subEntityCode = $relationConfig->getChildEntityCode();
                    $validators[$entityCode][$subEntityCode] = $this->configClassFactory->createObject(
                        $validation->getConfigClass()
                    );
                }
            }
        }

        foreach ($subEntityConfigs as $subEntitiesConfig) {
            $this->collectRelationValidators(
                $subEntitiesConfig,
                $validators
            );
        }
    }

    /**
     * Checks if specified behavior is allowed
     *
     * @param string $behavior
     * @param array $includeBehaviors
     * @param array $excludeBehaviors
     * @return bool
     */
    private function isBehaviorAllowed(
        string $behavior,
        array $includeBehaviors,
        array $excludeBehaviors
    ): bool {
        if ((!empty($includeBehaviors) && !in_array($behavior, $includeBehaviors))
            || (!empty($excludeBehaviors) && in_array($behavior, $excludeBehaviors))
        ) {
            return false;
        }

        return true;
    }

    /**
     * Sets root flags to entities config instances.
     *
     * Entity considered as root if data of it's parent entity isn't read in specified configuration
     * or it hasn't parent entity itself.
     * Profile entities config may have several root entities depends on Fields Configuration
     *
     * @param EntitiesConfigInterface $config
     * @param EntitiesConfigInterface|null $parentConfig
     */
    private function markRoots(EntitiesConfigInterface $config, ?EntitiesConfigInterface $parentConfig = null)
    {
        $isRoot = false;
        if ($parentConfig === null && count($config->getFields())) {
            $isRoot = true;
        }
        if ($parentConfig && !count($parentConfig->getFields()) && count($config->getFields())) {
            $isRoot = true;
        }

        $config->setIsRoot($isRoot);

        foreach ($config->getSubEntitiesConfig() as $subConfig) {
            $this->markRoots($subConfig, $config);
        }
    }
}
