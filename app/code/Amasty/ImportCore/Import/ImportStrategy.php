<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import;

use Amasty\ImportCore\Api\ActionInterface;
use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Api\ImportProcessInterfaceFactory;
use Amasty\ImportCore\Api\ImportResultInterface;
use Amasty\ImportCore\Api\ImportResultInterfaceFactory;
use Amasty\ImportCore\Exception\JobDelegatedException;
use Amasty\ImportCore\Exception\JobDoneException;
use Amasty\ImportCore\Exception\JobTerminatedException;
use Amasty\ImportCore\Import\Config\EntityConfigProvider;
use Amasty\ImportCore\Import\Parallelization\ResultMerger;
use Amasty\ImportCore\Model\Process\ProcessRepository;
use Amasty\ImportExportCore\Parallelization\JobManager;
use Amasty\ImportExportCore\Parallelization\JobManagerFactory;
use Magento\Framework\App\State;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Psr\Log\LoggerInterface;

class ImportStrategy
{
    /**
     * @var array
     */
    private $actionGroups;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var ImportResultInterfaceFactory
     */
    private $importResultFactory;

    /**
     * @var ProcessRepository
     */
    private $processRepository;

    /**
     * @var ResultMerger
     */
    private $resultMerger;

    /**
     * @var JobManagerFactory
     */
    private $jobManagerFactory;

    /**
     * @var JobManager
     */
    private $jobManager;

    /**
     * @var ImportProcessInterfaceFactory
     */
    private $importProcessFactory;

    /**
     * @var EntityConfigProvider
     */
    private $entityConfigProvider;

