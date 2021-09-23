<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ImportCore
 */


namespace Amasty\ImportCore\Model\Batch;

use Amasty\ImportCore\Model\Batch\ResourceModel\Batch as BatchResource;
use Amasty\ImportCore\Model\Batch\ResourceModel\CollectionFactory;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Ui\Api\Data\BookmarkSearchResultsInterfaceFactory;

class BatchRepository
{
    /**
     * @var BookmarkSearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * @var BatchFactory
     */
    private $batchFactory;

    /**
     * @var BatchResource
     */
    private $batchResource;

    /**
     * Model data storage
     *
     * @var array
     */
    private $batches;

    /**
     * @var CollectionFactory
     */
    private $batchCollectionFactory;

    public function __construct(
        BookmarkSearchResultsInterfaceFactory $searchResultsFactory,
        BatchFactory $batchFactory,
        BatchResource $batchResource,
        CollectionFactory $batchCollectionFactory
    ) {
        $this->searchResultsFactory = $searchResultsFactory;
        $this->batchFactory = $batchFactory;
        $this->batchResource = $batchResource;
        $this->batchCollectionFactory = $batchCollectionFactory;
    }

    public function save(Batch $batch)
    {
        try {
            if ($batch->getId()) {
                $batch = $this->getById($batch->getId())->addData($batch->getData());
            }
            $this->batchResource->save($batch);
            unset($this->batches[$batch->getId()]);
        } catch (\Exception $e) {
            if ($batch->getId()) {
                throw new CouldNotSaveException(
                    __(
                        'Unable to save batch with ID %1. Error: %2',
                        [$batch->getId(), $e->getMessage()]
                    )
                );
            }
            throw new CouldNotSaveException(__('Unable to save new batch. Error: %1', $e->getMessage()));
        }

        return $batch;
    }

    public function getById($id)
    {
        if (!isset($this->batches[$id])) {
            /** @var Batch $batch */
            $batch = $this->batchFactory->create();
            $this->batchResource->load($batch, $id);
            if (!$batch->getId()) {
                throw new NoSuchEntityException(__('Batch with specified ID "%1" not found.', $id));
            }
            $this->batches[$id] = $batch;
        }

        return $this->batches[$id];
    }

    public function fetchBatch(string $processIdentity): Batch
    {
        $connection = $this->batchResource->getConnection();
        $connection->beginTransaction();

        $collection = $this->batchCollectionFactory->create();
        $collection->addFieldToFilter(Batch::PROCESS_IDENTITY, $processIdentity)
            ->setOrder('id', 'ASC')
            ->setPageSize(1);
        $collection->getSelect()->forUpdate(true); // Lock record for read
        /** @var Batch $batch */
        $batch = $collection->getFirstItem();
        if ($batch->getId()) {
            $this->batchResource->unserializeFields($batch);
            $this->batchResource->delete($batch);
        }

        $connection->commit();

        return $batch;
    }

    public function cleanup(string $processIdentity): int
    {
        return $this->batchResource->deleteProcessData($processIdentity);
    }
}
