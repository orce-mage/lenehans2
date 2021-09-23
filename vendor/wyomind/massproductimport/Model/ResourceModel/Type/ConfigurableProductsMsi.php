<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

class ConfigurableProductsMsi extends \Wyomind\MassProductImport\Model\ResourceModel\Type\Msi
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
