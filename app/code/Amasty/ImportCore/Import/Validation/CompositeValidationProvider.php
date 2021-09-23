<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */

declare(strict_types=1);

namespace Amasty\ImportCore\Import\Validation;

use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Api\Validation\ValidationProviderInterface;
use Magento\Framework\ObjectManagerInterface;

class CompositeValidationProvider implements ValidationProviderInterface
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ValidationProviderInterface
     */
    private $defaultProvider;

    /**
     * @var array
     */
    private $providersByEntityCode = [];

    /**
     * @var ValidationProviderInterface[][]
     */
    private $providerInstancesByEntityCode = [];

    public function __construct(
        ObjectManagerInterface $objectManager,
        ValidationProviderInterface $defaultProvider,
        array $providersByEntityCode = []
    ) {
        $this->objectManager = $objectManager;
        $this->defaultProvider = $defaultProvider;
        $this->providersByEntityCode = $providersByEntityCode;
    }

    public function getFieldValidators(
        ImportProcessInterface $importProcess,
        array &$validatorsForCollect = []
    ): array {
        $this->collectValidators($importProcess, $validatorsForCollect, 'getFieldValidators');

        return $validatorsForCollect;
    }

    public function getRowValidators(
        ImportProcessInterface $importProcess,
        array &$validatorsForCollect = []
    ): array {
        $this->collectValidators($importProcess, $validatorsForCollect, 'getRowValidators');

        return $validatorsForCollect;
    }

    public function getRelationValidators(
        ImportProcessInterface $importProcess,
        array &$validatorsForCollect = []
    ): array {
        $this->collectValidators($importProcess, $validatorsForCollect, 'getRelationValidators');

        return $validatorsForCollect;
    }

    /**
     * Collect validator instances
     *
     * @param ImportProcessInterface $importProcess
     * @param array $validatorsForCollect
     * @param string $collectMethodName
     * @return void
     */
    private function collectValidators(
        ImportProcessInterface $importProcess,
        array &$validatorsForCollect,
        string $collectMethodName
    ) {
        $entityCode = $importProcess->getProfileConfig()
            ->getEntitiesConfig()
            ->getEntityCode();
        foreach ($this->getProviders($entityCode) as $provider) {
            $provider->$collectMethodName($importProcess, $validatorsForCollect);
        }
    }

    /**
     * Get validation provider instances by entity code
     *
     * @param string $entityCode
     * @return ValidationProviderInterface[]
     */
    private function getProviders($entityCode)
    {
        if (!isset($this->providerInstancesByEntityCode[$entityCode])) {
            $this->providerInstancesByEntityCode[$entityCode] = [$this->defaultProvider];
            if (isset($this->providersByEntityCode[$entityCode])) {
                $providerInstance = $this->objectManager->create($this->providersByEntityCode[$entityCode]);
                if (!$providerInstance instanceof ValidationProviderInterface) {
                    throw new \InvalidArgumentException(
                        get_class($providerInstance) . ' doesn\'t implement ' . ValidationProviderInterface::class
                    );
                }

                $this->providerInstancesByEntityCode[$entityCode][] = $providerInstance;
            }
        }

        return $this->providerInstancesByEntityCode[$entityCode];
    }
}
