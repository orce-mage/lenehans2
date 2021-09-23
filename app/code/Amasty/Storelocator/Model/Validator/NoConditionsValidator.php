<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


declare(strict_types=1);

namespace Amasty\Storelocator\Model\Validator;

use Amasty\Storelocator\Api\Data\LocationInterface;
use Amasty\Storelocator\Api\Validator\LocationProductValidatorInterface;
use Amasty\Storelocator\Model\Config\Source\ConditionType;
use Magento\Catalog\Api\Data\ProductInterface;

class NoConditionsValidator implements LocationProductValidatorInterface
{
    /**
     * @param LocationInterface $location
     * @param ProductInterface $product
     * @return bool
     */
    public function isValid(LocationInterface $location, ProductInterface $product): bool
    {
        return true;
    }

    /**
     * @param LocationInterface $location
     * @return bool
     */
    public function isSupports(LocationInterface $location): bool
    {
        return $location->getConditionType() == ConditionType::NO_CONDITIONS;
    }
}
