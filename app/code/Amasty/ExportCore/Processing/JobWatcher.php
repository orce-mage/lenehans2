<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Processing;

use Amasty\ExportCore\Api\ExportResultInterface;
use Amasty\ExportCore\Api\ExportResultInterfaceFactory;
use Amasty\ExportCore\Model\Process\Process;
use Amasty\ExportCore\Model\Process\ProcessRepository;

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
     * @var ExportResultInterfaceFactory
     */
    private $exportResultFactory;

    public function __construct(
        ProcessRepository $processRepository,
        ExportResultInterfaceFactory $exportResultFactory,
        string $processIdentity = null
    ) {
        $this->processIdentity = $processIdentity;
        $this->processRepository = $processRepository;
        $this->exportResultFactory = $exportResultFactory;
    }

    /**
     * @return array
     */
    public function getJobState()
    {
        $process = $this->getProcess();
        if ($exportResultData = $process->getExportResult()) {
            /** @var ExportResultInterface $exportResult */
            $exportResult = $this->exportResultFactory->create();
            $exportResult->unserialize($exportResultData);
        } else {
            $exportResult = null;
        }

        return [$process, $exportResult];
    }

    protected function getProcess(): Process
    {
        return $this->processRepository->getByIdentity($this->processIdentity);
    }
}
