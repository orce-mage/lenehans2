<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Api;

use Amasty\ExportCore\Api\Config\EntityConfigInterface;
use Amasty\ExportCore\Api\Config\ProfileConfigInterface;
use Magento\Framework\Api\ExtensibleDataInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\Phrase;

interface ExportProcessInterface extends ExtensibleDataInterface
{
    public function getIdentity(): ?string;

    public function getProfileConfig(): ProfileConfigInterface;
    public function getEntityConfig(): EntityConfigInterface;
    public function getExportResult(): ExportResultInterface;

    public function addCriticalMessage($message): \Amasty\ExportCore\Api\ExportProcessInterface;
    public function addErrorMessage($message): \Amasty\ExportCore\Api\ExportProcessInterface;
    public function addWarningMessage($message): \Amasty\ExportCore\Api\ExportProcessInterface;
    public function addInfoMessage($message): \Amasty\ExportCore\Api\ExportProcessInterface;
    public function addDebugMessage($message): \Amasty\ExportCore\Api\ExportProcessInterface;
    public function addMessage(int $type, $message): \Amasty\ExportCore\Api\ExportProcessInterface;

    /**
     * Each element represents an entity.
     * @return array[]
     */
    public function getData(): array;
    public function setData(array $data): \Amasty\ExportCore\Api\ExportProcessInterface;

    public function canFork(): bool;
    public function fork(): int;
    public function isChildProcess(): bool;

    public function setCollection(Collection $collection);
    public function getCollection(): ?Collection;

    public function setIsHasNextBatch(bool $hasNextBatch): \Amasty\ExportCore\Api\ExportProcessInterface;
    public function isHasNextBatch(): bool;
    public function getCurrentBatchIndex(): int;
    public function setCurrentBatchIndex(int $index): \Amasty\ExportCore\Api\ExportProcessInterface;

    /**
     * Extension point for customizations to set extension attributes of ExportProcess class
     *
     * @return \Amasty\ExportCore\Api\ExportProcessInterface
     */
    public function initialize(): \Amasty\ExportCore\Api\ExportProcessInterface;

    /**
     * @return \Amasty\ExportCore\Api\ExportProcessExtensionInterface
     */
    public function getExtensionAttributes(): \Amasty\ExportCore\Api\ExportProcessExtensionInterface;

    /**
     * @param \Amasty\ExportCore\Api\ExportProcessExtensionInterface $extensionAttributes
     *
     * @return \Amasty\ExportCore\Api\ExportProcessInterface
     */
    public function setExtensionAttributes(
        \Amasty\ExportCore\Api\ExportProcessExtensionInterface $extensionAttributes
    ): \Amasty\ExportCore\Api\ExportProcessInterface;
}
