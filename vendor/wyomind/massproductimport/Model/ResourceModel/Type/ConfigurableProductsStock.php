<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

class ConfigurableProductsStock extends \Wyomind\MassProductImport\Model\ResourceModel\Type\Stock
{



    function getFields($fieldset = null, $form = null, $class = null)
    {
        return false;
    }

    public function reset()
    {
        $this->fields = [];
        $this->qtyField = false;
    }

    public function prepareQueries($productId, $profile)
    {
        $data = [];
        $data["product_id"] = $productId;
        $data["stock_id"] = "1";
        foreach ($this->fields as $field => $value) {
            $data[$field] = $this->getValue($value);
        }

        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->table, $data);

        parent::prepareQueries($productId, $profile);
    }
}
