<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


declare(strict_types=1);

namespace Amasty\ImportCore\Cron;

use Amasty\ImportCore\Import\Utils\CleanUpByProcessIdentity;
use Amasty\ImportCore\Model\Batch\Batch;
use Amasty\ImportCore\Model\Batch\ResourceModel\CollectionFactory;
use Amasty\ImportCore\Model\FileUploadMap\FileUploadMap;
use Amasty\ImportCore\Model\FileUploadMap\ResourceModel\CollectionFactory as FileUploadMapCollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Psr\Log\LoggerInterface;

class CleanupBatches
{
    /**
     * @var string
     */
    private $interval;

    /**
     * @var CleanUpByProcessIdentity
     */
    private $cleanUpByProcessIdentity;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var FileUploadMapCollectionFactory
     */
    private $fileUploadMapCollectionFactory;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        CleanUpByProcessIdentity $cleanUpByProcessIdentity,
        CollectionFactory $collectionFactory,
        FileUploadMapCollectionFactory $fileUploadMapCollectionFactory,
        Filesystem $filesystem,
        LoggerInterface $logger,
        string $interval = '-1 day'
    ) {
        $this->interval = $interval;
        $this->cleanUpByProcessIdentity = $cleanUpByProcessIdentity;
        $this->collectionFactory = $collectionFactory;
        $this->fileUploadMapCollectionFactory = $fileUploadMapCollectionFactory;
        $this->filesystem = $filesystem;
        $this->logger = $logger;
    }

    public function execute()
    {
        $deadLine = new \DateTime('now', new \DateTimeZone('utc'));
        $deadLine->modify($this->interval);
        $collection = $this->collectionFactory->create();
        $collection->addFieldToSelect(Batch::PROCESS_IDENTITY)
            ->addFieldToFilter(Batch::CREATED_AT, ['lt' => $deadLine->format('Y-m-d H:i:s')]);
        foreach ($collection->getData() as $batch) {
            $this->cleanUpByProcessIdentity->execute($batch[Batch::PROCESS_IDENTITY]);
        }

        $fileUploadMapCollection = $this->fileUploadMapCollectionFactory->create();
        $fileUploadMapCollection->addFieldToFilter(
            FileUploadMap::CREATED_AT,
            ['lt' => $deadLine->format('Y-m-d H:i:s')]
        );
        $tmpDir = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
        foreach ($fileUploadMapCollection->getItems() as $fileUploadMap) {
            if ($tmpDir->isFile($fileUploadMap->getFilename())) {
                try {
                    $tmpDir->delete($fileUploadMap->getFilename());
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            }
        }
        $fileUploadMapCollection->walk('delete');
    }
}
