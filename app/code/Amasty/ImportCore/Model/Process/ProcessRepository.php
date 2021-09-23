<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Model\Process;

use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Api\ImportResultInterface;
use Amasty\ImportCore\Api\ImportResultInterfaceFactory;
use Amasty\ImportExportCore\Utils\Serializer;
use Amasty\ImportCore\Model\Process\ResourceModel\CollectionFactory;
use Amasty\ImportCore\Model\Process\ResourceModel\Process as ProcessResource;
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
     * @var Serializer
     */
    private $serializer;

    /**
     * @var ImportResultInterfaceFactory
     */
    private $importResultFactory;

    public function __construct(
        CollectionFactory $collectionFactory,
        ProcessFactory $processFactory,
        ProcessResource $processResource,
        ImportResultInterfaceFactory $importResultFactory,
        Serializer $serializer
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->processResource = $processResource;
        $this->processFactory = $processFactory;
        $this->serializer = $serializer;
        $this->importResultFactory = $importResultFactory;
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

    public function updateProcess(ImportProcessInterface $importProcess)
    {
        try {
            $process = $this->getByIdentity($importProcess->getIdentity());
        } catch (NoSuchEntityException $e) {
            return;
        }

        $process
            ->setStatus(Process::STATUS_RUNNING)
            ->setPid(getmypid())
            ->setImportResult($importProcess->getImportResult()->serialize());
        $this->processResource->save($process);
    }

    public function finalizeProcess(ImportProcessInterface $importProcess)
    {
        try {
            $process = $this->getByIdentity($importProcess->getIdentity());
        } catch (NoSuchEntityException $e) {
            return;
        }
        $importResult = $importProcess->getImportResult();
        $process
            ->setStatus($importResult->isFailed() ? Process::STATUS_FAILED : Process::STATUS_SUCCESS)
            ->setFinished(true)
            ->setPid(null)
            ->setImportResult($importResult->serialize());
        $this->processResource->save($process);
    }

    public function markAsFailed(string $identity, string $errorMessage = null)
    {
        try {
            $process = $this->getByIdentity($identity);
        } catch (NoSuchEntityException $e) {
            return;
        }

        if ($process->getImportResult()) {
            /** @var ImportResultInterface $result */
            $result = $this->importResultFactory->create();
            $result->unserialize($process->getImportResult());
            $result->terminateImport(true);
            $result->addCriticalMessage($errorMessage);
            $serializedResult = $result->serialize();
        } else {
            $serializedResult = null;
        }

        $process
            ->setStatus(Process::STATUS_FAILED)
            ->setFinished(true)
            ->setPid(null)
            ->setImportResult($serializedResult);
        $this->processResource->save($process);
    }

    public function initiateProcess(ProfileConfigInterface $profileConfig, string $identity = null): string
    {
        if (empty($identity)) {
            $identity = $this->generateNewIdentity();
        }

        /** @var Process $process */
        $process = $this->processFactory->create();
        $process->setIdentity($identity)
            ->setProfileConfigSerialized(
                $this->serializer->serialize($profileConfig, ProfileConfigInterface::class)
            )->setStatus(Process::STATUS_PENDING)
            ->setPid(null)
            ->setEntityCode($profileConfig->getEntityCode())
            ->setImportResult(null);
        $this->processResource->save($process);

        return $identity;
    }

    public function generateNewIdentity()
    {
        return uniqid();
    }
}
