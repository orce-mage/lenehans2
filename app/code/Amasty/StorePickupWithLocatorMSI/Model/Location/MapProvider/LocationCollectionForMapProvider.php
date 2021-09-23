<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Model\Location\MapProvider;

use Amasty\Storelocator\Model\ResourceModel\Location\Collection;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Amasty\StorePickupWithLocator\Api\LocationCollectionForMapProviderInterface;
use Amasty\StorePickupWithLocatorMSI\Model\ConfigProvider;
use Amasty\StorePickupWithLocatorMSI\Model\Location\GetLocationsByProduct;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\StoreManagerInterface;

class LocationCollectionForMapProvider implements LocationCollectionForMapProviderInterface
{
    /**
     * @var RequestInterface
     */
    private $request;

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
     * @var GetLocationsByProduct
     */
    private $getLocationsByProduct;

    /**
     * @var CollectionFactory
     */
    private $locationCollectionFactory;

    public function __construct(
        RequestInterface $request,
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        GetLocationsByProduct $getLocationsByProduct,
        CollectionFactory $locationCollectionFactory
    ) {
        $this->request = $request;
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->getLocationsByProduct = $getLocationsByProduct;
        $this->locationCollectionFactory = $locationCollectionFactory;
    }

    /**
     * @return Collection
     */
    public function getCollection(): Collection
    {
        $params = $this->request->getParams();
        if (empty($params['product'])) {
            return $this->locationCollectionFactory->create();
        }
        $productId = $params['product'];

        $locationCollection = $this->getLocationsByProduct->getLocationsByProduct(
            $this->productRepository->getById($productId)->getSku(),
            (int)$this->storeManager->getStore()->getId(),
            !$this->configProvider->isIncludeOutOfStockLocations()
        );

        $locationCollection->addDistance($locationCollection->getSelect());

        return $locationCollection;
    }
}
