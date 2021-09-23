<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

/**
 * Class CustomScript
 * @package Wyomind\MassProductImport\Model\ResourceModel\Type
 */
class CustomScript extends \Wyomind\MassProductImport\Model\ResourceModel\Type\Attribute
{


    /**
     * List of available mapping options
     * @return array
     */
    public function getDropdown()
    {
        $dropdown = parent::getDropdown();
        $dropdown["Layaway Product Attributes"] = $dropdown["Attributes"];
        unset($dropdown["Attributes"]);
        foreach ($dropdown["Layaway Product Attributes"] as $i => $elt) {
            if (!stristr($elt["label"], "layaway")) {
                unset($dropdown["Layaway Product Attributes"][$i]);
            } else {
                $dropdown["Layaway Product Attributes"][$i]["id"] = str_replace("Attribute", "CustomScript", $dropdown["Layaway Product Attributes"][$i]["id"]);
            }
        }
        return $dropdown;
    }

    public function collect($productId, $value, $strategy, $profile)
    {

        switch ($value) {
            case 'No Limit':
                $value = 0;
                break;
            case 'Day':
                $value = 1;
                break;
            case 'Week':
                $value = 2;
                break;
            case 'Month':
                $value = 3;
                break;
            case 'Year':
                $value = 4;
                break;
            case 'Fixed Date':
                $value = 5;
                break;
        }


        list($entityType, $attributeId) = $strategy['option'];
        $attribute_code = null;
        if (isset($strategy['option'][2])) {
            $attribute_code = $strategy['option'][2];
        }

        $table = $this->getTable("catalog_product_entity_" . $entityType);

        switch ($attribute_code) {
            default:
                $val = [];
                if ($entityType == "int" && !isset($this->selectAttributes[$attributeId]) && $value != '') {
                    $value = (int)$this->getValue($value);
                }
                $value = trim($value);
                if ($value == "") {
                    foreach ($strategy['storeviews'] as $storeview) {
                        if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
                            $tableCpe = $this->getTable("catalog_product_entity");
                            $data = [
                                "row_id" => "(SELECT MAX(row_id) from $tableCpe where entity_id=$productId)",
                                "store_id" => $storeview,
                                "attribute_id" => "$attributeId"
                            ];
                        } else {
                            $data = [
                                "entity_id" => "$productId",
                                "store_id" => $storeview,
                                "attribute_id" => "$attributeId",
                            ];
                        }


                        $this->queries[$this->queryIndexer][] = $this->_delete($table, $data);
                    }
                    return;
                }
// if attribute is dropdown, swatch, multiselect


                $val[] = $this->helperData->sanitizeField($value);

                if (!count($val)) {
                    return;
                }
                $value = "CONCAT(" . implode(",',',", $val) . ")";
                break;
        }


        foreach ($strategy['storeviews'] as $storeview) {
            if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
                $tableCpe = $this->getTable("catalog_product_entity");
                $data = [
                    "row_id" => "(SELECT MAX(row_id) from $tableCpe where entity_id=$productId)",
                    "store_id" => $storeview,
                    "attribute_id" => "$attributeId",
                    "value" => $value
                ];
                $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($table, $data);
            } else {
                $data = [
                    "entity_id" => "$productId",
                    "store_id" => $storeview,
                    "attribute_id" => "$attributeId",
                    "value" => $value
                ];
                $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($table, $data);
            }
        }


    }
}
