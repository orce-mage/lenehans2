<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

class ConfigurableProductsMerchandising extends \Wyomind\MassProductImport\Model\ResourceModel\Type\Merchandising
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
