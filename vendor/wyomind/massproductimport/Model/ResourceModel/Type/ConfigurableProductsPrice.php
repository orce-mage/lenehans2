<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

/**
 * Class ConfigurableProductsPrice
 * @package Wyomind\MassProductImport\Model\ResourceModel\Type
 */
class ConfigurableProductsPrice extends \Wyomind\MassProductImport\Model\ResourceModel\Type\Price
{
    /**
     * @param null $fieldset
     * @param null $form
     * @param null $class
     * @return bool
     */
    function getFields($fieldset = null, $form = null, $class = null)
    {
        return false;
    }
}
