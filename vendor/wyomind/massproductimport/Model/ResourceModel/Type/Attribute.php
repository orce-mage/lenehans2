<?php

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

/**
 * Class Attribute
 * @package Wyomind\MassProductImport\Model\ResourceModel\Type
 */
class Attribute extends \Wyomind\MassProductImport\Model\ResourceModel\Type\AbstractResource
{

    /**
     * List of the attribute labels
     * @var array
     */
    public $_attributeLabels = [];

    /**
     * @var array
     */
    public $selectAttributes = [];
    /**
     * @var array
     */
    public $attributes = [];
    /**
     * Store the option ids temporary
     * @var array
     */
    protected $_OptionIdRegistry = [];
    /**
     * Store the urlRewrites processed
     * @var array
     */
    public $urlRewriteStoreViews = [];
    /**
     * Separator symbol for multiple values (multiselect attributes)
     */
    const LABEL_SEPARATOR = ",";
    /**
     * Separator symbol for DYNAMIC ATTRIBUTE / VALUE
     */
    const DYNAMIC_ATTRIBUTE_SEPARATOR = "=";
    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    protected $_filterBuilder;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    protected $_searchCriteriaBuilder;
    /**
     * @var \Magento\Framework\Filter\FilterManagerFactory
     */
    protected $_filterManager;
    /**
     * @var \Magento\Store\Model\StoreRepository
     */
    protected $_storeRepository;


    /**
     * @var index of the translation for dropdown attributes
     */
    protected $translationIndex = [];
    /**
     * @var \Wyomind\Framework\Helper\Module
     */
    public $framework;

    /**
     * @var \Wyomind\MassProductImport\Helper\Data
     */
    public $helperData;


