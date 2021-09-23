<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

class Ignored extends \Wyomind\MassStockUpdate\Model\ResourceModel\Type\Ignored
{
    /**
     * @var null
     */
    public $_table = null;

    /**
     *  Construct method
     */
    public function _construct()
    {

        $this->_table = $this->getTable('ves_brand_product');
        $this->_tableVarchar = $this->getTable('catalog_product_entity_varchar');
        $this->_tableEav = $this->getTable('eav_attribute');

        parent::_construct();
    }

    /**
     * Collect all the queries
     * @param int $productId
     * @param string $value
     * @param array $strategy
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Profile|\Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     */
    public function collect($productId, $value, $strategy, $profile)
    {
        $value = (int)$value;

        if ($strategy["option"][0] == "ves_brand_id") {
            if (is_integer($value) && $value) {
                $data = [
                    "product_id" => $productId,
                    "brand_id" => $value
                ];
                $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->_table, $data);

                foreach ($strategy['storeviews'] as $storeview) {
                    $data = [
                        "entity_id" => "$productId",
                        "store_id" => $storeview,
                        "attribute_id" => "(SELECT attribute_id from $this->_tableEav where attribute_code='product_brand')",
                        "value" => $value
                    ];
                    $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->_tableVarchar, $data);
                }
            }
        }

        parent::collect($productId, $value, $strategy, $profile);
    }
}
