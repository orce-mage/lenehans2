<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

/**
 * Class Category
 * @package Wyomind\MassProductImport\Model\ResourceModel\Type
 */
class Category extends \Wyomind\MassProductImport\Model\ResourceModel\Type\AbstractResource
{
    /**
     * @var string
     */
    const CATEGORY_PATH_SEPARATOR = ",";

    /**
     * @var string
     */
    const CATEGORY_LEVEL_SEPARATOR = "/";

    /**
     * @var array
     */
    const ATTRIBUTES = [
        "name" => [
            "type" => "varchar"
        ],
        "is_active" => [
            "type" => "int"
        ],
        "include_in_menu" => [
            "type" => "int"
        ],
        "display_mode" => [
            "default" => "PRODUCT",
            "type" => "varchar"
        ],
        "is_anchor" => [
            "default" => 1,
            "type" => "int"
        ],
        "url_key" => [
            "type" => "varchar"
        ],
//        "url_path" => array(
//            "type" => "varchar"
//        ),
    ];

    /**
     * @var array
     */
    public $_mapping = [];
    /**
     * @var
     */
    public $_autoIncrementvalue = 1;
    /**
     * @var null
     */
    public $_increment = null;

    /**
     * @var null
     */
    public $_sequenceIncrement = null;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    public $_categoryCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryRepository
     */
    public $_categoryRepository;

    /**
     * @var array
     */
    public $_categoryNames = [];

    /**
     * @var array
     */
    public $_categoryPaths = [];

    /**
     * @var array
     */
    public $_categoryLevels = [];

    /**
     * @var array
     */
    public $_categoryIds = [];

    /**
     * @var array
     */
    public $_categoryData = [];

    /**
     * @var array
     */
    public $_categoryRegistry = [];

    /**
     * @var \Magento\Framework\Filter\FilterManagerFactory|null
     */
    public $_filterManager = null;

    /**
     * @var bool
     */
    public $_rootCategoryId = false;

    /**
     * @var array
     */
    public $_categoryProductPosition = [];

    /**
     * @var array
     */
    public $_storeRootIds = [];

    /**
     * Category constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Wyomind\Framework\Helper\Module $framework
     * @param \Wyomind\MassProductImport\Helper\Data $helperData
     * @param \Magento\Framework\Filter\FilterManagerFactory $filterManager
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection
     * @param \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
     * @param \Magento\Catalog\Model\CategoryRepository $categoryRepository
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Wyomind\Framework\Helper\Module $framework,
        \Wyomind\MassProductImport\Helper\Data $helperData,
        \Magento\Framework\Filter\FilterManagerFactory $filterManager,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\CategoryRepository $categoryRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $connectionName = null
    ) {
    
        parent::__construct($context, $framework, $helperData, $entityAttributeCollection, $connectionName);
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_categoryRepository = $categoryRepository;
        $this->_filterManager = $filterManager;
        $this->_storeManager = $storeManager;
    }

    public function _construct()
    {
        $this->table = $this->getTable("catalog_category_product");
        $this->tableSequence = $this->getTable("sequence_catalog_category");
        $this->tableEntity = $this->getTable("catalog_category_entity");
        $this->tableEntityVarchar = $this->getTable("catalog_category_entity_varchar");
        $this->tableEntityInt = $this->getTable("catalog_category_entity_int");

        parent::_construct();
    }

    /**
     * @param array $mapping
     * @return array
     */
    public function getIndexes($mapping = [])
    {
        $indexes = [3 => "catalog_category_product", 4 => "catalog_product_category"];
        if ($this->framework->getStoreConfig("catalog/frontend/flat_catalog_category")) {
            $indexes[1001] = "catalog_category_flat";
        }
        return $indexes;
    }

    /**
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Profile|\Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @param array $columns
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeCollect($profile, $columns)
    {
        /* get entity type id */
        $read = $this->getConnection();
        $tableEet = $this->getTable('eav_entity_type');
        $select = $read->select()->from($tableEet)->where('entity_type_code=\'catalog_category\'');
        $data = $read->fetchAll($select);
        $this->categoryEntityTypeId = $data[0]['entity_type_id'];