    /**
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * @var State
     */
    private $appState;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        ImportResultInterfaceFactory $importResultFactory,
        ObjectManagerInterface $objectManager,
        ProcessRepository $processRepository,
        JobManagerFactory $jobManagerFactory,
        EntityConfigProvider $entityConfigProvider,
        ResultMerger $resultMerger,
        ImportProcessInterfaceFactory $importProcessFactory,
        ManagerInterface $eventManager,
        State $appState,
        LoggerInterface $logger,
        array $actionGroups = []
    ) {
        $this->objectManager = $objectManager;
        $this->importResultFactory = $importResultFactory;
        $this->processRepository = $processRepository;
        $this->resultMerger = $resultMerger;
        $this->jobManagerFactory = $jobManagerFactory;
        $this->actionGroups = $actionGroups;
        $this->importProcessFactory = $importProcessFactory;
        $this->entityConfigProvider = $entityConfigProvider;
        $this->eventManager = $eventManager;
        $this->appState = $appState;
        $this->logger = $logger;
    }

    public function run(ProfileConfigInterface $profileConfig, string $processIdentity): ImportResultInterface
    {
        if ($profileConfig->isUseMultiProcess() && JobManager::isAvailable()) {
            $importProcess = null;
            /** @var JobManager $jobManager */
            $this->jobManager = $this->jobManagerFactory->create(
                [
                    'jobDoneCallback' => function ($response) use (&$importProcess) {
                        $this->processFinalization($importProcess, $response);
                    },
                    'maxJobs' => $profileConfig->getMaxJobs()
                ]
            );
        } else {
            $this->jobManager = null;
        }

        /** @var ImportProcessInterface $importProcess */
        $importProcess = $this->importProcessFactory->create(
            [
                'identity' => $processIdentity,
                'profileConfig' => $profileConfig,
                'entityConfig' => $this->entityConfigProvider->get($profileConfig->getEntityCode()),
                'jobManager' => $this->jobManager
            ]
        );

        $this->eventManager->dispatch('amimport_before_run', ['importProcess' => $importProcess]);
        $this->registerErrorCatching($importProcess);

        $importResult = $importProcess->getImportResult();

        foreach ($this->getSortedActionGroups() as $groupName => $actionsGroup) {
            if (!$importResult->isImportTerminated()) {
                $importResult->resetProcessedRecords();
                $importResult->setStage($groupName);
                $this->processRepository->updateProcess($importProcess);
            }
            try {
                $actions = $this->prepareActions($actionsGroup, $importProcess);
                $this->processActions($actions, $importProcess);
            } catch (JobDoneException $e) {
                return $importResult;
            } catch (\Exception $e) {
                $importProcess->addCriticalMessage($e->getMessage());
            }
        }

        $this->finalizeProcess($importProcess);

        return $importResult;
    }

    /**
     * @codeCoverageIgnore Can't be covered by unit/integration tests due to pcntl_fork() usage
     * @param \Amasty\ImportCore\Api\ActionInterface[] $actions
     * @param ImportProcessInterface $importProcess
     */
    protected function processActions(
        array $actions,
        ImportProcessInterface $importProcess
    ) {
        $batchNumber = 0;
        do {
            $importProcess->setData([]);
            $importProcess->setIsHasNextBatch(false);
            $importProcess->setBatchNumber(++$batchNumber);

            try {
                foreach ($actions as $actionCode => $action) {
                    $action->execute($importProcess);
                    if ($importProcess->getImportResult()->isImportTerminated()) {
                        break 2;
                    }
                }
            } catch (JobTerminatedException $e) { // Job terminated by some child process. Entire process is failed
                return;
                // phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedCatch
            } catch (JobDelegatedException $e) {
                // Another actions from current group should be processed by a child process. Moving to the next batch
            }

            if ($importProcess->isChildProcess()) {
                $this->jobManager->reportToParent($importProcess->getImportResult()->serialize());
                throw new JobDoneException();
            }

            $this->processRepository->updateProcess($importProcess);
        } while ($importProcess->isHasNextBatch() && !$importProcess->getImportResult()->isImportTerminated());

        if ($this->jobManager) {
            try {
                $this->jobManager->waitForAllJobs(); // Status updates here
            } catch (JobTerminatedException $e) {
                return;
            }
        }
    }

    /**
     * @codeCoverageIgnore Can't be covered by unit/integration tests due to pcntl_fork() usage
     * @param ImportProcessInterface $importProcess
     * @param $response
     */
    protected function processFinalization(
        ImportProcessInterface $importProcess,
        $response
    ) {
        if ($response) {
            $childResult = $this->importResultFactory->create();
            $childResult->unserialize($response);
            $this->resultMerger->merge($importProcess->getImportResult(), $childResult);
            $this->processRepository->updateProcess($importProcess);

            if ($importProcess->getImportResult()->isFailed()) {
                throw new JobTerminatedException();
            }
        }
    }

    /**
     * @param array $actions
     *
     * @param ImportProcessInterface $importProcess
     * @return ActionInterface[]
     */
    public function prepareActions(array $actions, ImportProcessInterface $importProcess): array
    {
        $result = [];

        foreach ($actions as $actionCode => $action) {
            if (empty($action['class'])) {
                continue;
            }
            $class = $action['class'];
            if (!is_subclass_of($class, ActionInterface::class)) {
                throw new \RuntimeException('Wrong action class: "' . $class . '"');
            }

            if (!isset($action['sortOrder'])) {
                new \LogicException('"sortOrder" is not specified for action "' . $actionCode . '"');
            }
            $sortOrder = (int)$action['sortOrder'];
            if (!empty($action['entities']) && is_array($action['entities'])) {
                if (!in_array(
                    $importProcess->getProfileConfig()->getEntityCode(),
                    $action['entities']
                )) {
                    continue;
                }
            }
            unset($action['class']);
            unset($action['sortOrder']);
            unset($action['entities']);

            if (!isset($result[$sortOrder])) {
                $result[$sortOrder] = [];
            }

            /** @var ActionInterface $actionObject */
            $actionObject = $this->objectManager->create($class, $action);
            $actionObject->initialize($importProcess);
            $result[$sortOrder][$actionCode] = $actionObject;
        }
        if (empty($result)) {
            return [];
        }

        ksort($result);

        return array_merge(...$result);
    }

    public function getSortedActionGroups(): array
    {
        if (empty($this->actionGroups)) {
            return [];
        }

        $result = [];
        foreach ($this->actionGroups as $groupCode => $groupConfig) {
            if (empty($groupConfig['actions'])) {
                continue;
            }

            if (!isset($groupConfig['sortOrder'])) {
                new \LogicException('"sortOrder" is not specified for action group "' . $groupCode . '"');
            }

            $sortOrder = (int)$groupConfig['sortOrder'];
            if (!isset($result[$sortOrder])) {
                $result[$sortOrder] = [];
            }

            unset($groupConfig['sortOrder']);
            $result[$sortOrder][$groupCode] = $groupConfig['actions'];
        }

        if (empty($result)) {
            return [];
        }

        ksort($result);

        return array_merge(...$result);
    }

    public function registerErrorCatching(ImportProcessInterface $importProcess): ImportStrategy
    {
        //phpcs:ignore
        \register_shutdown_function(function () use ($importProcess) {
            if (error_get_last() === null || (error_get_last()['type'] ?? null) != 1) {
                return;
            }
            if ($this->appState->getMode() === State::MODE_PRODUCTION) {
                $this->logger->critical(error_get_last()['message']);
                $importProcess->addCriticalMessage(
                    (string)__('Something went wrong while import. Please review logs')
                );
            } else {
                $importProcess->addCriticalMessage(
                    error_get_last()['message'] ?? __('Something went wrong while import')
                );
            }
            $importProcess->getImportResult()->terminateImport(true);
            $this->finalizeProcess($importProcess);
        });

        return $this;
    }

    public function finalizeProcess(ImportProcessInterface $importProcess): ImportStrategy
    {
        $this->eventManager->dispatch('amimport_after_run', ['importProcess' => $importProcess]);

        $this->processRepository->finalizeProcess($importProcess);

        return $this;
    }
}