    /**
     * @var \Magento\Framework\App\ObjectManager|\Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Attribute constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Wyomind\Framework\Helper\Module $framework
     * @param \Wyomind\MassProductImport\Helper\Data $helperData
     * @param \Magento\Framework\Filter\FilterManagerFactory $filterManager
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Store\Model\StoreRepository $storeRepository
     * @param \Magento\Framework\App\ObjectManager $objectManager
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Wyomind\Framework\Helper\Module $framework,
        \Wyomind\MassProductImport\Helper\Data $helperData,
        \Magento\Framework\Filter\FilterManagerFactory $filterManager,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Store\Model\StoreRepository $storeRepository,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $connectionName = null
    ) {
    

        $this->helperData = $helperData;
        $this->_filterBuilder = $filterBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_filterManager = $filterManager;
        $this->_storeRepository = $storeRepository;
        $this->objectManager = $objectManager;
        parent::__construct($context, $framework, $helperData, $entityAttributeCollection, $connectionName);
        $this->framework = $framework;


        $this->tableEaov = $this->getTable('eav_attribute_option_value');
        $this->tableEaos = $this->getTable('eav_attribute_option_swatch');
        $this->tableEao = $this->getTable('eav_attribute_option');


        $read = $this->getConnection();

        $select = "   SELECT eao.option_id,value,store_id,attribute_id FROM " . $this->tableEao . " AS eao
                        INNER JOIN " . $this->tableEaov . " AS eaov ON eao.option_id = eaov.option_id
                        WHERE store_id=0";

        $dropdownLabels = $read->fetchAll($select);

// collect all optiosn for swatch attributes
        foreach ($dropdownLabels as $attributeLabel) {
            $attributeId = $attributeLabel["attribute_id"];
            $optionId = $attributeLabel["option_id"];
            $this->_attributeLabels[$attributeId][$optionId] = $attributeLabel["value"];
        }


        $fields = ["source_model", "source_model", "source_model", "source_model"];
        $conditions = [
            ["notnull" => true],
            ["neq" => ""],
            ["neq" => "Magento\Eav\Model\Entity\Attribute\Source\Boolean"],
            ["neq" => "Magento\Eav\Model\Entity\Attribute\Source\Table"],
        ];
        $attributes = $this->getAttributesList($fields, $conditions, true);
        foreach ($attributes as $attribute) {
            $sourceModel = $this->objectManager->get($attribute["source_model"]);

            try {
                $options = $sourceModel->getAllOptions();
                foreach ($options as $option) {
                    if (isset($option["value"]) && is_string($option["value"]) && isset($option["label"]) && is_string($option["label"])) {
                        $this->_attributeLabels[$attribute["attribute_id"]][$option["value"]] = (string)$option["label"];
                    }
                }
            } catch (\Throwable $e) {
                // ignore error
            }
        }


    }


    /**
     * Before collecting the queries
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Profile|\Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @param array $columns
     * @throws \Magento\Framework\Exception\InputException
     */
    public function beforeCollect($profile, $columns)
    {
        // attribute id in the mapping
        $ids = [];

        if (isset($columns["Attribute"])) {
            foreach ($columns["Attribute"] as $column) {
                if (isset($column[1])) {
                    $ids[] = $column[1];
                }
            }
// collect all select / multiselect attributes
            $fields = ["frontend_input"];
            $conditions = [
                ["in" =>
                    [
                        "select", "multiselect"
                    ]
                ]
            ];
            $attributes = $this->getAttributesList($fields, $conditions, true);
            foreach ($attributes as $attribute) {
                $swatch = false;
                $swatch_type = false;
                $standardOption = false;
                if (isset($attribute["additional_data"])) {
                    $additional_data = json_decode($attribute["additional_data"]);
                    if (isset($additional_data->swatch_input_type)) {
                        $swatch = true;
                        if ($additional_data->swatch_input_type == 'visual') {
                            $swatch_type = 1;
                        } elseif ($additional_data->swatch_input_type == 'text') {
                            $swatch_type = 0;
                        }
                    }
                }
                if ($attribute['source_model'] == 'Magento\Eav\Model\Entity\Attribute\Source\Table' || $attribute['source_model'] == '' || $attribute['source_model'] == null) {
                    $standardOption = true;
                }
                $this->selectAttributes[$attribute["attribute_id"]] = [
                    "attribute_code" => $attribute["attribute_code"],
                    "swatch" => $swatch,
                    "swatch_type" => $swatch_type,
                    "standard_option" => $standardOption

                ];
            }

            // store all attributes

            $fields = ["backend_type", "attribute_id", "atttribute_code"];
            $attributes = $this->getAttributesList($fields, []);
            foreach ($attributes as $attribute) {
                $this->attributes[$attribute["attribute_code"]] = [
                    "id" => $attribute["attribute_id"],
                    "backend_type" => $attribute["backend_type"],
                ];
            }


//
        }


// collect all option for dropdown attribute
        if (count($ids)) {
        }
// tax class

        $this->taxClasses = $this->helperData->getTaxClasses();
        $this->visibility = $this->helperData->getVisibility();

        parent::beforeCollect($profile, $columns);
    }

