<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorelocatorIndexer
 */


declare(strict_types=1);

namespace Amasty\StorelocatorIndexer\Model\Validator;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Api\Validator\LocationProductValidatorInterface;
use Amasty\Storelocator\Model\Config\Source\ConditionType;
use Amasty\StorelocatorIndexer\Model\ResourceModel\LocationProductIndex;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Store\Model\StoreManagerInterface;

class ProductAttributeValidator implements LocationProductValidatorInterface
{
    /**
     * @var LocationProductIndex
     */
    private $locationProductIndex;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        LocationProductIndex $locationProductIndex,
        StoreManagerInterface $storeManager
    ) {
        $this->locationProductIndex = $locationProductIndex;
        $this->storeManager = $storeManager;
    }
    /**
     * @param LocationInterface $location
     * @param ProductInterface $product
     * @return bool
     */
    public function isValid(LocationInterface $location, ProductInterface $product): bool
    {
        return $this->locationProductIndex->validateLocation(
            $location->getId(),
            $product->getId(),
            $this->storeManager->getStore()->getId()
        );
    }

    /**
     * @param LocationInterface $location
     * @return bool
     */
    public function isSupports(LocationInterface $location): bool
    {
        return $location->getConditionType() == ConditionType::PRODUCT_ATTRIBUTE;
    }
}
