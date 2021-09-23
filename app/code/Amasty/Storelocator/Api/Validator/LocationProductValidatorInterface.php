<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Api\Validator;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Magento\Catalog\Api\Data\ProductInterface;

interface LocationProductValidatorInterface
{
    /**
     * @param LocationInterface $location
     * @param ProductInterface $product
     * @return bool
     */
    public function isValid(LocationInterface $location, ProductInterface $product): bool;

    /**
     * @param LocationInterface $location
     * @return bool
     */
    public function isSupports(LocationInterface $location): bool;
}