    /**
     * Collect all the queries
     * @param int $productId
     * @param string $value
     * @param array $strategy
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Profile|\Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @throws \Exception
     */
    public function collect($productId, $value, $strategy, $profile)
    {
        if ($strategy['option'][0] == "dynamicAttribute") {
            list($attribute_code, $value) = explode(self::DYNAMIC_ATTRIBUTE_SEPARATOR, $value);

            if (!isset($this->attributes[$attribute_code])) {
                return;
            }

            $strategy["option"][0] = $this->attributes[$attribute_code]["backend_type"];
            $strategy["option"][1] = $this->attributes[$attribute_code]["id"];
        }

        list($entityType, $attributeId) = $strategy['option'];
        $attribute_code = null;
        if (isset($strategy['option'][2])) {
            $attribute_code = $strategy['option'][2];
        }

        $table = $this->getTable("catalog_product_entity_" . $entityType);

        switch ($attribute_code) {
            case "url_key":
                $value = "'" . $this->_filterManager->create()->translitUrl($value) . "'";

                break;
            case "visibility":
                $value = strtolower($value);

                if (isset($this->visibility[$value]) || in_array($value, array_map('strtolower', $this->visibility))) {
                    if (in_array($value, array_map('strtolower', $this->visibility))) {
                        $value = array_search($value, array_map('strtolower', $this->visibility));
                    }
                }
                //$strategy['storeviews'] = array(0);
                break;
            case "tax_class_id":
                if ($value == '') {
                    return;
                }
                $value = strtolower($value);

                if (isset($this->taxClasses[$value]) || in_array($value, array_map('strtolower', $this->taxClasses))) {
                    if (in_array($value, array_map('strtolower', $this->taxClasses))) {
                        $value = array_search($value, array_map('strtolower', $this->taxClasses));
                    }
                }

                //$strategy['storeviews'] = array(0);
                break;
            case "status":
                $value = $this->getValue($value);
                if ($value == 0) {
                    $value = 2;
                }
                // $strategy['storeviews'] = array(0);
                break;

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
                            $this->queries[$this->queryIndexer][] = "SET @id=$productId;"; // to avoid update-from-select on the same table whe using other identity than id or sku
                            $data = [
                                "entity_id" => "@id",
                                "store_id" => $storeview,
                                "attribute_id" => "$attributeId",
                            ];
                        }


                        $this->queries[$this->queryIndexer][] = $this->_delete($table, $data);
                    }
                    return;
                }
// if attribute is dropdown, swatch, multiselect
                if (isset($this->selectAttributes[$attributeId])) {
                    $values = explode(self::LABEL_SEPARATOR, $value);

                    $list = ["color" => null, "type" => $this->selectAttributes[$attributeId]["swatch_type"]];
                    foreach ($values as $origValue) {
                        $value = $this->helperData->getValue($origValue);
                        $attributes = $this->helperData->getJsonAttributes();
                        $storeviews = $this->helperData->prepareStorewiewsParameters($attributes);
                        $list = $list + $storeviews;

                        $parameter = $this->helperData->prepareFields($list, $origValue);
                        $optionVal = $parameter["color"];

                        $type = ($parameter["type"] == "'image'") ? 2 : $parameter["type"];
                        if ($value != "") {
// if the option_id exists for this label
                            $optionId = false;

                            if (isset($this->_attributeLabels[$attributeId])) {
                                $optionId = array_search($value, $this->_attributeLabels[$attributeId]);
                                if (!$optionId && $this->selectAttributes[$attributeId]['standard_option'] === false) {
                                    $optionId = $value; // attribute has a source model, and imported value couldn't be found as a label. Try to set it directly as the attribute value
                                }
                            }
                            if ($optionId) {
                                $val[] = $this->helperData->sanitizeField($optionId);
                                if ($this->selectAttributes[$attributeId]["swatch"]) {
                                    // update new value for swatch
                                    if ($optionVal == "''") { // color property not imported, leave existing color as it is
                                        // nothing
                                    } elseif ($optionVal == "'empty'") { // color property imported its value is "empty" clear existing color
                                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableEaos, [
                                                "option_id" => $optionId,
                                                "type" => $type,
                                                "value" => 'null'
                                            ]);
                                    } else { // color property imported, update existing color
                                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableEaos, [
                                                "option_id" => $optionId,
                                                "type" => $type,
                                                "value" => $optionVal
                                            ]);
                                    }
                                }
                            } //else the option_id and label is inserted
                            else {
                                // if option_id not yet added
                                $md5 = "md5";
                                if (!isset($this->_OptionIdRegistry[$md5($attributeId . $value)])) {
                                    $this->queries[$this->queryIndexer][] = "INSERT INTO `$this->tableEao` (`attribute_id`) VALUES ( '$attributeId');";
                                    $this->queries[$this->queryIndexer][] = "SELECT @option_id:=LAST_INSERT_ID();";
                                    // insert new value for dropdown
                                    $this->queries[$this->queryIndexer][] = "INSERT INTO `$this->tableEaov` (`option_id`,`value`) VALUES ( @option_id,'" . str_replace("'", "''", $value) . "');";
                                    if ($this->selectAttributes[$attributeId]["swatch"]) {
                                        // insert new value for swatch
                                        if ($optionVal == "''") {
                                            $optionVal = "'" . $value . "'";
                                        }
                                        $this->queries[$this->queryIndexer][] = "INSERT INTO `$this->tableEaos` (`option_id`,`type`,`value`) VALUES ( @option_id," . $this->selectAttributes[$attributeId]["swatch_type"] . "," . $optionVal . ");";
                                    }

                                    $this->_OptionIdRegistry[$md5($attributeId . $value)] = true;
                                }
                                $val[] = "(SELECT eao.option_id FROM `$this->tableEao`  eao INNER JOIN `$this->tableEaov`  eaov ON eao.option_id=eaov.option_id WHERE attribute_id='$attributeId' AND value='" . str_replace("'", "''", $value) . "' LIMIT 1)";
                            }
                        }


                        foreach ($this->helperData->getStoreviews($attributes) as $storeview) {
                            if (isset($parameter[$storeview["code"]])) {
                                $this->translationIndex[$attributeId][$value][$storeview["value"]] = $parameter[$storeview["code"]];
                            }
                        }
                    }

