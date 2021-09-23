<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\ViewModel\Catalog\Product;

use Amasty\StorePickupWithLocatorMSI\Model\ConfigProvider;
use Amasty\StorePickupWithLocatorMSI\Model\Location\LocationWithSourceChecker;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;

class Locations implements ArgumentInterface
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
     * @var LocationWithSourceChecker
     */
    private $locationWithSourceChecker;

    public function __construct(
        ConfigProvider $configProvider,
        StoreManagerInterface $storeManager,
        ProductRepositoryInterface $productRepository,
        LocationWithSourceChecker $locationWithSourceChecker
    ) {
        $this->configProvider = $configProvider;
        $this->storeManager = $storeManager;
        $this->productRepository = $productRepository;
        $this->locationWithSourceChecker = $locationWithSourceChecker;
    }

    /**
     * @param int $productId
     * @return bool
     */
    public function isShowLocationsWithMsi(int $productId): bool
    {
        if ($this->configProvider->isShowLocationsWithMsi()
            && $this->locationWithSourceChecker->isExists((int)$this->storeManager->getStore()->getId())) {
            return true;
        }

        return false;
    }
}
