<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

class ConfigurableProduct extends \Wyomind\MassProductImport\Model\ResourceModel\Type\AbstractResource
{

    const ITEM_SEPARATOR = ",";
    public $_configurableAttributes = [];
    public $_configurableAttributeLabels = [];

    public function _construct()
    {
        $this->tableCpr = $this->getTable("catalog_product_relation");
        $this->tableCpsl = $this->getTable("catalog_product_super_link");
        $this->tableCpe = $this->getTable("catalog_product_entity");
        $this->tableCpsa = $this->getTable("catalog_product_super_attribute");
        $this->tableCpsal = $this->getTable("catalog_product_super_attribute_label");

        parent::_construct();
    }

    public function beforeCollect($profile, $columns)
    {
        $fields = ["frontend_input"];
        $conditions = [
            ["eq" =>
                [
                    "select",
                ]
            ],
        ];
        $atributes = $this->getAttributesList($fields, $conditions, false);
        foreach ($atributes as $attribute) {
            $this->_configurableAttributes[$attribute["attribute_id"]] = $attribute["attribute_code"];
            $this->_configurableAttributeLabels[$attribute["attribute_id"]] = $attribute["frontend_label"];
        }

        parent::beforeCollect($profile, $columns);
    }

    function collect($productId, $value, $strategy, $profile)
    {


        list($field) = $strategy['option'];
        switch ($field) {
            case "parentSku":
                if ($value != "") {
                    if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
                        $data = [
                            "parent_id" => "(SELECT MAX(row_id) from $this->tableCpe where entity_id=(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . " LIMIT 1))",
                            "child_id" => $productId,
                        ];
                    } else {
                        $data = [
                            "parent_id" => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . " LIMIT 1)",
                            "child_id" => $productId,
                        ];
                    }
                    $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpr, $data, true);


                    if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
                        $data = [
                            "product_id" => $productId,
                            "parent_id" => "(SELECT MAX(row_id) from $this->tableCpe where entity_id=(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . " LIMIT 1))",
                        ];
                    } else {
                        $data = [
                            "product_id" => $productId,
                            "parent_id" => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . " LIMIT 1)",
                        ];
                    }
                    $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpsl, $data, true);


                    $this->fields["sku"] = $value;
                }
                return;
                break;
            case "childrenSkus":
                $values = explode(self::ITEM_SEPARATOR, $value);
                foreach ($values as $value) {
                    if ($value != "") {
                        if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
                            $data = [
                                "parent_id" => "(SELECT MAX(row_id) from $this->tableCpe where entity_id=$productId)",
                                "child_id" => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . " LIMIT 1)",
                            ];
                        } else {
                            $data = [
                                "parent_id" => $productId,
                                "child_id" => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . " LIMIT 1)",
                            ];
                        }
                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpr, $data, true);

                        if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
                            $data = [
                                "product_id" => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . " LIMIT 1)",
                                "parent_id" => "(SELECT MAX(row_id) from $this->tableCpe where entity_id=$productId)",
                            ];
                        } else {
                            $data = [
                                "product_id" => "(SELECT entity_id FROM `$this->tableCpe` WHERE sku=" . $this->helperData->sanitizeField($value) . " LIMIT 1)",
                                "parent_id" => $productId,
                            ];
                        }
                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpsl, $data, true);
                    }
                }
                return;
                break;
            case "attributes":
                if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
                    $productId = "(SELECT MAX(row_id) from $this->tableCpe where entity_id=$productId)";
                }

                $values = explode(self::ITEM_SEPARATOR, $value);

                foreach ($values as $value) {
                    $origValue = $value;
                    $value = $this->helperData->getValue($value);


                    if (isset($this->_configurableAttributes[$value]) || in_array($value, $this->_configurableAttributes)) {
                        if (in_array($value, $this->_configurableAttributes)) {
                            $value = array_search($value, $this->_configurableAttributes);
                        }

                        $label = $this->_configurableAttributeLabels[$value];
                        $fields = [
                            "product_id" => new \Zend_Db_Expr($productId),
                            "attribute_id" => "" . $value . "",
                            "position" => 0
                        ];
                        $data = $this->helperData->prepareFields($fields, $origValue, "position");

                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpsa, $data);
                        $this->queries[$this->queryIndexer][] = "SELECT @product_super_attribute_id:= product_super_attribute_id FROM " . $this->tableCpsa . " WHERE product_id=$productId AND attribute_id='" . $value . "';";

                        $data = [
                            "product_super_attribute_id" => "@product_super_attribute_id",
                            "value" => "'" . $label . "'",
                        ];
                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCpsal, $data);
                    }
                }


                return;
                break;
        }
        parent::collect($productId, $value, $strategy, $profile);
    }

    public function getDropdown()
    {
        $dropdown = [];
        $i = 0;


        $dropdown['Configurable Products'][$i]['label'] = __("Parent SKU");
        $dropdown['Configurable Products'][$i]["id"] = "ConfigurableProduct/parentSku";
        $dropdown['Configurable Products'][$i]['style'] = "configurable-product-parent no-configurable";
        $dropdown['Configurable Products'][$i]['type'] = "Parent product SKU";
        $dropdown['Configurable Products'][$i]['value'] = "Sku ABC";

        $i++;
        $dropdown['Configurable Products'][$i]['label'] = __("Children SKUs");
        $dropdown['Configurable Products'][$i]["id"] = "ConfigurableProduct/childrenSkus";
        $dropdown['Configurable Products'][$i]['style'] = "configurable-product-children no-configurable";
        $dropdown['Configurable Products'][$i]['type'] = "List of children product SKU's separated by " . self::ITEM_SEPARATOR;
        $dropdown['Configurable Products'][$i]['value'] = "Sku ABC" . self::ITEM_SEPARATOR . " Sku XYZ" . self::ITEM_SEPARATOR . "...";


        $i++;
        $dropdown['Configurable Products'][$i]['label'] = __("Configurable Attributes");
        $dropdown['Configurable Products'][$i]["id"] = "ConfigurableProduct/attributes";
        $dropdown['Configurable Products'][$i]['style'] = "configurable-product-attribute no-configurable";
        $dropdown['Configurable Products'][$i]['type'] = "List of attribute code separated by " . self::ITEM_SEPARATOR;
        $dropdown['Configurable Products'][$i]['value'] = "color" . self::ITEM_SEPARATOR . " size" . self::ITEM_SEPARATOR . "...";
        return $dropdown;
    }

    public function getIndexes($mapping = [])
    {
        return [1 => "catalogrule_rule", 2 => "catalogrule_product"];
    }

    function getFields($fieldset = null, $form = null, $class = null)
    {
        if ($fieldset == null) {
            return true;
        }


        $fieldset->addField('create_configurable_onthefly', 'select', [
            'label' => __('Create parent of configurable products on the fly'),
            'name' => 'create_configurable_onthefly',
            'id' => 'create_configurable_onthefly',
            'required' => true,
            'values' => [
                [
                    'value' => 0,
                    'label' => __('no')
                ],
                [
                    'value' => 1,
                    'label' => __('yes')
                ]
            ],
            "note" => "<script> 
                require(['jquery'],function($){
                   $('#create_configurable_onthefly').on('change',function(){updateCreateConfigurableOnthefly()});
                   $(document).ready(function(){updateCreateConfigurableOnthefly()});
                   function updateCreateConfigurableOnthefly(){
                        
                        if($('#create_configurable_onthefly').val()==0){
                            $('#mapping-area').addClass('configurableproducts-hidden');
//                            $('.configurableproducts-row').addClass('hidden');
                        }
                        else{
                             $('#mapping-area').removeClass('configurableproducts-hidden');
//                            $('.configurableproducts-row').removeClass('hidden')
                        }
                    }
                }) 
                
                </script>"
        ]);
    }
}
