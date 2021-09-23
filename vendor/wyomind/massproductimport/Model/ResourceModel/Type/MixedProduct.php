<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

/**
 * Class MixedProduct
 * @package Wyomind\MassProductImport\Model\ResourceModel\Type
 */
class MixedProduct extends \Wyomind\MassProductImport\Model\ResourceModel\Type\AbstractResource
{

    /**
     *
     */
    const FIELD_SEPARATOR = ",";
    /**
     *
     */
    const LINK_TYPE_ID = 3;

    /**
     *
     */
    public function _construct()
    {
        $this->tableCpr = $this->getTable("catalog_product_relation");
        $this->tableCpl = $this->getTable("catalog_product_link");
        $this->tableCpe = $this->getTable("catalog_product_entity");
        $this->tableCpla = $this->getTable("catalog_product_link_attribute");
        $this->tableCplai = $this->getTable("catalog_product_link_attribute_int");
        parent::_construct();
    }


    /**
     * @param int $productId
     * @param string $value
     * @param array $strategy
     * @param \Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     */
    function collect($productId, $value, $strategy, $profile)
    {


        list($field) = $strategy['option'];
        switch ($field) {
            case "parentSku":
                $values = explode(self::FIELD_SEPARATOR, $value);
                foreach ($values as $value) {
                    if ($value != "") {
                        $data = [
                            "parent_id" => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . " LIMIT 1)",
                            "child_id" => $productId
                        ];
                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpr, $data);
                        $data = [
                            "linked_product_id" => $productId,
                            "product_id" => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . " LIMIT 1)",
                            "link_type_id" => self::LINK_TYPE_ID
                        ];
                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpl, $data);
                        $this->queries[$this->queryIndexer][] = "SELECT @link_id:=link_id FROM " . $this->tableCpl . " WHERE linked_product_id=" . $productId . " AND product_id = (SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . " LIMIT 1) AND link_type_id = " . self::LINK_TYPE_ID . ";";
                        $this->queries[$this->queryIndexer][] = "SELECT @position:= IFNULL(MAX(value),0)+1 FROM " . $this->tableCplai . " 
                                        INNER JOIN " . $this->tableCpl . "   ON " . $this->tableCpl . ".link_id=" . $this->tableCplai . ".link_id AND product_id=(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . " LIMIT 1)
                                        WHERE product_link_attribute_id =(SELECT product_link_attribute_id FROM " . $this->tableCpla . " WHERE link_type_id=" . self::LINK_TYPE_ID . " and product_link_attribute_code='position') 
                                       ";

                        $data = [
                            "product_link_attribute_id" => "(SELECT product_link_attribute_id FROM " . $this->tableCpla . " WHERE link_type_id=" . self::LINK_TYPE_ID . " and product_link_attribute_code='position')",
                            "link_id" => "@link_id",
                            "value" => "@position"
                        ];
                    }

                    $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCplai, $data);
                }
                return;

                break;
            case "childrenSkus":
                $values = explode(self::FIELD_SEPARATOR, $value);
                foreach ($values as $value) {
                    if ($value != "") {
                        $data = [
                            "parent_id" => $productId,
                            "child_id" => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . " LIMIT 1)"
                        ];
                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpr, $data);
                        $data = [
                            "linked_product_id" => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . " LIMIT 1)",
                            "product_id" => $productId,
                            "link_type_id" => self::LINK_TYPE_ID
                        ];
                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpl, $data);
                        $this->queries[$this->queryIndexer][] = "SELECT @link_id:=link_id FROM " . $this->tableCpl . " WHERE linked_product_id=(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . " LIMIT 1) AND product_id = " . $productId . " AND link_type_id = " . self::LINK_TYPE_ID . ";";
                        $this->queries[$this->queryIndexer][] = "SELECT @position:= IFNULL(MAX(value),0)+1 FROM " . $this->tableCplai . " 
                                        INNER JOIN " . $this->tableCpl . "   ON " . $this->tableCpl . ".link_id=" . $this->tableCplai . ".link_id AND product_id=" . $productId . " LIMIT 1)
                                        WHERE product_link_attribute_id =(SELECT product_link_attribute_id FROM " . $this->tableCpla . " WHERE link_type_id=" . self::LINK_TYPE_ID . " and product_link_attribute_code='position') 
                                       ";
                        $data = [
                            "product_link_attribute_id" => "(SELECT product_link_attribute_id FROM " . $this->tableCpla . " WHERE link_type_id=1 and product_link_attribute_code='position')",
                            "link_id" => "@link_id",
                            "value" => "@position"
                        ];
                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCplai, $data);
                    }
                }
                return;
                break;
        }

        parent::collect($productId, $value, $strategy, $profile);
    }

    /**
     * @return array
     */
    public function getDropdown()
    {
        $dropdown = [];
        $i = 0;


        $dropdown['Grouped Products'][$i]['label'] = __("Parent SKU");
        $dropdown['Grouped Products'][$i]["id"] = "MixedProduct/parentSku";
        $dropdown['Grouped Products'][$i]['style'] = "mixed-product no-configurable";
        $dropdown['Grouped Products'][$i]['type'] = "List of related product SKU's separated by " . self::FIELD_SEPARATOR;
        $dropdown['Grouped Products'][$i]['value'] = "Sku ABC " . self::FIELD_SEPARATOR . " Sku XYZ " . self::FIELD_SEPARATOR . "...";

        $i++;
        $dropdown['Grouped Products'][$i]['label'] = __("Children SKUs");
        $dropdown['Grouped Products'][$i]["id"] = "MixedProduct/childrenSkus";
        $dropdown['Grouped Products'][$i]['style'] = "mixed-product no-configurable";
        $dropdown['Grouped Products'][$i]['type'] = "List of related product SKU's separated by " . self::FIELD_SEPARATOR;
        $dropdown['Grouped Products'][$i]['value'] = "Sku ABC " . self::FIELD_SEPARATOR . " Sku XYZ " . self::FIELD_SEPARATOR . "...";
        return $dropdown;
    }

    /**
     * @param array $mapping
     * @return array
     */
    public function getIndexes($mapping = [])
    {
        return [1 => "catalogrule_rule", 2 => "catalogrule_product"];
    }
}
