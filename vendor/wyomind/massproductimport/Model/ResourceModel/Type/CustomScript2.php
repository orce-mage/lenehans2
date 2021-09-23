<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

class CustomScript extends \Wyomind\MassProductImport\Model\ResourceModel\Type\Attribute
{
    /**
     * Store all attributes registered in Magento
     * @var null
     */
    public $_attributes = null;
    /**Store the mapping defined by the user
     * @var array
     */
    public $_columns = [];

    /**
     * List of available mapping options
     * @return array
     */
    public function getDropdown()
    {
        $type = "User's custom scripts";
        $value = "-";

        $dropdown = [];
        $i = 0;
        $dropdown['Custom Script'][$i]['label'] = __("Create Attributes and Values on the fly");
        $dropdown['Custom Script'][$i]["id"] = "CustomScript/1";
        $dropdown['Custom Script'][$i]['style'] = "custom-script";
        $dropdown['Custom Script'][$i]['type'] = $type;
        $dropdown['Custom Script'][$i]['value'] = $value;
        $i++;


        return $dropdown;
    }

    /** Collect data that will be used latter in collect()
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Profile|\Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @param array $columns
     * @throws \Magento\Framework\Exception\InputException
     */
    public function beforeCollect($profile, $columns)
    {
        // attribute id in the mapping

// collect dropdown and swatch attribute
        $fields = [];
        $conditions = [];
        $attributes = $this->getAttributesList();
        foreach ($attributes as $attribute) {
            $this->_attributes[$attribute["attribute_code"]] =
                [
                    "attribute_id" => $attribute["attribute_id"],
                    "backend_type" => $attribute["backend_type"]
                ];
        }

        $this->_columns = $columns;

        parent::beforeCollect($profile, $columns);
    }

    /**
     * Generate the SQL queries
     * @param int $productId
     * @param string $value
     * @param array $strategy
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Profile|\Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @throws \Exception
     */
    public function collect($productId, $value, $strategy, $profile)
    {

        switch ($strategy["option"][0]) {
            case 1:
                $json = json_decode($value, true);
                foreach ($json["FEATURE"] as $node) {
                    $attributeCode = $node["FNAME"];
                    $val = $node["FVALUE"];
                    if (is_array($node["FVALUE"])) {
                        $val = implode("-", $node["FVALUE"]);
                    }

                    if (!isset($this->_attributes[strtolower($attributeCode)])) {
                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();


                        list($frontendInput, $backendType) = $this->getAttributeType($val);

                        $attributeData = [
                            'attribute_code' => strtolower($attributeCode),
                            'is_global' => 1,
                            'frontend_label' => $attributeCode,
                            'frontend_input' => $frontendInput,
                            'backend_type' => $backendType,

                            'default_value_text' => '',
                            'default_value_yesno' => 0,
                            'default_value_date' => '',
                            'default_value_textarea' => '',
                            'is_unique' => 0,
                            'apply_to' => 0,
                            'is_required' => 1,
                            'is_configurable' => 1,
                            'is_searchable' => 0,
                            'is_comparable' => 1,
                            'is_visible_in_advanced_search' => 1,
                            'is_used_for_price_rules' => 0,
                            'is_wysiwyg_enabled' => 0,
                            'is_html_allowed_on_front' => 1,
                            'is_visible_on_front' => 1,
                            'used_in_product_listing' => 1,
                            'used_for_sort_by' => 0,
                            'is_filterable' => 1,
                            'is_filterable_in_search' => 0,

                            'option' => [],
                            'default' => []
                        ];

                        $attributeInterface = $objectManager->create('\Magento\Catalog\Api\Data\ProductAttributeInterface');
                        $attributeInterface->setData($attributeData);
                        $attribute = $objectManager->create('\Magento\Catalog\Model\Product\Attribute\Repository');
                        $savedAttribute = $attribute->save($attributeInterface);
                        $attributeId = $savedAttribute->getId();

                        $this->_attributes[strtolower($attributeCode)] =
                            [
                                "attribute_id" => $attributeId,
                                "backend_type" => $backendType
                            ];
                        if ($frontendInput == "select") {
                            $this->selectAttributes[$attributeId] = [
                                "attribute_code" => strtolower($attributeCode),
                                "swatch" => false,
                                "swatch_type" => false
                            ];
                            $this->_attributeLabels[$attributeId] = [];
                        }
                    } else {
                        $backendType = $this->_attributes[strtolower($attributeCode)]["backend_type"];
                        $attributeId = $this->_attributes[strtolower($attributeCode)]["attribute_id"];
                    }

                    $strategy["option"] = [
                        $backendType,
                        $attributeId
                    ];


                    parent::collect($productId, $val, $strategy, $profile);
                }
        }
    }

    /**
     * Get the backend_type and frontend_input
     * @param $value
     * @return array [frontend_input,backen_type]
     */
    public function getAttributeType($value)
    {
        $boolean = [
            'TRUE',
            'FALSE',
            'false',
            'true',
        ];
//        if (is_array($value)) {
//            return array("multiselect", "varchar");
//        }
        if (in_array($value, $boolean)) {
            return ["boolean", "int"];
        }
//        if(preg_match("/(EV|EF)[0-9]{6}/", $value)) {
//            return array("select", "int");
//        }
        return ["text", "varchar"];
    }
}