// basic attribute
                } else {
                    $val[] = $this->helperData->sanitizeField($value);
                }
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
        parent::collect($productId, $value, $strategy, $profile);
    }

    /**
     * @return string|void
     */
    public function afterCollect()
    {
        if (!empty($this->translationIndex)) {
            $this->queries[$this->queryIndexer][] = "/** DROPDOWN ATTRIBUTE TRANSLATIONS */";
            foreach ($this->translationIndex as $attributeId => $values) {
                foreach ($values as $value => $translation) {
                    foreach ($translation as $storeId => $translatedValue) {
                        if ($translatedValue != "''" && $translatedValue != "'#empty#'") {
                            $data = [
                                "store_id" => $storeId,
                                "option_id" => new \Zend_Db_Expr("(SELECT " . $this->tableEao . ".option_id FROM " . $this->tableEao . "  INNER JOIN " . $this->tableEaov . " AS eaov ON store_id=0  AND " . $this->tableEao . ".option_id=eaov.option_id WHERE attribute_id=" . $attributeId . " AND BINARY value=" . $this->helperData->sanitizeField($value) . ")"),
                                "value" => $translatedValue
                            ];

                            $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableEaov, $data, true);
                        } elseif ($translatedValue == "'#empty#'") {
                            $deleteJoinQuery = "DELETE eaov_locale FROM " . $this->tableEao . " eao JOIN " . $this->tableEaov . " AS eaov_base ON eaov_base.store_id=0 AND BINARY eaov_base.value=" . $this->helperData->sanitizeField($value) . " JOIN " . $this->tableEaov . " AS eaov_locale ON eaov_locale.store_id=$storeId AND eaov_base.option_id=eaov_locale.option_id  WHERE eao.attribute_id=$attributeId;";
                            $this->queries[$this->queryIndexer][] = $deleteJoinQuery;
                        }
                    };
                };
            };
        };


        parent::afterCollect();
    }


    /**
     * Dropdown entries
     * @return array
     */
    public function getDropdown()
    {

        /* ATTIBUTE MAPPING */
        $dropdown = [];
        $attributesList = $this->getAttributesList();

        $i = 0;
        foreach ($attributesList as $attribute) {
//         is_global

            if (!empty($attribute['frontend_label'])) {
                $storeviewsDependent = "";
                if ($attribute['is_global'] != 1) {
                    $storeviewsDependent = "storeviews-dependent";
                }
                $dropdown['Attributes'][$i]['label'] = $attribute['frontend_label'];
                $dropdown['Attributes'][$i]["id"] = "Attribute/" . $attribute['backend_type'] . "/" . $attribute['attribute_id'];
                $dropdown['Attributes'][$i]['style'] = "Attribute $storeviewsDependent";
                if ($attribute["frontend_input"] == "select") {
                    $dropdown['Attributes'][$i]['type'] = "Option value name (case sensitive)";
                    if (isset($this->_attributeLabels[$attribute['attribute_id']]) && $attribute['frontend_label'] != "Google Merchant Center Category") {
                        $dropdown['Attributes'][$i]['options'] = ($this->_attributeLabels[$attribute['attribute_id']]);
                        $dropdown['Attributes'][$i]['newable'] = true;
                    }
                } elseif ($attribute["frontend_input"] == "multiselect") {
                    $dropdown['Attributes'][$i]['type'] = "Option value names (case sensitive) separated by " . self::LABEL_SEPARATOR;
                    $dropdown['Attributes'][$i]['options'] = null;
                    if (isset($this->_attributeLabels[$attribute['attribute_id']]) && $attribute['frontend_label'] != "Google Merchant Center Category") {
                        $dropdown['Attributes'][$i]['options'] = ($this->_attributeLabels[$attribute['attribute_id']]);
                        $dropdown['Attributes'][$i]['multiple'] = true;
                        $dropdown['Attributes'][$i]['newable'] = true;
                    }
                } else {
                    $dropdown['Attributes'][$i]['type'] = $this->{$attribute['backend_type']};
                }
                $i++;
            }
        }
        if ($this->framework->moduleIsEnabled("Ves_Brand")) {
            $dropdown['Attributes'][$i]['label'] = "Venus - Brand Name";
            $dropdown['Attributes'][$i]["id"] = "Ignored/ves_brand_id";
            $dropdown['Attributes'][$i]['style'] = "Attribute";
            $dropdown['Attributes'][$i]['type'] = "Brand Id";
            $i++;
        }


        $dropdown['Dynamic Attributes'][$i]['label'] = "Dynamic Attribute ";
        $dropdown['Dynamic Attributes'][$i]["id"] = "Attribute/dynamicAttribute";
        $dropdown['Dynamic Attributes'][$i]['style'] = "Attribute";
        $dropdown['Dynamic Attributes'][$i]['type'] = "Dynamic Attribute";
        $i++;


        return $dropdown;
    }

    /**
     * index to process
     * @param array $mapping
     * @return array
     */
    public function getIndexes($mapping = [])
    {
        $indexes = [0 => "catalog_product_attribute"];
        $storeviews = [];

        foreach ($mapping as $i => $map) {
            if (is_object($map)) {
                if (!property_exists($map, 'enabled')) {
                    continue;
                }
            } else {
                if (!array_key_exists('enabled', $map)) {
                    continue;
                }
            }


            $strategy = explode("/", $map->id);

            if (isset($strategy[3]) && $strategy[3] == "url_key") {
                $storeviews = array_merge($storeviews, $mapping[$i]->storeviews);
            }
        }

        $this->urlRewriteStoreViews = array_unique($storeviews);
        if (in_array(0, $this->urlRewriteStoreViews)) {
            $this->urlRewriteStoreViews = [];
            $stores = $this->_storeRepository->getList();
            foreach ($stores as $store) {
                $this->urlRewriteStoreViews[] = $store["store_id"];
            }
        }

        $indexes[10] = "catalog_url";
        $indexes[100] = "catalogsearch_fulltext";
        if ($this->framework->getStoreConfig("catalog/frontend/flat_catalog_product")) {
            $indexes[1001] = "catalog_product_flat";
        }
        return $indexes;
    }
}
