<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Api;

use Amasty\ImportCore\Api\Behavior\BehaviorResultInterface;
use Amasty\ImportCore\Api\Config\EntityConfigInterface;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;

interface ImportProcessInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    public function getIdentity(): ?string;

    public function getEntityConfig(): EntityConfigInterface;
    public function getProfileConfig(): ProfileConfigInterface;
    public function getImportResult(): ImportResultInterface;

    public function addProcessedEntityResult(
        string $entityCode,
        BehaviorResultInterface $result
    ): ImportProcessInterface;
    public function getProcessedEntityResult(string $entityCode = null);
    public function resetProcessedEntitiesResult(): ImportProcessInterface;

    public function addCriticalMessage(string $message): ImportProcessInterface;
    public function addErrorMessage(string $message): ImportProcessInterface;
    public function addWarningMessage(string $message): ImportProcessInterface;
    public function addInfoMessage(string $message): ImportProcessInterface;
    public function addDebugMessage(string $message): ImportProcessInterface;
    public function addMessage(int $type, string $message): ImportProcessInterface;
    public function addValidationError(
        string $message,
        int $rowNumber,
        string $entityName = null
    ): ImportProcessInterface;

    public function getData(): array;
    public function setData(array $data): ImportProcessInterface;

    public function canFork(): bool;
    public function fork(): int;
    public function isChildProcess(): bool;

    public function getErrorQuantity(): int;
    public function increaseErrorQuantity(): void;

    public function getBatchNumber(): int;
    public function setBatchNumber(int $batchNumber): void;
    public function setIsHasNextBatch(bool $hasNextBatch): void;
    public function isHasNextBatch(): bool;

    /**
     * Extension point for customizations to set extension attributes of ImportProcess class
     */
    public function initialize(): ImportProcessInterface;

    /**
     * @return \Amasty\ImportCore\Api\ImportProcessExtensionInterface
     */
    public function getExtensionAttributes(): \Amasty\ImportCore\Api\ImportProcessExtensionInterface;

    /**
     * @param \Amasty\ImportCore\Api\ImportProcessExtensionInterface $extensionAttributes
     *
     * @return void
     */
    public function setExtensionAttributes(
        \Amasty\ImportCore\Api\ImportProcessExtensionInterface $extensionAttributes
    ): void;
}
