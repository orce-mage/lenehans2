<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

class ConfigurableProductsImage extends \Wyomind\MassProductImport\Model\ResourceModel\Type\Image
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
