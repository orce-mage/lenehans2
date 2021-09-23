<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import;

use Amasty\ImportCore\Api\Behavior\BehaviorResultInterface;
use Amasty\ImportCore\Api\Config\EntityConfigInterface;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Api\ImportProcessExtensionInterfaceFactory;
use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Api\ImportResultInterface;
use Amasty\ImportCore\Api\ImportResultInterfaceFactory;
use Amasty\ImportExportCore\Parallelization\JobManager;

class ImportProcess implements ImportProcessInterface
{
    /**
     * @var ImportResultInterface
     */
    private $importResult;

    /**
     * @var \Amasty\ImportCore\Api\ImportProcessExtensionInterface
     */
    private $extensionAttributes;

    /**
     * @var JobManager
     */
    private $jobManager;

    private $data = [];

    /**
     * @var array|null
     */
    private $processedEntitiesResult;

    /**
     * @var ImportResultInterfaceFactory
     */
    private $importResultFactory;

    /**
     * @var bool
     */
    private $isChildProcess = false;

    /**
     * @var string
     */
    private $identity;

    /**
     * @var int
     */
    private $errorQuantity = 0;

    /**
     * @var int
     */
    private $batchNumber = 0;

    /**
     * @var bool
     */
    private $hasNextBatch = false;

    /**
     * @var ProfileConfigInterface
     */
    private $profileConfig;

    /**
     * @var EntityConfigInterface
     */
    private $entityConfig;

    /**
     * @var ImportProcessExtensionInterfaceFactory
     */
    private $extensionAttributesFactory;

    public function __construct(
        ImportProcessExtensionInterfaceFactory $extensionAttributesFactory,
        ProfileConfigInterface $profileConfig,
        EntityConfigInterface $entityConfig,
        ImportResultInterfaceFactory $importResultFactory,
        string $identity,
        ImportResultInterface $importResult = null,
        JobManager $jobManager = null
    ) {
        $this->importResult = $importResult ?? $importResultFactory->create();
        $this->jobManager = $jobManager;
        $this->importResultFactory = $importResultFactory;
        $this->identity = $identity;
        $this->profileConfig = $profileConfig;
        $this->entityConfig = $entityConfig;
        $this->extensionAttributesFactory = $extensionAttributesFactory;
        $this->initialize();
    }

    //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    public function initialize(): ImportProcessInterface
    {
        return $this;
    }

    public function getProfileConfig(): ProfileConfigInterface
    {
        return $this->profileConfig;
    }

    public function getEntityConfig(): EntityConfigInterface
    {
        return $this->entityConfig;
    }

    public function getImportResult(): ImportResultInterface
    {
        return $this->importResult;
    }

    public function addProcessedEntityResult(
        string $entityCode,
        BehaviorResultInterface $result
    ): ImportProcessInterface {
        $this->processedEntitiesResult[$entityCode] = $result;

        return $this;
    }

    /**
     * @param string|null $entityCode
     * @return BehaviorResultInterface[]|null
     */
    public function getProcessedEntityResult(string $entityCode = null)
    {
        if ($entityCode !== null) {
            return $this->processedEntitiesResult[$entityCode] ?? null;
        }

        return $this->processedEntitiesResult;
    }

    public function resetProcessedEntitiesResult(): ImportProcessInterface
    {
        $this->processedEntitiesResult = null;

        return $this;
    }

    public function addCriticalMessage(string $message): ImportProcessInterface
    {
        $this->addMessage(ImportResultInterface::MESSAGE_CRITICAL, $message);

        return $this;
    }

    public function addErrorMessage(string $message): ImportProcessInterface
    {
        $this->addMessage(ImportResultInterface::MESSAGE_ERROR, $message);

        return $this;
    }

    public function addWarningMessage(string $message): ImportProcessInterface
    {
        $this->addMessage(ImportResultInterface::MESSAGE_WARNING, $message);

        return $this;
    }

    public function addInfoMessage(string $message): ImportProcessInterface
    {
        $this->addMessage(ImportResultInterface::MESSAGE_INFO, $message);

        return $this;
    }

    public function addDebugMessage(string $message): ImportProcessInterface
    {
        $this->addMessage(ImportResultInterface::MESSAGE_DEBUG, $message);

        return $this;
    }

    public function addMessage(int $type, string $message): ImportProcessInterface
    {
        $this->getImportResult()->logMessage($type, $message);

        return $this;
    }

    public function addValidationError(
        string $message,
        int $rowNumber,
        string $entityName = null
    ): ImportProcessInterface {
        $this->getImportResult()->logValidationMessage($message, $rowNumber, $entityName);

        return $this;
    }

    public function fork(): int
    {
        if (!$this->canFork()) {
            throw new \RuntimeException('Failed to fork: Multiprocessing is not enabled or supported');
        } elseif ($this->isChildProcess) {
            throw new \RuntimeException('Failed to fork: Only parent process is allowed to fork');
        }

        $this->jobManager->waitForFreeSlot(); // Status updates here

        $pid = $this->jobManager->fork();
        if ($pid === 0) { // Child process
            // Reset counters and errors for all child processes
            /** @var ImportResultInterface $importResult */
            $this->importResult = $this->importResultFactory->create();
            $this->isChildProcess = true;
        } elseif ($pid < 0) {
            throw new \RuntimeException('Failed to fork');
        }

        return $pid;
    }

    public function canFork(): bool
    {
        return $this->jobManager !== null;
    }

    public function isChildProcess(): bool
    {
        return $this->isChildProcess;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function setData(array $data): ImportProcessInterface
    {
        $this->data = $data;

        return $this;
    }

    public function getIdentity(): ?string
    {
        return $this->identity;
    }

    public function getErrorQuantity(): int
    {
        return $this->errorQuantity;
    }

    public function increaseErrorQuantity(): void
    {
        $this->errorQuantity++;
    }

    public function getBatchNumber(): int
    {
        return $this->batchNumber;
    }

    public function setBatchNumber(int $batchNumber): void
    {
        $this->batchNumber = $batchNumber;
    }

    public function setIsHasNextBatch(bool $hasNextBatch): void
    {
        $this->hasNextBatch = $hasNextBatch;
    }

    public function isHasNextBatch(): bool
    {
        return $this->hasNextBatch;
    }

    public function getExtensionAttributes(): \Amasty\ImportCore\Api\ImportProcessExtensionInterface
    {
        if ($this->extensionAttributes === null) {
            $this->extensionAttributes = $this->extensionAttributesFactory->create();
        }

        return $this->extensionAttributes;
    }

    public function setExtensionAttributes(
        \Amasty\ImportCore\Api\ImportProcessExtensionInterface $extensionAttributes
    ): void {
        $this->extensionAttributes = $extensionAttributes;
    }
}
