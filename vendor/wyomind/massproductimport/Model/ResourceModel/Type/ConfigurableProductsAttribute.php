<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

/**
 * Class ConfigurableProductsAttribute
 * @package Wyomind\MassProductImport\Model\ResourceModel\Type
 */
class ConfigurableProductsAttribute extends \Wyomind\MassProductImport\Model\ResourceModel\Type\Attribute
{

    /**
     * @param null $fieldset
     * @param bool $form
     * @param null $class
     * @return bool|null
     */
    public function getFields($fieldset = null, $form = false, $class = null)
    {
        return null;
    }

    /**
     * @param int $productId
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Profile|\Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @return array|void
     */
    public function prepareQueries($productId, $profile)
    {
        parent::prepareQueries($productId, $profile);
    }

    /**
     * @param int $productId
     * @param string $value
     * @param array $strategy
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Profile|\Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @throws \Exception
     */
    public function collect($productId, $value, $strategy, $profile)
    {

        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $source = $objectManager->get('\Wyomind\MassProductImport\Model\ResourceModel\Type\Attribute');
        $this->_OptionIdRegistry = $source->_OptionIdRegistry;

        return parent::collect($productId, $value, $strategy, $profile);
    }
}
