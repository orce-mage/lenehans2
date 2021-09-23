<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\Import;

use Amasty\ImportCore\Api\ActionInterface;
use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Exception\JobDelegatedException;
use Amasty\ImportCore\Model\Batch\BatchRepository;

class DataLoadAction implements ActionInterface
{
    /**
     * @var BatchRepository
     */
    private $batchRepository;

    public function __construct(
        BatchRepository $batchRepository
    ) {
        $this->batchRepository = $batchRepository;
    }

    public function execute(ImportProcessInterface $importProcess): void
    {
        $batch = $this->batchRepository->fetchBatch($importProcess->getIdentity());

        if ($batch->getId()) {
            $importProcess->setData($batch->getBatchData());
            $importProcess->setIsHasNextBatch(true);
            if ($importProcess->canFork()) {
                if ($importProcess->fork() > 0) { // parent
                    throw new JobDelegatedException(); // Break execution cycle and pass to the next batch
                }
            }
        } else {
            $importProcess->getImportResult()->terminateImport();
            $importProcess->setIsHasNextBatch(false);
        }
    }

    //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    public function initialize(ImportProcessInterface $importProcess): void
    {
    }
}
