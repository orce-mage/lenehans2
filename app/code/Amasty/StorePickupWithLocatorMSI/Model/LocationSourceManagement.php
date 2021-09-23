<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Model;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\StorePickupWithLocatorMSI\Api\Data\LocationDataInterface;
use Amasty\StorePickupWithLocatorMSI\Api\Data\LocationDataInterfaceFactory;
use Amasty\StorePickupWithLocatorMSI\Api\Data\LocationSourceSearchResultInterfaceFactory;
use Amasty\StorePickupWithLocatorMSI\Api\Data\LocationWithQtyInterface;
use Amasty\StorePickupWithLocatorMSI\Api\Data\LocationWithQtyInterfaceFactory;
use Amasty\StorePickupWithLocatorMSI\Api\LocationSourceManagementInterface;
use Amasty\StorePickupWithLocatorMSI\Model\Location\GetLocationsByProduct;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

class LocationSourceManagement implements LocationSourceManagementInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Location\GetLocationsByProduct
     */
    private $getLocationsByProduct;

    /**
     * @var LocationSourceSearchResultInterfaceFactory
     */
    private $searchResultFactory;

    /**
     * @var LocationWithQtyInterfaceFactory
     */
    private $locationWithQtyFactory;

    /**
     * @var LocationDataInterfaceFactory
     */
    private $locationDataFactory;

    public function __construct(
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        GetLocationsByProduct $getLocationsByProduct,
        LocationSourceSearchResultInterfaceFactory $searchResultFactory,
        LocationWithQtyInterfaceFactory $locationWithQtyFactory,
        LocationDataInterfaceFactory $locationDataFactory
    ) {
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->getLocationsByProduct = $getLocationsByProduct;
        $this->searchResultFactory = $searchResultFactory;
        $this->locationWithQtyFactory = $locationWithQtyFactory;
        $this->locationDataFactory = $locationDataFactory;
    }

    /**
     * @param int $productId
     * @return \Amasty\StorePickupWithLocatorMSI\Api\Data\LocationSourceSearchResultInterface
     */
    public function getLocationsByProduct(
        int $productId
    ): \Amasty\StorePickupWithLocatorMSI\Api\Data\LocationSourceSearchResultInterface {
        $locationCollection = $this->getLocationsByProduct->getLocationsByProduct(
            $this->productRepository->getById($productId)->getSku(),
            (int)$this->storeManager->getStore()->getId(),
            !$this->configProvider->isIncludeOutOfStockLocations()
        );

        $locationCollection->setOrder(GetLocationsByProduct::QTY_IN_STOCK);

        /** @var \Amasty\StorePickupWithLocatorMSI\Api\Data\LocationSourceSearchResultInterface $searchResult */
        $searchResult = $this->searchResultFactory->create();

        $items = $this->prepareItems($locationCollection->getItems());

        // @TODO $collection->getSize() doesn't work because of complex query (it returns 1 when there is no items)
        // @TODO change it while implementing pagination
        $searchResult->setTotalCount(count($items));
        $searchResult->setItems($items);

        return $searchResult;
    }

    /**
     * @param LocationInterface[] $items
     * @return LocationWithQtyInterface[]
     */
    private function prepareItems(array $items)
    {
        $locationsWithQty = [];

        foreach ($items as $item) {
            $locationData = $this->locationDataFactory->create(['data' => $item->getData()]);
            $locationWithQty = $this->locationWithQtyFactory->create();
            $locationWithQty->setLocation($locationData);
            $locationWithQty->setQty((int)$item->getData(GetLocationsByProduct::QTY_IN_STOCK));

            $locationsWithQty[] = $locationWithQty;
        }

        return $locationsWithQty;
    }
}