        /* get all position */
        $read = $this->getConnection();
        $select = $read->select()->from($this->table)->reset(\Zend_Db_Select::COLUMNS)->columns(["CONCAT(product_id,'_',category_id) as identifier", "position"]);
        $data = $read->fetchPairs($select);
        $this->_categoryProductPosition = $data;

        /*Category root id*/
        $this->_rootCategoryId = \Magento\Catalog\Model\Category::TREE_ROOT_ID;

        /*Get All root category*/
        $storeRoots = [];
        foreach ($this->_storeManager->getStores() as $store) {
            $storeRoots[$store->getId()] = $store->getRootCategoryId();
        }
        $this->_storeRootIds = $storeRoots;

        foreach (self::ATTRIBUTES as $key => $attribute) {
            /* get name attribute id */
            $tableEava = $this->getTable('eav_attribute');
            $select = $read->select()->from($tableEava)->where("attribute_code='$key' AND entity_type_id=" . $this->categoryEntityTypeId);
            $data = $read->fetchAll($select);
            $var = $key . "_id";
            $this->$var = $data[0]['attribute_id'];
        }

        if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
            $this->_increment = $this->getNextAutoincrement(true);
        } else {
            $this->_increment = $this->getNextAutoincrement();
        }
        $this->_autoIncrementvalue = $this->getAutoincrementValue();
        $this->_categoryNames = $this->getCategories(false, false, "name");

        $this->_categoryData = $this->getCategories(false, false);


        parent::beforeCollect($profile, $columns);
    }

    /**
     * @param int $productId
     * @param string $value
     * @param array $strategy
     * @param \Wyomind\MassProductImport\Model\ResourceModel\Profile|\Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @throws \Exception
     */
    public function collect($productId, $value, $strategy, $profile)
    {
        $parentId = $profile->getCategoryParentId();
        $isActive = $profile->getCategoryIsActive();
        $includeInMenu = $profile->getCategoryIncludeInMenu();

        if (trim($value) != "") {
            if ($strategy["option"][0] == "mapping") {
                $categoryPrefix = [];
                foreach ($strategy["storeviews"] as $storeId) {
                    if ($storeId == 0) {
                        $categoryPrefix[] = "path LIKE '" . $this->_rootCategoryId . "/%'";
                        break;
                    }
                    $categoryPrefix[] = "path LIKE '" . $this->_rootCategoryId . "/" . $this->_storeRootIds[$storeId] . "/%'";
                };
                $like = "(" . implode(" OR ", $categoryPrefix) . ")";

                $this->queries[$this->queryIndexer][] = "DELETE `" . $this->table . "` FROM `" . $this->table . "` 
            INNER JOIN " . $this->tableEntity . " ON " . $this->tableEntity . ".entity_id = " . $this->table . ".category_id AND " . $like . "
            WHERE product_id=" . $productId . ";";
            }

            $values = explode(self::CATEGORY_PATH_SEPARATOR, $value);
            $values = array_unique($values);

            foreach ($values as $value) {
                if (empty(trim($value))) {
                    continue;
                }

                if ($profile->getTreeDetection() == false) {
                    $data = $this->helperData->prepareFields(["position" => 0], $value, "position");
                    $position = $data["position"];
                    $value = $this->helperData->getValue($value);
                    if (is_integer((int)$value) && (int)$value > 0) {
                        if (!isset($this->_categoryData["cat-" . $value])) {
                            //Invalid category Id
                            continue;
                        } else {
                            $categoryId = $value;
                        }
                    } elseif (isset($this->_categoryNames[strtolower(trim($value))])) {
                        // check the label to find the category id;
                        $categoryId = $this->_categoryNames[strtolower(trim($value))];
                    } else {
                        // create a new category
                        $md5 = "md5";
                        if ($profile->getCreateCategoryOnthefly() && $strategy["option"][0] != "remove") {
                            if (!isset($this->_categoryRegistry[$md5(strtolower(trim($value)))])) {
                                $parentPath = $this->_categoryData["cat-" . $parentId]["path"];
                                $parentLevel = $this->_categoryData["cat-" . $parentId]["level"];
                                $this->createCategory($parentId, $parentPath, $parentLevel, $value, $isActive, $includeInMenu);
                                $this->_categoryRegistry[$md5(strtolower(trim($value)))] = true;
                            }
                            $categoryId = "(SELECT e.entity_id FROM $this->tableEntity AS e INNER JOIN $this->tableEntityVarchar AS ev ON e.entity_id=ev.entity_id AND attribute_id=$this->name_id AND store_id=0 AND value='" . str_replace("'", "''", $value) . "' LIMIT 1)";
                        } else {
                            $categoryId = 0;
                        }
                    }
                    if ($categoryId) {
                        if (!$position) {
                            $position = 0;
                            if (isset($this->_categoryProductPosition[$productId . '_' . $categoryId])) {
                                $position = $this->_categoryProductPosition[$productId . '_' . $categoryId];
                            }
                        }
                        if ($strategy["option"][0] != "remove") {
                            $data = ["category_id" => $categoryId, "product_id" => $productId, "position" => $position];
                            $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->table, $data);
                        } else {
                            $this->queries[$this->queryIndexer][] = "DELETE FROM `" . $this->table . "`  WHERE  category_id ='" . $categoryId . "' AND product_id=" . $productId . ";";
                        }
                    }
                } else {
                    // Tree detection
                    $values = explode(self::CATEGORY_LEVEL_SEPARATOR, $value);

                    if (count($values)) {
                        $path = $this->_rootCategoryId;
                        $parentId = $this->_rootCategoryId;
                        foreach ($values as $value) {
                            $parameter = $this->helperData->prepareFields(["position" => 0], $value, "position");
                            $position = $parameter["position"];
                            $value = $this->helperData->getValue($value);
                            // get entity_id
                            $categoryId = $this->getCategoryIdByName($value, $path);

                            if (!$categoryId) {
                                if (!$profile->getCreateCategoryOnthefly() || $strategy["option"][0] == "remove") {
                                    break 2;
                                } else {
                                    // create new category
                                    $parentPath = $this->_categoryData["cat-" . $parentId]["path"];
                                    $parentLevel = $this->_categoryData["cat-" . $parentId]["level"];
                                    $this->createCategory($parentId, $parentPath, $parentLevel, $value, $isActive, $includeInMenu);

                                    // store the new category data

                                    $this->_categoryData["cat-" . $this->_increment] = [
                                        'id' => $this->_increment,
                                        "path" => $parentPath . "/" . $this->_increment,
                                        "name" => $value,
                                        "level" => $parentLevel + 1,
                                    ];


                                    $categoryId = $this->_increment;
                                    $this->_increment += $this->_autoIncrementvalue;
                                }
                            }
                            $path .= "/" . $categoryId;
                            $parentId = $categoryId;
                        }
//                        if (isset($categoryId)) {
                        if (!$position) {
                            $position = 0;
                            if (isset($this->_categoryProductPosition[$productId . '_' . $categoryId])) {
                                $position = $this->_categoryProductPosition[$productId . '_' . $categoryId];
                            }
                        }

                        if ($strategy["option"][0] != "remove") {
                            $data = ["category_id" => $categoryId, "product_id" => $productId, "position" => $position];
                            $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->table, $data);
                        } else {
                            $this->queries[$this->queryIndexer][] = "DELETE FROM `" . $this->table . "`  WHERE  category_id ='" . $categoryId . "' AND product_id=" . $productId . ";";
                        }
//                        }
                    }
                }
            }

            parent::collect($productId, $value, $strategy, $profile);
        }
    }

    /**
     * Create new categories
     * @param $parentId
     * @param $parentPath
     * @param $parentLevel
     * @param $value
     * @param $isActive
     * @param $includeInMenu
     */
    public function createCategory(
        $parentId,
        $parentPath,
        $parentLevel,
        $value,
        $isActive,
        $includeInMenu
    ) {
    

        if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
            $this->queries[$this->queryIndexer][] = "INSERT INTO `" . $this->tableSequence . "` VALUES (sequence_value=NULL);";
            $this->queries[$this->queryIndexer][] = "SET @sequence_id=LAST_INSERT_ID();";

            $this->queries[$this->queryIndexer][] = "INSERT INTO `$this->tableEntity` (entity_id,attribute_set_id,parent_id,created_at,updated_at,created_in,updated_in, position)"
                . "VALUES(@sequence_id,$this->categoryEntityTypeId,$parentId,'" . date("Y-m-d H:i:s") . "','" . date("Y-m-d H:i:s") . "','1','2147483647', (SELECT ifnull(max(controlcategory.position),0) + 1 from ". $this->tableEntity ." AS controlcategory WHERE controlcategory.parent_id = $parentId));";

            $this->queries[$this->queryIndexer][] = "SET @row_id=LAST_INSERT_ID();";
            $this->queries[$this->queryIndexer][] = "UPDATE  `$this->tableEntity` SET path=CONCAT('$parentPath','/',@sequence_id), level=$parentLevel+1 WHERE row_id=@row_id;";
        } else {
            $this->queries[$this->queryIndexer][] = "INSERT INTO `$this->tableEntity` (entity_id,attribute_set_id,parent_id,created_at,updated_at, position)"
                . "VALUES(NULL,$this->categoryEntityTypeId,$parentId,'" . date("Y-m-d H:i:s") . "','" . date("Y-m-d H:i:s") . "', (SELECT ifnull(max(controlcategory.position),0) + 1 from ". $this->tableEntity ." AS controlcategory WHERE controlcategory.parent_id = $parentId));";
            $this->queries[$this->queryIndexer][] = "SET @category_id=LAST_INSERT_ID();";
            $this->queries[$this->queryIndexer][] = "UPDATE  `$this->tableEntity` SET path=CONCAT('$parentPath','/',@category_id), level=$parentLevel+1 WHERE entity_id=@category_id;";
        }


        foreach (self::ATTRIBUTES as $key => $attribute) {
            $var = $key . "_id";
            switch ($key) {
                case "name":
                    $val = str_replace("'", "''", $value);
                    break;
                case "is_active":
                    $val = $isActive;
                    break;
                case "include_in_menu":
                    $val = $includeInMenu;
                    break;
                case "url_key":
                    $val = $this->_filterManager->create()->translitUrl($value);
                    break;
                case "url_path":
                    $val = "url_path";
                    break;
                default:
                    $val = $attribute["default"];
            }

            if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
                $type = "row_id";
                $variable = "@row_id";
            } else {
                $type = "entity_id";
                $variable = "@category_id";
            }
            $table = "tableEntity" . ucfirst($attribute["type"]);

            if ($key == 'url_key') { // do a special insert for the url key case: avoid url key duplication
                $this->queries[$this->queryIndexer][] = "INSERT INTO `" . $this->$table . "` (attribute_id, store_id, $type, value) ("
                    . "SELECT " . $this->$var . ",0,$variable, IF(IFNULL(MAX(IF(value regexp '^$val-[0-9]*$', SUBSTRING_INDEX(value, '-', -1),''))+1,'')='',"
                    . " '$val',"
                    . " CONCAT('$val','-',IFNULL(MAX(IF(value REGEXP '^$val-[0-9]*$', SUBSTRING_INDEX(value, '-', -1),''))+1,'')))"
                    . " FROM " . $this->tableEntityVarchar
                    . " WHERE (SELECT count(*) FROM " . $this->tableEntityVarchar . " WHERE value = '$val' AND attribute_id='" . $this->$var . "' AND store_id = 0) >= 1"
                    . ");";
            } else {
                $this->queries[$this->queryIndexer][] = "INSERT INTO `" . $this->$table . "`  (attribute_id,store_id,$type,value) VALUES (" . $this->$var . ",0,$variable,'$val');";
            }
        }
    }

    /**
     * @return string
     */
    public function afterCollect()
    {
        $this->queries[$this->queryIndexer][] = "UPDATE " . $this->tableEntity . " SET children_count = ("
            . "SELECT COUNT(*) FROM (SELECT * FROM " . $this->tableEntity . ") AS category_alias WHERE path LIKE CONCAT(" . $this->tableEntity . ".path, '/%'));";

        return parent::afterCollect();
    }

    /**
     * @return array
     */
    public function getDropdown()
    {
        $dropdown = [];
        /* OTHER MAPPING */
        $i = 0;
        $dropdown['Category'][$i]['label'] = __("Replace all categories with");
        $dropdown['Category'][$i]["id"] = "Category/mapping";
        $dropdown['Category'][$i]['style'] = "Category";
        $dropdown['Category'][$i]['type'] = "List of category paths (case sensitive) separated by " . self::CATEGORY_LEVEL_SEPARATOR . "  or category ids separated by" . self::CATEGORY_PATH_SEPARATOR;
        $dropdown['Category'][$i]['value'] = "Category A" . self::CATEGORY_PATH_SEPARATOR . " Category B" . self::CATEGORY_PATH_SEPARATOR . "...";
        $i++;
        $dropdown['Category'][$i]['label'] = __("Add to the categories");
        $dropdown['Category'][$i]["id"] = "Category/add";
        $dropdown['Category'][$i]['style'] = "Category";
        $dropdown['Category'][$i]['type'] = "List of category paths (case sensitive) separated by " . self::CATEGORY_LEVEL_SEPARATOR . "  or category ids separated by" . self::CATEGORY_PATH_SEPARATOR;
        $dropdown['Category'][$i]['value'] = "Category A" . self::CATEGORY_PATH_SEPARATOR . " Category B" . self::CATEGORY_PATH_SEPARATOR . "...";
        $i++;
        $dropdown['Category'][$i]['label'] = __("Remove from the categories");
        $dropdown['Category'][$i]["id"] = "Category/remove";
        $dropdown['Category'][$i]['style'] = "Category";
        $dropdown['Category'][$i]['type'] = "List of category paths (case sensitive) separated by " . self::CATEGORY_LEVEL_SEPARATOR . "  or category ids separated by" . self::CATEGORY_PATH_SEPARATOR;
        $dropdown['Category'][$i]['value'] = "Category A" . self::CATEGORY_PATH_SEPARATOR . " Category B" . self::CATEGORY_PATH_SEPARATOR . "...";

        return $dropdown;
    }

    /**
     * @param null $fieldset
     * @param null $form
     * @param null $class
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    function getFields($fieldset = null, $form = null, $class = null)
    {
        if ($fieldset == null) {
            return true;
        }

        $fieldset->addField(
            'create_category_onthefly',
            'select',
            [
                'name' => 'create_category_onthefly',
                'label' => __('Create categories on the fly'),
                "required" => true,
                'values' => [
                    [
                        'value' => 0,
                        'label' => 'No'
                    ],
                    [
                        'value' => 1,
                        'label' => 'Yes'
                    ],
                ],
                'note' => "",
            ]
        );

        $fieldset->addField(
            'category_is_active',
            'select',
            [
                'name' => 'category_is_active',
                'label' => __('New categories active by default'),
                "required" => true,
                'values' => [
                    [
                        'value' => 0,
                        'label' => 'No'
                    ],
                    [
                        'value' => 1,
                        'label' => 'Yes'
                    ],
                ],
                'note' => "",
            ]
        );

        $fieldset->addField(
            'category_include_in_menu',
            'select',
            [
                'name' => 'category_include_in_menu',
                'label' => __('New categories included in menu by default'),
                "required" => true,
                'values' => [
                    [
                        'value' => 0,
                        'label' => 'No'
                    ],
                    [
                        'value' => 1,
                        'label' => 'Yes'
                    ],
                ],
                'note' => "",
            ]
        );


        $fieldset->addField(
            'tree_detection',
            'select',
            [
                'name' => 'tree_detection',
                'label' => __('Category tree auto-detection'),
                "required" => true,
                'values' => [
                    [
                        'value' => 0,
                        'label' => 'No'
                    ],
                    [
                        'value' => 1,
                        'label' => 'Yes'
                    ],
                ],
                'note' => "Category levels must be separated by slashes ( / )
            <script> 
                require(['jquery'],function($){
                   $('#tree_detection,#create_category_onthefly').on('change',function(){updateCategoryParentId()});
                   $(document).ready(function(){updateCategoryParentId()});
                   function updateCategoryParentId(){
                   setTimeout(function(){
                        if($('#tree_detection').val()==0 && $('#create_category_onthefly').val()!=0){
                            $('.field-category_parent_id').css('display','')
                        }
                        else{
                            $('.field-category_parent_id').css('display','none')
                        }
                        },100)
                    }
                }) 
                
                </script>"
            ]
        );
        $fieldset->addField(
            'category_parent_id',
            'select',
            [
                'name' => 'category_parent_id',
                'label' => __('New categories are children of'),
                "required" => true,
                'values' => $this->getCategories(),
                'note' => "",
            ]
        );
    }

    /**
     * @param bool $name
     * @param bool $path
     * @return bool
     */
    function getCategoryIdByName($name = false, $path = false)
    {
        foreach ($this->_categoryData as $key => $categoryData) {
            $pathStartWithChunk = explode("/", $categoryData["path"]);
            array_pop($pathStartWithChunk);
            $pathStartWith = implode("/", $pathStartWithChunk);

            if (strtolower($categoryData["name"]) == strtolower($name) && (string)$pathStartWith === (string)$path) {
                return $categoryData["id"];
            }
        }
        return false;
    }

    function getAutoincrementValue()
    {
        $query = "SHOW VARIABLES LIKE 'auto_increment_increment';";
        $writeConnection = $this->getConnection('core_write');
        $result = $writeConnection->rawFetchRow($query, "Value");
        if ($result) {
            return (int)$result;
        } else {
            return "1";
        }
    }

    /**
     * @param bool $sequence
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getNextAutoincrement($sequence = false)
    {
        $connection = $this->getConnection();
        if ($sequence) {
            $entityStatus = $connection->showTableStatus($this->tableSequence);
        } else {
            $entityStatus = $connection->showTableStatus($this->tableEntity);
        }


        if (empty($entityStatus['Auto_increment'])) {
            throw new \Magento\Framework\Exception\LocalizedException(__('Cannot get autoincrement value'));
        }
        return $entityStatus['Auto_increment'];
    }

    /**
     * @param bool $category
     * @param bool $dropdown
     * @param null $field
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getCategories($category = false, $dropdown = true, $field = null)
    {
        $options = [];
        if (!$category) {
            $category = $this->_categoryRepository->get(\Magento\Catalog\Model\Category::TREE_ROOT_ID, 0);
        }

        //if ($category->getLevel() > 0) {
        if ($dropdown) {
            $label = trim(str_repeat('--', $category->getLevel()) . ' ' . $category->getName());
            $options[] = [
                'value' => $category->getEntityId(),
                'label' => $label,
            ];
        } else {
            if ($field == "name") {
                $method = "get" . ucwords($field);
                $options[strtolower($category->$method())] = $category->getEntityId();
            } else {
                $method = "get" . ucwords($field);
                $options["cat-" . $category->getEntityId()] = [
                    'id' => $category->getEntityId(),
                    "path" => $category->getPath(),
                    "name" => $category->getName(),
                    "level" => $category->getLevel(),
                ];
            }
        }
        // }

        if ($category->getChildrenCount()) {
            $children = $this->_categoryCollectionFactory->create();
            $children->addAttributeToSelect('name')
                ->addFieldToFilter('parent_id', $category->getEntityId())
                ->setStore(0);

            foreach ($children as $child) {

                /** @var Mage_Catalog_Model_Category $child */
                $options = array_merge($options, $this->getCategories($child, $dropdown, $field));
            }
        }

        return $options;
    }
}
