<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Processing;

use Amasty\ImportCore\Api\Config\ProfileConfigInterface;
use Amasty\ImportCore\Model\Process\ProcessRepository;
use Amasty\ImportCore\Model\Process\ResourceModel\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Shell;
use Symfony\Component\Process\PhpExecutableFinder;

class JobManager
{
    /**
     * @var CollectionFactory
     */
    private $processCollectionFactory;

    /**
     * @var JobWatcherFactory
     */
    private $jobWatcherFactory;

    /**
     * @var Shell
     */
    private $shell;

    /**
     * @var ProcessRepository
     */
    private $processRepository;

    /**
     * @var PhpExecutableFinder
     */
    private $phpExecutableFinder;

    public function __construct(
        CollectionFactory $processCollectionFactory,
        JobWatcherFactory $jobWatcherFactory,
        ProcessRepository $processRepository,
        PhpExecutableFinder $phpExecutableFinder,
        Shell $shell
    ) {
        $this->processCollectionFactory = $processCollectionFactory;
        $this->jobWatcherFactory = $jobWatcherFactory;
        $this->shell = $shell;
        $this->processRepository = $processRepository;
        $this->phpExecutableFinder = $phpExecutableFinder;
    }

    public function requestJob(ProfileConfigInterface $profileConfig, string $identity = null): JobWatcher
    {
        try {
            $matchingProcess = $this->processRepository->getByIdentity($identity);

            if ($matchingProcess->getPid() && $this->isPidAlive((int)$matchingProcess->getPid())) {
                return $this->jobWatcherFactory->create([
                    'processIdentity' => $identity
                ]);
            } else {
                $this->processRepository->delete($matchingProcess);
            }
        } catch (NoSuchEntityException $e) {
            ;// Nothiiiiiing
        }

        $identity = $this->processRepository->initiateProcess($profileConfig, $identity);

        $phpPath = $this->phpExecutableFinder->find() ?: 'php';

        $this->shell->execute(
            $phpPath . ' %s amasty:import:run-job %s > /dev/null &',
            [
                BP . '/bin/magento',
                $identity
            ]
        );

        return $this->jobWatcherFactory->create(['processIdentity' => $identity]);
    }

    public function watchJob(string $identity): JobWatcher
    {
        $matchingProcess = $this->processRepository->getByIdentity($identity);

        return $this->jobWatcherFactory->create([
            'processIdentity' => $matchingProcess->getIdentity(),
            'pid'             => (int)$matchingProcess->getPid()
        ]);
    }

    public function isPidAlive(int $pid): bool
    {
        //phpcs:ignore
        return false !== posix_getpgid($pid);
    }
}
