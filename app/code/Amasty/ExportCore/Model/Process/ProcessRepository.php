<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Model\Process;

use Amasty\ExportCore\Api\Config\ProfileConfigInterface;
use Amasty\ExportCore\Api\ExportProcessInterface;
use Amasty\ExportCore\Api\ExportResultInterface;
use Amasty\ExportCore\Api\ExportResultInterfaceFactory;
use Amasty\ImportExportCore\Utils\Serializer;
use Amasty\ExportCore\Model\Process\ResourceModel\CollectionFactory;
use Amasty\ExportCore\Model\Process\ResourceModel\Process as ProcessResource;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\NoSuchEntityException;

class ProcessRepository
{
    const IDENTITY = 'process_id';

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var ProcessResource
     */
    private $processResource;

    private $processes = [];

    /**
     * @var ProcessFactory
     */
    private $processFactory;

    /**
     * @var ExportResultInterfaceFactory
     */
    private $exportResultFactory;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        CollectionFactory $collectionFactory,
        Serializer $serializer,
        ProcessFactory $processFactory,
        ProcessResource $processResource,
        ExportResultInterfaceFactory $exportResultFactory
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->processResource = $processResource;
        $this->processFactory = $processFactory;
        $this->exportResultFactory = $exportResultFactory;
        $this->serializer = $serializer;
    }

    public function getByIdentity($identity): Process
    {
        if (!isset($this->processes[$identity])) {
            /** @var Process $process */
            $process = $this->processFactory->create();
            $this->processResource->load($process, $identity, Process::IDENTITY);
            if (!$process->getId()) {
                throw new NoSuchEntityException(__('Process with specified identity "%1" not found.', $identity));
            }

            $process->setProfileConfig(
                $this->serializer->unserialize(
                    $process->getProfileConfigSerialized(),
                    ProfileConfigInterface::class
                )
            );

            $this->processes[$identity] = $process;
        }

        return $this->processes[$identity];
    }

    public function delete(Process $process)
    {
        try {
            $this->processResource->delete($process);
            unset($this->processes[$process->getIdentity()]);
        } catch (\Exception $e) {
            if ($process->getId()) {
                throw new CouldNotDeleteException(
                    __(
                        'Unable to remove batch with ID %1. Error: %2',
                        [$process->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotDeleteException(__('Unable to remove batch. Error: %1', $e->getMessage()));
        }

        return true;
    }

    public function updateProcess(ExportProcessInterface $exportProcess)
    {
        try {
            $process = $this->getByIdentity($exportProcess->getIdentity());
        } catch (NoSuchEntityException $e) {
            return;
        }

        $process
            ->setStatus(Process::STATUS_RUNNING)
            ->setPid(getmypid())
            ->setExportResult($exportProcess->getExportResult()->serialize());
        $this->processResource->save($process);
    }

    public function finalizeProcess(ExportProcessInterface $exportProcess)
    {
        try {
            $process = $this->getByIdentity($exportProcess->getIdentity());
        } catch (NoSuchEntityException $e) {
            return;
        }

        $exportResult = $exportProcess->getExportResult();
        $process
            ->setStatus($exportResult->isFailed() ? Process::STATUS_FAILED : Process::STATUS_SUCCESS)
            ->setFinished(true)
            ->setPid(null)
            ->setExportResult($exportResult->serialize());
        $this->processResource->save($process);
    }

    public function markAsFailed(string $identity, string $errorMessage = null)
    {
        try {
            $process = $this->getByIdentity($identity);
        } catch (NoSuchEntityException $e) {
            return;
        }

        if ($process->getExportResult()) {
            /** @var ExportResultInterface $result */
            $result = $this->exportResultFactory->create();
            $result->unserialize($process->getExportResult());
            $result->terminateExport(true);
            $process->addCriticalMessage($errorMessage);
            $serializedResult = $result->serialize();
        } else {
            $serializedResult = null;
        }

        $process
            ->setStatus(Process::STATUS_FAILED)
            ->setFinished(true)
            ->setPid(null)
            ->setExportResult($serializedResult);
        $this->processResource->save($process);
    }

    public function initiateProcess(ProfileConfigInterface $profileConfig, string $identity = null): string
    {
        if (empty($identity)) {
            $identity = $this->generateNewIdentity();
        }

        /** @var Process $process */
        $process = $this->processFactory->create();
        $process
            ->setIdentity($identity)
            ->setProfileConfigSerialized(
                $this->serializer->serialize($profileConfig, ProfileConfigInterface::class)
            )->setStatus(Process::STATUS_PENDING)
            ->setPid(null)
            ->setEntityCode($profileConfig->getEntityCode())
            ->setExportResult(null);
        $this->processResource->save($process);

        return $identity;
    }

    public function generateNewIdentity()
    {
        return uniqid();
    }
}
