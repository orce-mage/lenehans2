<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


declare(strict_types=1);

namespace Amasty\ExportCore\Export\Action\Export\Generation;

use Amasty\ExportCore\Api\ActionInterface;
use Amasty\ExportCore\Api\ChunkStorageInterface;
use Amasty\ExportCore\Api\ChunkStorageInterfaceFactory;
use Amasty\ExportCore\Api\ExportProcessInterface;

class SaveAction implements ActionInterface
{
    /**
     * @var ChunkStorageInterfaceFactory
     */
    private $chunkStorageFactory;

    /**
     * @var ChunkStorageInterface
     */
    private $chunkStorage;

    public function __construct(
        ChunkStorageInterfaceFactory $chunkStorageFactory
    ) {
        $this->chunkStorageFactory = $chunkStorageFactory;
    }

    public function execute(
        ExportProcessInterface $exportProcess
    ) {
        if (!$exportProcess->isChildProcess() && $exportProcess->getCurrentBatchIndex() === 1) {
            $exportProcess->addInfoMessage('Started generating the file.');
        }

        $this->chunkStorage->saveChunk(
            $exportProcess->getData(),
            $exportProcess->getCurrentBatchIndex()
        );
        $this->updateProgress($exportProcess);
    }

    protected function updateProgress(ExportProcessInterface $exportProcess)
    {
        $result = $exportProcess->getExportResult();
        $result->setRecordsProcessed(
            $result->getRecordsProcessed() + count($exportProcess->getData())
        );
    }

    public function initialize(ExportProcessInterface $exportProcess)
    {
        $this->chunkStorage = $this->chunkStorageFactory->create([
            'processIdentity' => $exportProcess->getIdentity()
        ]);
    }
}
