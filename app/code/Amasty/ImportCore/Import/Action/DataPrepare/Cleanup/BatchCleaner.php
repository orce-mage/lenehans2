<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Import\Action\DataPrepare\Cleanup;

use Amasty\ImportCore\Api\Action\CleanerInterface;
use Amasty\ImportCore\Api\ImportProcessInterface;
use Amasty\ImportCore\Model\Batch\BatchRepository;

class BatchCleaner implements CleanerInterface
{
    /**
     * @var BatchRepository
     */
    private $batchRepository;

    public function __construct(BatchRepository $batchRepository)
    {
        $this->batchRepository = $batchRepository;
    }

    /**
     * @inheritDoc
     */
    public function clean(ImportProcessInterface $importProcess): void
    {
        $this->batchRepository->cleanup($importProcess->getIdentity());
    }
}
