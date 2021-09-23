<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorelocatorIndexer
 */


namespace Amasty\StorelocatorIndexer\Model\Indexer\Product;

use Amasty\Storelocator\Model\Location;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory as LocationCollectionFactory;
use Amasty\StorelocatorIndexer\Model\Indexer\AbstractIndexBuilder;
use Amasty\StorelocatorIndexer\Model\Indexer\Location\LocationIndexer;
use Amasty\StorelocatorIndexer\Model\ResourceModel\LocationProductIndex;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\ProductRepository;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class IndexBuilder extends AbstractIndexBuilder
{
    /**
     * @var ProductCollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * @var ProductRepository
     */
    protected $productRepository;

    /**
     * @var LocationIndexer
     */
    private $locationIndexer;

    /**
     * @var LocationProductIndex
     */
    protected $locationProduct;

    public function __construct(
        LocationCollectionFactory $locationCollectionFactory,
        LoggerInterface $logger,
        ProductCollectionFactory $productCollectionFactory,
        ProductRepository $productRepository,
        LocationIndexer $locationIndexer,
        LocationProductIndex $locationProduct,
        $batchSize = 1000
    ) {
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productRepository = $productRepository;
        $this->locationIndexer = $locationIndexer;
        $this->locationProduct = $locationProduct;
        parent::__construct($locationCollectionFactory, $logger, $productCollectionFactory, $batchSize);
    }

    /**
     * @param array $ids
     *
     * @throws \Exception
     */
    protected function doReindexByIds($ids)
    {
        $productCollection = $this->getAllProducts();
        $productCollection->addFieldToFilter('id', ['in' => $ids]);

        foreach ($productCollection as $product) {
            $this->updateLocationProductsIndex($product);
        }
    }

    /**
     * @throws LocalizedException
     */
    protected function doReindexFull()
    {
        $this->locationIndexer->executeFull();
    }

    /**
     * @param int $productId
     *
     * @return void
     * @throws LocalizedException
     */
    public function reindexByProductId($productId)
    {
        $this->reindexByProductIds([$productId]);
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
    public function reindexByProductIds(array $ids)
    {
        try {
            $this->doReindexByProductIds($ids);
        } catch (\Exception $e) {
            $this->critical($e);
            throw new LocalizedException(__($e->getMessage()), $e);
        }
    }

    /**
     * Reindex by ids. Template method
     *
     * @param array $productIds
     *
     * @return void
     * @throws \Exception
     */
    protected function doReindexByProductIds(array $productIds): void
    {
        $this->locationProduct->deleteByIds(null, $productIds);

        /** @var Location $location */
        foreach ($this->getAllLocations() as $location) {
            $validProductIds = [];
            foreach ($productIds as $productId) {
                $product = $this->productRepository->getById($productId);
                foreach ($product->getStoreIds() as $storeId) {
                    $product->setStoreId($storeId);
                    if (!$location->getProductConditions()->getActions()
                        || $location->getProductConditions()->validate($product)
                    ) {
                        $validProductIds[$productId][$storeId] = true;
                    }
                }
            }

            if ($validProductIds) {
                $this->updateLocationIndex($location, $validProductIds);
            }
        }
    }

    /**
     * Collect product matches for location
     *
     * @param Product $product
     * @return $this
     * @throws \Exception
     */
    protected function updateLocationProductsIndex(Product $product)
    {
        $productId = $product->getId();
        $this->locationProduct->deleteByIds(null, $productId);

        if (!$product->getStatus()) {
            return $this;
        }

        $locationCollection = $this->getAllLocations();
        $rows = [];
        $count = 0;

        foreach ($product->getStoreIds() as $storeId) {
            foreach ($locationCollection->getItems() as $location) {
                $product->setStoreId($storeId);

                if ($location->getProductConditions()->validate($product)) {
                    $rows[] = [
                        LocationProductIndex::LOCATION_ID => $location->getId(),
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
        }

        if (!empty($rows)) {
            $this->locationProduct->insertData($rows);
        }

        return $this;
    }
}
