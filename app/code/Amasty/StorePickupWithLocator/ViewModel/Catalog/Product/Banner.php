<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocator
 */

declare(strict_types=1);

namespace Amasty\StorePickupWithLocator\ViewModel\Catalog\Product;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Model\ResourceModel\Location\Collection;
use Amasty\Storelocator\Model\ResourceModel\Location\CollectionFactory;
use Amasty\StorePickupWithLocator\Model\ConfigProvider;
use Amasty\StorePickupWithLocator\Model\Location\LocationProductFilterApplier;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product\Type;
use Magento\Cms\Block\Block;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\View\LayoutInterface;
use Magento\GroupedProduct\Model\Product\Type\Grouped;
use Magento\Store\Model\Store;
use Magento\Store\Model\StoreManagerInterface;

class Banner implements ArgumentInterface
{
    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var LocationProductFilterApplier
     */
    private $locationFilterApplier;

    /**
     * @var CollectionFactory
     */
    private $locationCollectionFactory;

    /**
     * @var Collection
     */
    private $locationCollection;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var array
     */
    private $allowedProductTypes;

    public function __construct(
        ConfigProvider $configProvider,
        LocationProductFilterApplier $locationFilterApplier,
        CollectionFactory $collectionFactory,
        StoreManagerInterface $storeManager,
        array $allowedProductTypes = [
            Type::TYPE_SIMPLE,
            Type::TYPE_BUNDLE,
            Configurable::TYPE_CODE,
            Grouped::TYPE_CODE
        ]
    ) {
        $this->configProvider = $configProvider;
        $this->locationFilterApplier = $locationFilterApplier;
        $this->locationCollectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->allowedProductTypes = $allowedProductTypes;
    }

    /**
     * @param ProductInterface $product
     * @return bool
     */
    public function shouldDisplayBanner(ProductInterface $product): bool
    {
        return in_array($product->getTypeId(), $this->allowedProductTypes)
            && !!$this->getLocationCollection((int)$product->getId())->getSize();
    }

    /**
     * @param LayoutInterface $currentLayout
     * @return string
     */
    public function getBannerBlockHtml(LayoutInterface $currentLayout): string
    {
        $html = '';

        if ($bannerBlockCode = $this->configProvider->getCurbsidePickupBannerCode()) {
            /** @var Block $bannerBlock */
            $bannerBlock = $currentLayout->createBlock(Block::class);
            $html = $bannerBlock
                ->setBlockId($bannerBlockCode)
                ->toHtml();
        }

        return $html;
    }

    /**
     * @param int $productId
     * @return Collection
     */
    private function getLocationCollection(int $productId): Collection
    {
        if ($this->locationCollection === null) {
            $storeId = (int)$this->storeManager->getStore()->getId();
            $locationCollection = $this->locationCollectionFactory->create();

            $locationCollection
                ->addFieldToFilter(
                    'main_table.' . LocationInterface::STATUS,
                    LocationInterface::STATUS_ENABLED
                )->addFieldToFilter(
                    'main_table.' . LocationInterface::CURBSIDE_ENABLED,
                    1
                )->addFilterByStores(
                    [Store::DEFAULT_STORE_ID, $storeId]
                );
            $this->locationFilterApplier->addProductsFilter($locationCollection, [$productId], $storeId);

            $this->locationCollection = $locationCollection;
        }

        return $this->locationCollection;
    }
}
