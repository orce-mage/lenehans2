<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

class TierPrice extends \Wyomind\MassProductImport\Model\ResourceModel\Type\Price
{
    /**
     * Tier Price Attribute id
     * @var bool
     */
    public $_tierPriceAttributeId = false;

    /**
     * Contruct
     */
    public function _construct()
    {
        $this->tableCeptp = $this->getTable("catalog_product_entity_tier_price");
        parent::_construct();
    }

    /**
     * Collect queries
     * @param int $productId
     * @param string $value
     * @param array $strategy
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Profile|\Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @throws \Exception
     */
    public function collect($productId, $value, $strategy, $profile)
    {
        list($entityType, $attributeId, $action) = $strategy['option'];

        $prices = explode(self::LINE_SEPARATOR, $value);

        if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
            $tableCpe = $this->getTable("catalog_product_entity");

            $productId = "(SELECT MAX(row_id) from $tableCpe where entity_id=$productId)";
            $field = "row_id";
        } else {
            $field = "entity_id";
        }

        if ($action == 'replace') { // delete existing tier prices if the action is to replace the prices
            $this->queries[$this->queryIndexer][] = "DELETE FROM " . $this->tableCeptp . " WHERE $field = $productId;";
        }

        $storeviews = $strategy["storeviews"];
        $websites = [];

        foreach ($prices as $price) {
            if (trim($price) != "") {
                list($groupId, $qty, $val) = explode(self::FIELD_SEPARATOR, $price);
                $allGroup = 0;
                if ($groupId == "*") {
                    $allGroup = 1;
                }
                $percentageValue = "NULL";
                if (substr($val, -1, 1) == "%") {
                    $percentageValue = substr($val, 0, strlen($val) - 1);
                    $val = 0;
                }

                foreach ($storeviews as $storeview) {
                    if ($storeview == 0 || !in_array($this->website[$storeview], $websites)) {
                        $websiteId = 0;
                        if ($storeview != 0) {
                            $websiteId = $this->website[$storeview];
                        }
                        $websites[] = $websiteId . "_" . $price;
                        $this->queries[$this->queryIndexer][] = "INSERT INTO " . $this->tableCeptp . " ($field,all_groups,customer_group_id,qty,value,percentage_value,website_id) "
                            . " VALUES ($productId,'$allGroup','$groupId','$qty','$val',$percentageValue,'" . $websiteId . "')"
                            . "ON DUPLICATE KEY UPDATE value='$val', percentage_value=$percentageValue\n ";
                    }
                }
            }
        }
    }

    /**
     * Get dropdown entries
     * @return array
     */
    public function getDropdown()
    {
        /* ATTIBUTE MAPPING */
        $dropdown = [];
        $fields = ["attribute_code"];
        $conditions = [
            ["eq" =>
                [
                    "tier_price"
                ],
            ],
        ];
        $attributesList = $this->getAttributesList($fields, $conditions, false);
        $attribute = $attributesList[0];

        /* ATTIBUTE MAPPING */
        $dropdown = [];
        $dropdown['Tier Prices'][0]['label'] = __("Add Tier Prices / Group Prices");
        $dropdown['Tier Prices'][0]['style'] = "Price storeviews-dependent" ;
        $dropdown['Tier Prices'][0]['id'] = "TierPrice/" . $attribute['backend_type'] . "/" . $attribute['attribute_id']."/add";
        $dropdown['Tier Prices'][0]['type'] = "List of tier prices separated by " . self::LINE_SEPARATOR;
        $dropdown['Tier Prices'][0]['value'] = "[Group id 1]" . self::FIELD_SEPARATOR . "[Qty 1]" . self::FIELD_SEPARATOR . "[Price 1]" . self::LINE_SEPARATOR . "[Group Id 2]" . self::FIELD_SEPARATOR . "[Qty 2]" . self::FIELD_SEPARATOR . "[Price 2]" . self::LINE_SEPARATOR . "...";

        $dropdown['Tier Prices'][1]['label'] = __("Replace Tier Prices / Group Prices");
        $dropdown['Tier Prices'][1]['style'] = "Price storeviews-dependent" ;
        $dropdown['Tier Prices'][1]['id'] = "TierPrice/" . $attribute['backend_type'] . "/" . $attribute['attribute_id']."/replace";
        $dropdown['Tier Prices'][1]['type'] = "List of tier prices separated by " . self::LINE_SEPARATOR;
        $dropdown['Tier Prices'][1]['value'] = "[Group id 1]" . self::FIELD_SEPARATOR . "[Qty 1]" . self::FIELD_SEPARATOR . "[Price 1]" . self::LINE_SEPARATOR . "[Group Id 2]" . self::FIELD_SEPARATOR . "[Qty 2]" . self::FIELD_SEPARATOR . "[Price 2]" . self::LINE_SEPARATOR . "...";
//var_dump($dropdown);
//die();
        return $dropdown;
    }

    /**
     * Get Indexes to run
     * @param array $mapping
     * @return array
     */
    public function getIndexes($mapping = [])
    {
        return [11 => "catalog_product_price"];
    }
}
