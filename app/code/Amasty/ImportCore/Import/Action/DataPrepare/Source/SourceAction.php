<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\DataPrepare\Source;

use Amasty\Base\Model\Serializer;
use Amasty\ImportCore\Api\ActionInterface;
use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Api\Source\SourceReaderInterface;
use Amasty\ImportCore\Exception\JobDelegatedException;
use Amasty\ImportCore\Import\Source\SourceReaderAdapter;
use Magento\ImportExport\Model\ResourceModel\Helper;

class SourceAction implements ActionInterface
{
    const DEFAULT_BATCH_SIZE = 500;

    /**
     * @var SourceReaderAdapter
     */
    private $sourceReaderAdapter;

    /**
     * @var SourceReaderInterface
     */
    private $sourceReader;

    /**
     * @var SourceDataProcessor
     */
    private $sourceDataProcessor;

    /**
     * @var Helper
     */
    private $resourceHelper;

    /**
     * @var Serializer
     */
    private $serializer;

    public function __construct(
        SourceReaderAdapter $sourceReaderAdapter,
        SourceDataProcessor $sourceDataProcessor,
        Helper $resourceHelper,
        Serializer $serializer
    ) {
        $this->sourceReaderAdapter = $sourceReaderAdapter;
        $this->sourceDataProcessor = $sourceDataProcessor;
        $this->resourceHelper = $resourceHelper;
        $this->serializer = $serializer;
    }

    public function execute(ImportProcessInterface $importProcess): void
    {
        if (!$importProcess->getImportResult()->getTotalRecords()) {
            $importProcess->getImportResult()->setTotalRecords(
                $this->sourceReader->estimateRecordsCount()
            );
        }

        $batchSize = $importProcess->getProfileConfig()->getBatchSize() ?: self::DEFAULT_BATCH_SIZE;
        $importProcess->getProfileConfig()->setBatchSize($batchSize);
        $data = [];

        $hasNextBatch = true;
        $maxBatchSize = $this->resourceHelper->getMaxDataSize();
        for ($i = 0; $i < $batchSize; $i++) {
            if ($maxBatchSize <= strlen($this->serializer->serialize($data))) {
                $importProcess->getProfileConfig()->setOverflowBatchSize($i);

                break;
            }
            if ($row = $this->sourceReader->readRow()) {
                $data[] = $this->sourceDataProcessor->convertToImportProcessStructure(
                    $importProcess,
                    $row
                );
            } else {
                $hasNextBatch = false;
                break;
            }
        }

        if (empty($data)) {
            $importProcess->addErrorMessage((string)__('Empty data batch has been read.'));
        }

        $importProcess->setIsHasNextBatch($hasNextBatch);
        $importProcess->setData($data);

        if ($importProcess->canFork()) {
            if ($importProcess->fork() > 0) { // parent
                throw new JobDelegatedException(); // Break execution cycle and pass to the next batch
            }
        }

        if ($importProcess->getBatchNumber() == 1) {
            $importProcess->addInfoMessage((string)__('The data is being read.'));
        }
    }

    public function initialize(ImportProcessInterface $importProcess): void
    {
        $this->sourceReader = $this->sourceReaderAdapter->getReader(
            $importProcess->getProfileConfig()->getSourceType()
        );
        $this->sourceReader->initialize($importProcess);
    }
}
