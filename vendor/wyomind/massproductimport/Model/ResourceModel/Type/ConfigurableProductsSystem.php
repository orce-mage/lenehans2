<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

/**
 * Class ConfigurableProductsSystem
 * @package Wyomind\MassProductImport\Model\ResourceModel\Type
 */
class ConfigurableProductsSystem extends \Wyomind\MassProductImport\Model\ResourceModel\Type\System
{
    /**
     * @param $profile
     * @param $columns
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeCollect($profile, $columns)
    {
        parent::beforeCollect($profile, $columns);
        $this->removeQueries = [];
    }

    /**
     * @param $productId
     * @param $value
     * @param $strategy
     * @param $profile
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    function collect($productId, $value, $strategy, $profile)
    {
        $this->fields["has_options"] = "1";
        $this->fields["required_options"] = "1";
        $this->fields["type_id"] = "configurable";
        parent::collect($productId, $value, $strategy, $profile);
    }


    /**
     * @param array $mapping
     * @return array
     */
    public function getIndexes($mapping = [])
    {
        return [1 => "catalogrule_rule", 2 => "catalogrule_product"];
    }

    /**
     * @param null $fieldset
     * @param null $form
     * @param null $class
     * @return bool|null|void
     */
    function getFields($fieldset = null, $form = null, $class = null)
    {
        return;
    }

    public function updatequeries($productId, $profile)
    {
        unset($this->removeQueries);

        return parent::updatequeries($productId, $profile);
    }
}
