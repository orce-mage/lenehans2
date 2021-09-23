<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

/**
 * Class ConfigurableProductsCategory
 * @package Wyomind\MassProductImport\Model\ResourceModel\Type
 */
class ConfigurableProductsCategory extends \Wyomind\MassProductImport\Model\ResourceModel\Type\Category
{


    /**
     * @param null $fieldset
     * @param null $form
     * @param null $class
     * @return bool|void
     */
    function getFields($fieldset = null, $form = null, $class = null)
    {
        return;
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

        $objectManager=\Magento\Framework\App\ObjectManager::getInstance();
        $source=$objectManager->get('\Wyomind\MassProductImport\Model\ResourceModel\Type\Category');
        $this->_categoryRegistry=$source->_categoryRegistry;
        $this->_categoryData=$source->_categoryData;

        parent::collect($productId, $value, $strategy, $profile);
    }
}
