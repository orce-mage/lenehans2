<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Processing;

use Amasty\ImportCore\Api\ImportResultInterface;
use Amasty\ImportCore\Api\ImportResultInterfaceFactory;
use Amasty\ImportCore\Model\Process\Process;
use Amasty\ImportCore\Model\Process\ProcessRepository;

class JobWatcher
{
    /**
     * @var int
     */
    protected $processIdentity;

    /**
     * @var ProcessRepository
     */
    private $processRepository;

    /**
     * @var ImportResultInterfaceFactory
     */
    private $importResultFactory;

    public function __construct(
        ProcessRepository $processRepository,
        ImportResultInterfaceFactory $importResultFactory,
        string $processIdentity = null
    ) {
        $this->processIdentity = $processIdentity;
        $this->processRepository = $processRepository;
        $this->importResultFactory = $importResultFactory;
    }

    /**
     * @return array
     */
    public function getJobState()
    {
        $process = $this->getProcess();
        if ($importResultData = $process->getImportResult()) {
            /** @var ImportResultInterface $importResult */
            $importResult = $this->importResultFactory->create();
            $importResult->unserialize($importResultData);
        } else {
            $importResult = null;
        }

        return [$process, $importResult];
    }

    protected function getProcess(): Process
    {
        return $this->processRepository->getByIdentity($this->processIdentity);
    }
}
