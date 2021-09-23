<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\DataPrepare;

use Amasty\ImportCore\Api\ActionInterface;
use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Model\Batch\Batch;
use Amasty\ImportCore\Model\Batch\BatchFactory;
use Amasty\ImportCore\Model\Batch\BatchRepository;

class DataSaveAction implements ActionInterface
{
    /**
     * @var BatchFactory
     */
    private $batchFactory;

    /**
     * @var BatchRepository
     */
    private $batchRepository;

    public function __construct(
        BatchFactory $batchFactory,
        BatchRepository $batchRepository
    ) {
        $this->batchFactory = $batchFactory;
        $this->batchRepository = $batchRepository;
    }

    public function execute(ImportProcessInterface $importProcess): void
    {
        $data = $importProcess->getData();
        if (count($data) == 0) {
            return;
        }
        /** @var Batch $batch */
        $batch = $this->batchFactory->create();

        $batch->setProcessIdentity($importProcess->getIdentity());
        $batch->setBatchData($data);

        $this->batchRepository->save($batch);

        $importResult = $importProcess->getImportResult();
        $behaviorResult = $importProcess->getProcessedEntityResult($importProcess->getEntityConfig()->getEntityCode());

        if ($behaviorResult) {
            $importResult->addBehaviorResult($behaviorResult);
        }
        $importResult->setRecordsProcessed(
            $importResult->getRecordsProcessed() + count($data)
        );

        if (!$importProcess->isHasNextBatch()) {
            $importProcess->addInfoMessage(__('The data preparation is completed.')->render());
        }
    }

    //phpcs:ignore Magento2.CodeAnalysis.EmptyBlock.DetectedFunction
    public function initialize(ImportProcessInterface $importProcess): void
    {
    }
}
