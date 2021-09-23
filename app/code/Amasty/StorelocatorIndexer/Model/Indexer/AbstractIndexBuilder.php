<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorelocatorIndexer
 */


namespace Amasty\StorelocatorIndexer\Model\Indexer;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\Config\Source\ConditionType;
use Amasty\Storelocator\Model\Location;
use Amasty\Storelocator\Model\ResourceModel\Location\Collection;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory as LocationCollectionFactory;
use Amasty\StorelocatorIndexer\Model\ResourceModel\LocationProductIndex;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

abstract class AbstractIndexBuilder
{
    /**
     * @var LocationCollectionFactory
     */
    protected $locationCollectionFactory;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @var int
     */
    protected $batchSize;

    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    public function __construct(
        LocationCollectionFactory $locationCollectionFactory,
        LoggerInterface $logger,
        ProductCollectionFactory $productCollectionFactory,
        $batchSize = 1000
    ) {
        $this->locationCollectionFactory = $locationCollectionFactory;
        $this->logger = $logger;
        $this->batchSize = $batchSize;
        $this->productCollectionFactory = $productCollectionFactory;
    }

    /**
     * Reindex by id
     *
     * @param int $id
     *
     * @return void
     * @throws LocalizedException
     * @api
     */
    public function reindexById($id)
    {
        $this->reindexByIds([$id]);
    }

    /**
     * Reindex by ids
     *
     * @param array $ids
     *
     * @return void
     * @throws LocalizedException
     * @api
     */
    public function reindexByIds(array $ids)
    {
        try {
            $this->doReindexByIds($ids);
        } catch (\Exception $e) {
            $this->critical($e);
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * Reindex by ids. Template method
     *
     * @param array $ids
     *
     * @return void
     */
    abstract protected function doReindexByIds($ids);

    /**
     * Full reindex
     *
     * @return void
     * @throws LocalizedException
     * @api
     */
    public function reindexFull()
    {
        try {
            $this->doReindexFull();
        } catch (\Exception $e) {
            $this->critical($e);
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * Full reindex Template method
     *
     * @return void
     */
    abstract protected function doReindexFull();

    /**
     * Get locations for index. Only "product attribute" conditions should be indexed
     *
     * @return Collection
     */
    protected function getAllLocations()
    {
        return $this->locationCollectionFactory->create()
            ->addFieldToFilter('status', 1)
            ->addFieldToFilter(LocationInterface::CONDITION_TYPE, ['eq' => ConditionType::PRODUCT_ATTRIBUTE]);
    }

    /**
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function getAllProducts()
    {
        return $this->productCollectionFactory->create();
    }

    /**
     * @param \Exception $exception
     *
     * @return void
     */
    protected function critical($exception)
    {
        $this->logger->critical($exception);
    }

    protected function updateLocationIndex(Location $location, $productIds)
    {
        $locationId = $location->getId();
        $this->locationProduct->deleteByIds($locationId, array_keys($productIds));

        $rows = [];
        $count = 0;
        foreach ($productIds as $productId => $stores) {
            foreach ($stores as $storeId => $value) {
                $rows[] = [
                    LocationProductIndex::LOCATION_ID => $locationId,
                    LocationProductIndex::PRODUCT_ID => $productId,
                    LocationProductIndex::STORE_ID => $storeId
                ];

                if (++$count == $this->batchSize) {
                    $this->locationProduct->insertData($rows);
                    $rows = [];
                    $count = 0;
                }
            }
        }

        if (!empty($rows)) {
            $this->locationProduct->insertData($rows);
        }

        return $this;
    }
}
