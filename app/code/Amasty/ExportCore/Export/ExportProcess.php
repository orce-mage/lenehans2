<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export;

use Amasty\ExportCore\Api\Config\EntityConfigInterface;
use Amasty\ExportCore\Api\Config\ProfileConfigInterface;
use Amasty\ExportCore\Api\ExportProcessExtensionInterface;
use Amasty\ExportCore\Api\ExportProcessExtensionInterfaceFactory;
use Amasty\ExportCore\Api\ExportProcessInterface;
use Amasty\ExportCore\Api\ExportResultInterface;
use Amasty\ExportCore\Api\ExportResultInterfaceFactory;
use Amasty\ExportCore\Model\Process\ProcessRepository;
use Amasty\ImportExportCore\Parallelization\JobManager;
use Magento\Framework\Data\Collection;

class ExportProcess implements ExportProcessInterface
{
    /**
     * @var ExportResultInterface
     */
    private $exportResult;

    /**
     * @var JobManager
     */
    private $jobManager;

    private $data = [];

    /**
     * @var ExportResultInterfaceFactory
     */
    private $exportResultFactory;

    /**
     * @var ProcessRepository
     */
    private $processRepository;

    /**
     * @var bool
     */
    private $isChildProcess = false;

    /**
     * @var string
     */
    private $identity;

    /**
     * @var bool
     */
    private $hasNextBatch = false;

    /**
     * @var int
     */
    private $currentBatchIndex = 0;

    /**
     * @var ProfileConfigInterface
     */
    private $profileConfig;

    /**
     * @var EntityConfigInterface
     */
    private $entityConfig;

    /**
     * @var Collection
     */
    private $collection;

    /**
     * @var ExportProcessExtensionInterface
     */
    private $extensionAttributes;

    /**
     * @var ExportProcessExtensionInterfaceFactory
     */
    private $extensionAttributesFactory;

    public function __construct(
        ExportProcessExtensionInterfaceFactory $extensionAttributesFactory,
        ProfileConfigInterface $profileConfig,
        EntityConfigInterface $entityConfig,
        ExportResultInterfaceFactory $exportResultFactory,
        ProcessRepository $processRepository,
        string $identity,
        ExportResultInterface $exportResult = null,
        JobManager $jobManager = null
    ) {
        $this->exportResult = $exportResult ?? $exportResultFactory->create();
        $this->jobManager = $jobManager;
        $this->exportResultFactory = $exportResultFactory;
        $this->processRepository = $processRepository;
        $this->identity = $identity;
        $this->profileConfig = $profileConfig;
        $this->entityConfig = $entityConfig;
        $this->extensionAttributesFactory = $extensionAttributesFactory;
        $this->initialize();
    }

    public function initialize(): ExportProcessInterface
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

    public function getExportResult(): ExportResultInterface
    {
        return $this->exportResult;
    }

    public function fork(): int
    {
        if (!$this->canFork()) {
            throw new \RuntimeException('Failed to fork: Multiprocessing is not enabled or supported');
        } elseif ($this->isChildProcess) {
            throw new \RuntimeException('Failed to fork: only parent process is allowed to fork');
        }

        $this->jobManager->waitForFreeSlot(); // Status updates here

        $pid = $this->jobManager->fork();
        if ($pid === 0) { // Child process
            // Reset counters and errors for all child processes
            /** @var ExportResultInterface $exportResult */
            $this->exportResult = $this->exportResultFactory->create();
            $this->isChildProcess = true;
        } elseif ($pid < 0) {
            throw new \RuntimeException('Failed to fork');
        }

        return $pid;
    }

    public function addCriticalMessage($message): ExportProcessInterface
    {
        $this->addMessage(ExportResultInterface::MESSAGE_CRITICAL, $message);

        return $this;
    }

    public function addErrorMessage($message): ExportProcessInterface
    {
        $this->addMessage(ExportResultInterface::MESSAGE_ERROR, $message);

        return $this;
    }

    public function addWarningMessage($message): ExportProcessInterface
    {
        $this->addMessage(ExportResultInterface::MESSAGE_WARNING, $message);

        return $this;
    }

    public function addInfoMessage($message): ExportProcessInterface
    {
        $this->addMessage(ExportResultInterface::MESSAGE_INFO, $message);

        return $this;
    }

    public function addDebugMessage($message): ExportProcessInterface
    {
        $this->addMessage(ExportResultInterface::MESSAGE_DEBUG, $message);

        return $this;
    }

    public function addMessage(int $type, $message): ExportProcessInterface
    {
        $this->getExportResult()->logMessage($type, $message);
        $this->processRepository->updateProcess($this);

        return $this;
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

    public function setData(array $data): ExportProcessInterface
    {
        $this->data = $data;

        return $this;
    }

    public function getIdentity(): ?string
    {
        return $this->identity;
    }

    public function setIsHasNextBatch(bool $hasNextBatch): ExportProcessInterface
    {
        $this->hasNextBatch = $hasNextBatch;

        return $this;
    }

    public function isHasNextBatch(): bool
    {
        return $this->hasNextBatch;
    }
    public function getCurrentBatchIndex(): int
    {
        return $this->currentBatchIndex;
    }

    public function setCurrentBatchIndex(int $index): ExportProcessInterface
    {
        $this->currentBatchIndex = $index;

        return $this;
    }

    public function setCollection(Collection $collection)
    {
        $this->collection = $collection;
    }

    public function getCollection(): ?Collection
    {
        return $this->collection;
    }

    public function getExtensionAttributes(): \Amasty\ExportCore\Api\ExportProcessExtensionInterface
    {
        if ($this->extensionAttributes === null) {
            $this->extensionAttributes = $this->extensionAttributesFactory->create();
        }

        return $this->extensionAttributes;
    }

    public function setExtensionAttributes(
        \Amasty\ExportCore\Api\ExportProcessExtensionInterface $extensionAttributes
    ): ExportProcessInterface {
        $this->extensionAttributes = $extensionAttributes;

        return $this;
    }
}
