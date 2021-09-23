<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

class Merchandising extends \Wyomind\MassProductImport\Model\ResourceModel\Type\AbstractResource
{

    const FIELD_SEPARATOR = ",";

    public function _construct()
    {

        $this->tableCpe = $this->getTable("catalog_product_entity");
        $this->tableCplt = $this->getTable("catalog_product_link_type");
        $this->tableCpl = $this->getTable("catalog_product_link");
        $this->tableCpla = $this->getTable("catalog_product_link_attribute");
        $this->tableCplai = $this->getTable("catalog_product_link_attribute_int");
        parent::_construct();
    }

    function collect($productId, $value, $strategy, $profile)
    {

        list($entityType) = $strategy['option'];
        switch ($entityType) {
            case 'relation':
            case 'cross_sell':
            case 'up_sell':
                $entityTypeIsValid = true;
                break;
            default:
                $entityTypeIsValid = false;
        }
        if ($entityTypeIsValid && empty($value) == false) {
            $skus = explode(self::FIELD_SEPARATOR, $value);
            // Delete old data
            // Insert new data
            if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
                $tableCpe = $this->getTable("catalog_product_entity");
                $productId = "(SELECT MAX(row_id) from $tableCpe where entity_id=$productId)";
            }

            $linkTypeId = " (SELECT link_type_id FROM `" . $this->tableCplt . "` WHERE code = '" . $entityType . "')";
            $productLinkAttributeId = "(SELECT product_link_attribute_id FROM " . $this->tableCpla . " WHERE product_link_attribute_code = 'position' AND link_type_id = " . $linkTypeId . ")";

            $this->queries[$this->queryIndexer][] = "DELETE FROM `" . $this->tableCpl . "` WHERE product_id=" . $productId . " AND link_type_id = " . $linkTypeId;

            $position=0;
            foreach ($skus as $sku) {
                $this->queries[$this->queryIndexer][] = "INSERT IGNORE INTO " . $this->tableCpl . " (product_id, linked_product_id,link_type_id) "
                    . " VALUES ($productId, (SELECT entity_id FROM `" . $this->tableCpe . "` WHERE sku = '" . $sku . "'), (SELECT link_type_id FROM `" . $this->tableCplt . "` WHERE code = '" . $entityType . "'))\n ";
                $this->queries[$this->queryIndexer][] = "INSERT IGNORE INTO " . $this->tableCplai . " (product_link_attribute_id,link_id,value) VALUES(" . $productLinkAttributeId . ",LAST_INSERT_ID(),$position)";
                $position++;
            }
        }
        parent::collect($productId, $value, $strategy, $profile);
    }

    public function getDropdown()
    {
        $i = 0;
        $dropdown = [];
        $dropdown['Merchandising'][$i]['label'] = __("Related products");
        $dropdown['Merchandising'][$i]["id"] = "Merchandising/relation";
        $dropdown['Merchandising'][$i]['style'] = "merchandising";
        $dropdown['Merchandising'][$i]['type'] = "List of related product SKU's separated by " . self::FIELD_SEPARATOR;
        $dropdown['Merchandising'][$i]['value'] = "Sku ABC " . self::FIELD_SEPARATOR . " Sku XYZ " . self::FIELD_SEPARATOR . "...";
        $i++;
        $dropdown['Merchandising'][$i]['label'] = __("Cross sell");
        $dropdown['Merchandising'][$i]["id"] = "Merchandising/cross_sell";
        $dropdown['Merchandising'][$i]['style'] = "merchandising";
        $dropdown['Merchandising'][$i]['type'] = "List of related product SKU's separated by " . self::FIELD_SEPARATOR;
        $dropdown['Merchandising'][$i]['value'] = "Sku ABC " . self::FIELD_SEPARATOR . " Sku XYZ " . self::FIELD_SEPARATOR . "...";
        $i++;
        $dropdown['Merchandising'][$i]['label'] = __("Up sell");
        $dropdown['Merchandising'][$i]["id"] = "Merchandising/up_sell";
        $dropdown['Merchandising'][$i]['style'] = "merchandising";
        $dropdown['Merchandising'][$i]['type'] = "List of related product SKU's separated by " . self::FIELD_SEPARATOR;
        $dropdown['Merchandising'][$i]['value'] = "Sku ABC " . self::FIELD_SEPARATOR . " Sku XYZ " . self::FIELD_SEPARATOR . "...";

        return $dropdown;
    }
}
