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
use Magento\Catalog\Api\Data\ProductInterface;

class LocationProductComplexValidator implements LocationProductValidatorInterface
{
    /**
     * @var LocationProductValidatorInterface[]
     */
    private $validators;

    public function __construct(array $validators)
    {
        $this->validators = $validators;
    }

    /**
     * @param LocationInterface $location
     * @param ProductInterface $product
     * @return bool
     */
    public function isValid(LocationInterface $location, ProductInterface $product): bool
    {
        $isValid = false;

        foreach ($this->validators as $validator) {
            if ($validator->isSupports($location)) {
                $isValid = $validator->isValid($location, $product);
                break;
            }
        }

        return $isValid;
    }

    /**
     * @param LocationInterface $location
     * @return bool
     */
    public function isSupports(LocationInterface $location): bool
    {
        return true;
    }
}
