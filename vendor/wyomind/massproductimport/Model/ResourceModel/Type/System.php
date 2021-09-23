<?php
/**
 * Copyright © 2019 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImport\Model\ResourceModel\Type;

/**
 *
 * @exclude_var e
 */
class System extends \Wyomind\MassStockUpdate\Model\ResourceModel\Type\System
{
    /**
     * @var
     */
    public $fields;

    public $indexes = [
        1 => "catalogrule_rule",
        2 => "catalogrule_product"
    ];


    /**
     * @var array
     */
    public $attributeSet = [];

    /**
     * @var array
     */
    public $website = [];

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory|null
     */
    public $_attributeSetCollectionFactory = null;

    /**
     * @var \Magento\Store\Model\WebsiteRepository|null
     */
    public $_websiteRepository = null;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime|null
     */
    public $_coreDate = null;

    /**
     * @var null|\Wyomind\MassProductImport\Helper\Data
     */
    public $_helperData = null;

    /**
     * @var array
     */
    public $removeQueries = [];

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    public $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\App\ObjectManager
     */
    public $objectManager;

    const WEBSITE_SEPARATOR = ",";


    /**
     * System constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Wyomind\Framework\Helper\Module $framework
     * @param \Wyomind\MassProductImport\Helper\Data $helperData
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $coreDate
     * @param \Magento\Store\Model\WebsiteRepository $websiteRepository
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Wyomind\Framework\Helper\Module $framework,
        \Wyomind\MassProductImport\Helper\Data $helperData,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $attributeSetCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $coreDate,
        \Magento\Store\Model\WebsiteRepository $websiteRepository,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $connectionName = null
    ) {
    
        $this->_attributeSetCollectionFactory = $attributeSetCollectionFactory;
        $this->helperData = $helperData;
        $this->_websiteRepository = $websiteRepository;
        $this->_coreDate = $coreDate;
        $this->_framework = $framework;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->objectManager = $objectManager;
        parent::__construct($context, $framework, $helperData, $entityAttributeCollection, $connectionName);
    }

    /**
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _construct()
    {
        $this->table = $this->getTable("catalog_product_entity");
        $this->tableSequence = $this->getTable("sequence_product");
        $this->tableCpei = $this->getTable("catalog_product_entity_int");
        $this->tableCpev = $this->getTable("catalog_product_entity_varchar");
        $this->tableCpw = $this->getTable("catalog_product_website");
        $this->tableCsi = $this->getTable("cataloginventory_stock_item");
        $this->tableEa = $this->getTable("eav_attribute");
        $this->tableUr = $this->getTable("url_rewrite");

        $read = $this->getConnection();
        $tableEet = $this->getTable('eav_entity_type');
        $select = $read->select()->from($tableEet)->where('entity_type_code=\'catalog_product\'');
        $data = $read->fetchAll($select);
        $typeId = $data[0]['entity_type_id'];


        /*  Liste des  attributs disponible dans la bdd */
        $attributeSetList = $this->_attributeSetCollectionFactory->create()->setEntityTypeFilter($typeId);
        $attributeSetList->addFieldToFilter("entity_type_id", ["eq" => $typeId]);
        $attributeSetCollection = $attributeSetList->getData();
        foreach ($attributeSetCollection as $id => $attributeSet) {
            $entityTypeId = $attributeSet["entity_type_id"];
            $name = $attributeSet["attribute_set_name"];
            $this->attributeSet[$attributeSet["attribute_set_id"]] = strtolower($name);
        }

        foreach ($this->_websiteRepository->getList() as $website) {
            $this->websites[$website->getId()] = strtolower($website->getName());
        }


        parent::_construct();
    }

    /**
     * @param \Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @param array $columns
     */
    public function beforeCollect($profile, $columns)
    {
        //special action for product that are missing from the file
        $action = $profile->getProductRemoval();
        $target = $profile->getProductTarget();
        $indexes = [];
        $sql = [];
        if ($action) {
            switch ($action) {
                //Disable product
                case 1:
                    if ($target == 0) {
                        if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
                            // Only products related to current profile
                            $sql[] = "UPDATE " . $this->tableCpei .
                                " INNER JOIN " . $this->table . " ON " . $this->table . ".row_id = " . $this->tableCpei . ".row_id SET value=2"
                                . " WHERE `attribute_id`=(SELECT attribute_id FROM " . $this->tableEa . " WHERE attribute_code='status' and entity_type_id = 4)"
                                . " AND " . $this->tableCpei . ".row_id=(SELECT max(row_id) FROM " . $this->table . " WHERE entity_id = %s)"
                                . " AND (" . $this->table . ".created_by = " . $profile->getId() . " OR " . $this->table . ".updated_by = " . $profile->getId() . ");";
                        } else {
                            // Only products related to current profile
                            $sql[] = "UPDATE " . $this->tableCpei .
                                " INNER JOIN " . $this->table . " ON " . $this->table . ".entity_id = " . $this->tableCpei . ".entity_id SET value=2"
                                . " WHERE `attribute_id`=(SELECT attribute_id FROM " . $this->tableEa . " WHERE attribute_code='status' and entity_type_id = 4)"
                                . " AND " . $this->tableCpei . ".entity_id=%s AND (" . $this->table . ".created_by = " . $profile->getId() . " OR " . $this->table . ".updated_by = " . $profile->getId() . ");";
                        }
                    } elseif ($target == 1) {
                        if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
                            // Only products related to current profile
                            $sql[] = "UPDATE " . $this->tableCpei .
                                " INNER JOIN " . $this->table . " ON " . $this->table . ".row_id = " . $this->tableCpei . ".row_id SET value=2"
                                . " WHERE `attribute_id`=(SELECT attribute_id FROM " . $this->tableEa . " WHERE attribute_code='status' and entity_type_id = 4)"
                                . " AND " . $this->tableCpei . ".row_id=(SELECT max(row_id) FROM " . $this->table . " WHERE entity_id = %s)"
                                . " AND (" . $this->table . ".created_by != " . $profile->getId() . " AND " . $this->table . ".updated_by != " . $profile->getId() . ");";
                        } else {
                            // Only products not related to current profile
                            $sql[] = "UPDATE " . $this->tableCpei .
                                " INNER JOIN " . $this->table . " ON " . $this->table . ".entity_id = " . $this->tableCpei . ".entity_id SET value=2"
                                . " WHERE `attribute_id`=(SELECT attribute_id FROM " . $this->tableEa . " WHERE attribute_code='status' and entity_type_id = 4)"
                                . "AND " . $this->tableCpei . ".entity_id=%s AND " . $this->table . ".created_by != " . $profile->getId() . " AND " . $this->table . ".updated_by != " . $profile->getId() . ";";
                        }
                    } else {
                        if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
                            // Only products related to current profile
                            $sql[] = "UPDATE " . $this->tableCpei .
                                " INNER JOIN " . $this->table . " ON " . $this->table . ".row_id = " . $this->tableCpei . ".row_id SET value=2"
                                . " WHERE `attribute_id`=(SELECT attribute_id FROM " . $this->tableEa . " WHERE attribute_code='status' and entity_type_id = 4)"
                                . " AND " . $this->tableCpei . ".row_id=(SELECT max(row_id) FROM " . $this->table . " WHERE entity_id = %s);";
                        } else {
                            // All products
                            $sql[] = "UPDATE  `" . $this->tableCpei . "` SET value=2 WHERE `attribute_id`=(SELECT attribute_id FROM " . $this->tableEa . " WHERE attribute_code='status' and entity_type_id = 4) AND entity_id=%s;";
                        }
                    }
                    break;
                //Remove product
                case 2:
                    if ($target == 0) {
                        // Only products related to current profile
                        $sql[] = "
                            DELETE ur
                            FROM " . $this->tableUr . " AS ur 
                            INNER JOIN  " . $this->table . " AS cpe ON cpe.entity_id=ur.entity_id AND (`created_by` = " . $profile->getId() . " OR `updated_by` = " . $profile->getId() . ")
                            WHERE ur.entity_type='product' AND ur.`entity_id`=2;";
                        $sql[] = "DELETE FROM " . $this->table . " WHERE `entity_id`=%s AND (`created_by` = " . $profile->getId() . " OR `updated_by` = " . $profile->getId() . ");";
                    } elseif ($target == 1) {
                        // Only products not related to current profile
                        $sql[] = "
                            DELETE ur
                            FROM " . $this->tableUr . " AS ur 
                            INNER JOIN  " . $this->table . " AS cpe ON cpe.entity_id=ur.entity_id AND `created_by` != " . $profile->getId() . " AND `updated_by` != " . $profile->getId() ."
                            WHERE ur.entity_type='product' AND ur.`entity_id`=2;";
                        $sql[] = "DELETE FROM " . $this->table . " WHERE `entity_id`=%s AND `created_by` != " . $profile->getId() . " AND `updated_by` != " . $profile->getId() . ";";
                    } else {
                        // All products
                        $sql[] = "
                            DELETE ur
                            FROM " . $this->tableUr . " AS ur 
                            INNER JOIN  " . $this->table . " AS cpe ON cpe.entity_id=ur.entity_id 
                            WHERE ur.entity_type='product' AND ur.`entity_id`=2;";
                        $sql[] = "DELETE FROM " . $this->table . " WHERE `entity_id`=%s";
                    }
                    break;
                // out of stock
                case 3:
                    if ($this->framework->moduleIsEnabled("Magento_Inventory")) {
                        $this->tableIsi = $this->getTable("inventory_source_item");
                        $this->tableCss = $this->getTable("cataloginventory_stock_status");
                        $sources = explode(",", $profile->getSourceTarget());
                        $inSource = [];
                        foreach ($sources as $source) {
//                            if ($source != "default") {
                            $inSource[] = "'" . $source . "'";
//                            }
                        }
                        $inSourceClause = implode(",", $inSource);
                        if ($target == 0) {
                            // Only products related to current profile
                            if (!empty($inSource)) {
                                $sql[] = "UPDATE " . $this->tableIsi
                                    . " INNER JOIN " . $this->table . " ON " . $this->table . ".sku = " . $this->tableIsi . ".sku  SET status=0"
                                    . " WHERE " . $this->table . ".entity_id=%s AND source_code IN (" . $inSourceClause . ") AND (" . $this->table . ".created_by = " . $profile->getId() . " OR " . $this->table . ".updated_by = " . $profile->getId() . ");";
                            }
                            if (in_array("default", $sources)) {
                                $sql[] = "UPDATE " . $this->tableCsi
                                    . " INNER JOIN " . $this->table . " ON " . $this->table . ".entity_id = " . $this->tableCsi . ".product_id  SET qty=0, is_in_stock=0"
                                    . " WHERE " . $this->table . ".entity_id=%s AND (" . $this->table . ".created_by = " . $profile->getId() . " OR " . $this->table . ".updated_by = " . $profile->getId() . ");";
                            }
                        } elseif ($target == 1) {
                            // Only products not related to current profile

                            if (!empty($inSource)) {
                                $sql[] = "UPDATE " . $this->tableIsi
                                    . " INNER JOIN " . $this->table . " ON " . $this->table . ".sku = " . $this->tableIsi . ".sku  SET  status=0"
                                    . " WHERE " . $this->table . ".entity_id=%s AND source_code IN (" . $inSourceClause . ")  AND " . $this->table . ".created_by != " . $profile->getId() . " AND " . $this->table . ".updated_by != " . $profile->getId() . ";";
                            }
                            if (in_array("default", $sources)) {
                                $sql[] = "UPDATE " . $this->tableCsi
                                    . " INNER JOIN " . $this->table . " ON " . $this->table . ".entity_id = " . $this->tableCsi . ".product_id  SET qty=0, is_in_stock=0"
                                    . " WHERE " . $this->table . ".entity_id=%s AND " . $this->table . ".created_by != " . $profile->getId() . " AND " . $this->table . ".updated_by != " . $profile->getId() . ";";
                            }
                        } else {
                            // All products
                            if (!empty($inSource)) {
                                $sql[] = "UPDATE " . $this->tableIsi
                                    . " INNER JOIN " . $this->table . " ON " . $this->table . ".sku = " . $this->tableIsi . ".sku  SET status=0"
                                    . " WHERE " . $this->table . ".entity_id=%s AND  source_code IN (" . $inSourceClause . ");";
                            }
                            if (in_array("default", $sources)) {
                                $sql[] = "UPDATE " . $this->tableCsi
                                    . " INNER JOIN " . $this->table . " ON " . $this->table . ".entity_id = " . $this->tableCsi . ".product_id  SET qty=0, is_in_stock=0"
                                    . " WHERE " . $this->table . ".entity_id=%s ;";
                            }
                        }
                        $resource = $this->objectManager->get("Wyomind\MassStockUpdate\Model\ResourceModel\Type\Msi");
                        $indexes = $resource->getIndexes();
                    } else {
                        if ($target == 0) {
                            // Only products related to current profile
                            $sql[] = "UPDATE " . $this->tableCsi .
                                " INNER JOIN " . $this->table . " ON " . $this->table . ".entity_id = " . $this->tableCsi . ".product_id SET is_in_stock=0"
                                . " WHERE " . $this->tableCsi . ".product_id=%s AND (" . $this->table . ".created_by = " . $profile->getId() . " OR " . $this->table . ".updated_by = " . $profile->getId() . ");";
                        } elseif ($target == 1) {
                            // Only products not related to current profile
                            $sql[] = "UPDATE " . $this->tableCsi
                                . " INNER JOIN " . $this->table . " ON " . $this->table . ".entity_id = " . $this->tableCsi . ".product_id  SET qty=0, is_in_stock=0"
                                . " WHERE " . $this->tableCsi . ".product_id=%s AND " . $this->table . ".created_by != " . $profile->getId() . " AND " . $this->table . ".updated_by != " . $profile->getId() . ";";
                        } else {
                            // All products
                            $sql[] = "UPDATE " . $this->tableCsi . " SET is_in_stock=0 WHERE product_id=%s;";
                        }
                        $resource = $this->objectManager->get("Wyomind\MassStockUpdate\Model\ResourceModel\Type\Stock");
                        $indexes = $resource->getIndexes();
                    }


                    break;

                // out of stock and set qty to 0
                case 4:
                    if ($this->_framework->moduleIsEnabled("Magento_Inventory")) {
                        $this->tableIsi = $this->getTable("inventory_source_item");
                        $this->tableCss = $this->getTable("cataloginventory_stock_status");
                        $this->tableCsi = $this->getTable("cataloginventory_stock_item");
                        $sources = explode(",", $profile->getSourceTarget());
                        $inSource = [];
                        foreach ($sources as $source) {
//                            if ($source != "default") {
                            $inSource[] = "'" . $source . "'";
//                            }
                        }
                        $inSourceClause = implode(",", $inSource);
                        if ($target == 0) {
                            // Only products related to current profile
                            if (!empty($inSource)) {
                                $sql[] = "UPDATE " . $this->tableIsi
                                    . " INNER JOIN " . $this->table . " ON " . $this->table . ".sku = " . $this->tableIsi . ".sku  SET quantity=0, status=0"
                                    . " WHERE " . $this->table . ".entity_id=%s AND source_code IN (" . $inSourceClause . ") AND (" . $this->table . ".created_by = " . $profile->getId() . " OR " . $this->table . ".updated_by = " . $profile->getId() . ");";
                            }
                            if (in_array("default", $sources)) {
                                $sql[] = "UPDATE " . $this->tableCsi
                                    . " INNER JOIN " . $this->table . " ON " . $this->table . ".entity_id = " . $this->tableCsi . ".product_id  SET qty=0, is_in_stock=0"
                                    . " WHERE " . $this->table . ".entity_id=%s  AND (" . $this->table . ".created_by = " . $profile->getId() . " OR " . $this->table . ".updated_by = " . $profile->getId() . ");";
                            }
                        } elseif ($target == 1) {
                            // Only products not related to current profile

                            if (!empty($inSource)) {
                                $sql[] = "UPDATE " . $this->tableIsi
                                    . " INNER JOIN " . $this->table . " ON " . $this->table . ".sku = " . $this->tableIsi . ".sku  SET quantity=0, status=0"
                                    . " WHERE " . $this->table . ".entity_id=%s AND source_code IN (" . $inSourceClause . ")  AND " . $this->table . ".created_by != " . $profile->getId() . " AND " . $this->table . ".updated_by != " . $profile->getId() . ";";
                            }
                            if (in_array("default", $sources)) {
                                $sql[] = "UPDATE " . $this->tableCsi
                                    . " INNER JOIN " . $this->table . " ON " . $this->table . ".entity_id = " . $this->tableCsi . ".product_id  SET qty=0, is_in_stock=0"
                                    . " WHERE " . $this->table . ".entity_id=%s  AND " . $this->table . ".created_by != " . $profile->getId() . " AND " . $this->table . ".updated_by != " . $profile->getId() . ";";
                            }
                        } else {
                            // All products
                            if (!empty($inSource)) {
                                $sql[] = "UPDATE " . $this->tableIsi
                                    . " INNER JOIN " . $this->table . " ON " . $this->table . ".sku = " . $this->tableIsi . ".sku  SET quantity=0, status=0"
                                    . " WHERE " . $this->table . ".entity_id=%s AND  source_code IN (" . $inSourceClause . ");";
                            }
                            if (in_array("default", $sources)) {
                                $sql[] = "UPDATE " . $this->tableCsi
                                    . " INNER JOIN " . $this->table . " ON " . $this->table . ".entity_id = " . $this->tableCsi . ".product_id  SET qty=0, is_in_stock=0"
                                    . " WHERE " . $this->table . ".entity_id=%s ;";
                            }
                        }
                        $resource = $this->objectManager->get("Wyomind\MassStockUpdate\Model\ResourceModel\Type\Msi");
                        $indexes = $resource->getIndexes();
                    } elseif ($this->framework->moduleIsEnabled("Wyomind_AdvancedInventory")) {
                        $this->tableAIs = $this->getTable("advancedinventory_stock");
                        $pos = explode(",", $profile->getPosTarget());
                        $inPos = [];
                        foreach ($pos as $source) {
                            $inPos[] = "'" . $source . "'";
                        }
                        $inPosClause = implode(",", $inPos);
                        if ($target == 0) {
                            // Only products related to current profile
                            if (!empty($inPos)) {
                                $sql[] = "UPDATE " . $this->tableAIs
                                    . " INNER JOIN " . $this->table . " ON " . $this->table . ".entity_id = " . $this->tableAIs . ".product_id  SET quantity_in_stock=0"
                                    . " WHERE " . $this->table . ".entity_id=%s AND place_id IN (" . $inPosClause . ") AND (" . $this->table . ".updated_by = " . $profile->getId() . " OR " . $this->table . ".created_by = " . $profile->getId() . ");";
                                $sql[] = "SET @sumQty = (select sum(quantity_in_stock) from " . $this->tableAIs . " where product_id = %s);";
                                $sql[] = "UPDATE " . $this->tableCsi .
                                    " INNER JOIN " . $this->table . " ON " . $this->table . ".entity_id = " . $this->tableCsi . ".product_id SET qty=@sumQty"
                                    . " WHERE " . $this->tableCsi . ".product_id=%s AND (" . $this->table . ".created_by = " . $profile->getId() . " OR " . $this->table . ".updated_by = " . $profile->getId() . ");";
                            }
                        } elseif ($target == 1) {
                            // Only products not related to current profile
                            if (!empty($inPos)) {
                                $sql[] = "UPDATE " . $this->tableAIs
                                    . " INNER JOIN " . $this->table . " ON " . $this->table . ".entity_id = " . $this->tableAIs . ".product_id  SET  quantity_in_stock=0"
                                    . " WHERE " . $this->table . ".entity_id=%s AND place_id IN (" . $inPosClause . ")  AND " . $this->table . ".updated_by != " . $profile->getId() . " AND " . $this->table . ".created_by != " . $profile->getId() . ";";
                                $sql[] = "SET @sumQty = (select sum(quantity_in_stock) from " . $this->tableAIs . " where product_id = %s);";
                                $sql[] = "UPDATE " . $this->tableCsi .
                                    " INNER JOIN " . $this->table . " ON " . $this->table . ".entity_id = " . $this->tableCsi . ".product_id SET qty=@sumQty"
                                    . " WHERE " . $this->tableCsi . ".product_id=%s AND (" . $this->table . ".created_by != " . $profile->getId() . " AND " . $this->table . ".updated_by != " . $profile->getId() . ");";
                            }
                        } else {
                            // All products
                            if (!empty($inPos)) {
                                $sql[] = "UPDATE " . $this->tableAIs
                                    . " INNER JOIN " . $this->table . " ON " . $this->table . ".entity_id = " . $this->tableAIs . ".product_id  SET quantity_in_stock=0"
                                    . " WHERE " . $this->table . ".entity_id=%s AND  place_id IN (" . $inPosClause . ");";
                                $sql[] = "SET @sumQty = (select sum(quantity_in_stock) from " . $this->tableAIs . " where product_id = %s);";
                                $sql[] = "UPDATE " . $this->tableCsi .
                                    " INNER JOIN " . $this->table . " ON " . $this->table . ".entity_id = " . $this->tableCsi . ".product_id SET qty=@sumQty"
                                    . " WHERE " . $this->tableCsi . ".product_id=%s;";
                            }
                        }
                        $resource = $this->objectManager->get("Wyomind\MassStockUpdate\Model\ResourceModel\Type\Stock");
                        $indexes = $resource->getIndexes();
                    } else {
                        if ($target == 0) {
                            // Only products related to current profile
                            $sql[] = "UPDATE " . $this->tableCsi .
                                " INNER JOIN " . $this->table . " ON " . $this->table . ".entity_id = " . $this->tableCsi . ".product_id SET qty=0, is_in_stock=0"
                                . " WHERE " . $this->tableCsi . ".product_id=%s AND (" . $this->table . ".created_by = " . $profile->getId() . " OR " . $this->table . ".updated_by = " . $profile->getId() . ");";
                        } elseif ($target == 1) {
                            // Only products not related to current profile
                            $sql[] = "UPDATE " . $this->tableCsi .
                                " INNER JOIN " . $this->table . " ON " . $this->table . ".entity_id = " . $this->tableCsi . ".product_id SET qty=0, is_in_stock=0"
                                . " WHERE " . $this->tableCsi . ".product_id=%s AND " . $this->table . ".created_by != " . $profile->getId() . " AND " . $this->table . ".updated_by != " . $profile->getId() . ";";
                        } else {
                            // All products
                            $sql[] = "UPDATE " . $this->tableCsi . " SET qty=0, is_in_stock=0 WHERE product_id=%s;";
                        }
                        $resource = $this->objectManager->get("Wyomind\MassStockUpdate\Model\ResourceModel\Type\Stock");
                        $indexes = $resource->getIndexes();
                    }


                    break;
            }

            $products = $profile->_products;
            foreach ($products as $productId) {
                foreach ($sql as $query) {
                    $this->removeQueries[$productId][] = sprintf($query, $productId);
                }
            }
        }
        foreach ($indexes as $key => $name) {
            $this->addIndex($key, $name);
        }
        parent::beforeCollect($profile, $columns);
    }

    /**
     * @param int $productId
     * @param \Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @return array|\Wyomind\MassStockUpdate\Model\ResourceModel\Type\System
     */
    public function updatequeries($productId, $profile)
    {

        if (is_integer($productId)) {
            unset($this->removeQueries[$productId]);
        }
        $queryGroup = $this->removeQueries;
        $this->removeQueries = [];

        foreach ($queryGroup as $key => $queries) {
            if (is_array($queries)) {
                $this->removeQueries[$key] = implode("__BREAKLINE__", $queries);
            } else {
                $this->removeQueries[$key] = $queries;
            }
        }

        return parent::updatequeries($productId, $profile);
    }

    public function reset()
    {
        $this->fields = [];

    }

    public function resetGlobal()
    {
        $this->reset();
        $this->removeQueries = [];
    }

    /**
     * @param int $productId
     * @param string $value
     * @param array $strategy
     * @param \Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function collect($productId, $value, $strategy, $profile)
    {
        list($field) = $strategy['option'];


        switch ($field) {
            case "has_options":
                $this->fields[$field] = $this->getValue($value);
                break;
            case "type_id":
                $this->fields[$field] = strtolower($value);
                if ($value == "configurable") {
                    $this->fields["has_options"] = 1;
                }
                break;
            case "attribute_set_id":
                $value = strtolower($value);
                if (isset($this->attributeSet[$value]) || in_array($value, $this->attributeSet)) {
                    if (in_array($value, $this->attributeSet)) {
                        $value = array_search($value, $this->attributeSet);
                    }
                } else {
                    break;
                }

                $this->fields[$field] = $value;
                break;
            case "website":
                $value = strtolower($value);
                $list = explode(self::WEBSITE_SEPARATOR, $value);

                $this->queries[$this->queryIndexer][] = "DELETE FROM " . $this->tableCpw . " WHERE product_id=" . $productId . ";";
                foreach ($list as $website) {
                    if (isset($this->websites[$website]) || in_array($website, $this->websites)) {
                        if (in_array($website, $this->websites)) {
                            $value = array_search($website, $this->websites);
                        } else {
                            $value = $website;
                        }

                        $this->queries[$this->queryIndexer][] = "INSERT INTO " . $this->tableCpw . " (product_id,website_id) VALUES(" . $productId . "," . $value . ");";
                    }
                }
                break;
            default:
                if ($field == $profile->_identifierCode && !in_array($profile->_identifierCode, ["sku", "entity_id"])) {
                    $this->currentIdentifierInsertRow = "INSERT INTO `" . $this->tableCpev . "` (`entity_id`,`store_id`,`attribute_id`,`value`) VALUES(%s,0,(SELECT attribute_id FROM " . $this->tableEa . " WHERE attribute_code='" . $profile->_identifierCode . "'),'" . $value . "');";
                } else {
                    $this->fields[$field] = addslashes($value);
                }
        }

        parent::collect($productId, $value, $strategy, $profile);
    }

    /**
     * @param int $productId
     * @param \Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     */
    public function prepareQueries($productId, $profile)
    {

        $this->fields["updated_at"] = $this->_coreDate->date("Y-m-d h:i:s");
        $this->fields["updated_by"] = $profile->getId();

        if (is_integer($productId)) {
            $update = [];
            foreach ($this->fields as $field => $value) {
                $update[] = "`" . $field . "` = '" . $value . "'";
            }
            if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
                $update[] = "`updated_in` = 2147483647";
            }
            $this->queries[$this->queryIndexer][] = "UPDATE `" . $this->table . "` SET \n"
                . implode(", \n", $update) . " \n WHERE `entity_id` = '$productId';";
        } else {
            $insert = [];

            $insert["fields"][] = "created_at";
            $insert["values"][] = "'" . $this->_coreDate->date("Y-m-d h:i:s") . "'";
            $insert["fields"][] = "created_by";
            $insert["values"][] = "'" . $profile->getId() . "'";

            if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
                $queries[] = "INSERT INTO `" . $this->tableSequence . "` VALUES (sequence_value=NULL);";
                $queries[] = "SET @product_id=LAST_INSERT_ID();";

                $insert["fields"][] = "entity_id";
                $insert["values"][] = "@product_id";
                $insert["fields"][] = "created_in";
                $insert["values"][] = 1;
                $insert["fields"][] = "updated_in";
                $insert["values"][] = 2147483647;
            }

            foreach ($this->fields as $field => $value) {
                $insert["fields"][] = $field;
                $insert["values"][] = "'" . $value . "'";
            }

            $queries[] = "INSERT INTO `" . $this->table . "` (" . implode(",", $insert["fields"]) . ") VALUES(" . implode(",", $insert["values"]) . ");";
            if (!in_array($profile->_identifierCode, ["sku", "entity_id"])) {
                $queries[] = sprintf($this->currentIdentifierInsertRow, "LAST_INSERT_ID()");
            }
            $queries = array_reverse($queries);
            foreach ($queries as $query) {
                array_unshift($this->queries[$this->queryIndexer], $query);
            }
        }

        parent::prepareQueries($productId, $profile);
    }

    /**
     * @return string|void
     */
    public function afterCollect()
    {
        if (is_array($this->queries) && array_key_exists($this->queryIndexer, $this->queries)) {
            $this->queries[$this->queryIndexer] = array_merge($this->queries[$this->queryIndexer], $this->removeQueries);
        }

        parent::afterCollect();
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     */
    public function getDropdown()
    {
        /* IMAGES MAPPING */
        $dropdown = [];
        $fields = ["backend_type"];
        $conditions = [
            ["eq" =>
                [
                    "static",
                ]
            ],
        ];
        $i = 0;
        $dropdown['Required attributes'][$i]['label'] = "Sku";
        $dropdown['Required attributes'][$i]['id'] = "System/sku";
        $dropdown['Required attributes'][$i]['style'] = "system";
        $dropdown['Required attributes'][$i]['type'] = $this->uniqueIdentifier;
        $dropdown['Required attributes'][$i]['value'] = "";

        $i++;

        $dropdown['Required attributes'][$i]['label'] = "Attribute set";
        $dropdown['Required attributes'][$i]["id"] = "System/attribute_set_id";
        $dropdown['Required attributes'][$i]['style'] = "system";
        $dropdown['Required attributes'][$i]['type'] = "Attribute set id or Attribute set name (case unsensitive)";
        $dropdown['Required attributes'][$i]['options'] = $this->attributeSet;

        $i++;
        $dropdown['Required attributes'][$i]['label'] = "Type";
        $dropdown['Required attributes'][$i]["id"] = "System/type_id";
        $dropdown['Required attributes'][$i]['style'] = "system";
        $dropdown['Required attributes'][$i]['type'] = "Product Type Id (case unsensitive)";
        $dropdown['Required attributes'][$i]['type'] = "Simple, Configurable, Downloadable, Virtual, Bundle, Grouped, ...";
        $dropdown['Required attributes'][$i]['options'] = $this->helperData->getProductTypeIds();

        $i++;
        $dropdown['Required attributes'][$i]['label'] = "Has Options";
        $dropdown['Required attributes'][$i]["id"] = "System/has_options";
        $dropdown['Required attributes'][$i]['style'] = "system";
        $dropdown['Required attributes'][$i]['type'] = "Product Has Options (yes/no)";
        $dropdown['Required attributes'][$i]['value'] = implode(", ", self::ENABLE) . " or " . implode(", ", self::DISABLE);
        $dropdown['Required attributes'][$i]['options'] = $this->helperData->getBoolean();

        $i++;
        $dropdown['Required attributes'][$i]['label'] = "Has Options";
        $dropdown['Required attributes'][$i]["id"] = "System/required_options";
        $dropdown['Required attributes'][$i]['style'] = "system";
        $dropdown['Required attributes'][$i]['type'] = "Product Has Required Options (yes/no)";
        $dropdown['Required attributes'][$i]['value'] = implode(", ", self::ENABLE) . " or " . implode(", ", self::DISABLE);
        $dropdown['Required attributes'][$i]['options'] = $this->helperData->getBoolean();

        $i++;
        $dropdown['Required attributes'][$i]['label'] = "Website";
        $dropdown['Required attributes'][$i]["id"] = "System/website";
        $dropdown['Required attributes'][$i]['style'] = "system";
        $dropdown['Required attributes'][$i]['type'] = "Website id or Website name (case unsensitive)";
        $dropdown['Required attributes'][$i]['options'] = $this->websites;

        $i++;

        $attribute = $this->getAttributesList(["attribute_code"], [["eq" => "tax_class_id"]])[0];
        $dropdown['Required attributes'][$i]['label'] = "Tax Class";
        $dropdown['Required attributes'][$i]["id"] = "Attribute/" . $attribute['backend_type'] . "/" . $attribute['attribute_id'] . "/tax_class_id";
        $dropdown['Required attributes'][$i]['style'] = "system storeviews-dependent";
        $dropdown['Required attributes'][$i]['type'] = "Taxclass id or Taxclass name (case unsensitive)";
        $dropdown['Required attributes'][$i]['options'] = $this->helperData->getTaxClasses();
        ;

        $i++;
        $attribute = $this->getAttributesList(["attribute_code"], [["eq" => "visibility"]])[0];
        $dropdown['Required attributes'][$i]['label'] = "Visibility";
        $dropdown['Required attributes'][$i]["id"] = "Attribute/" . $attribute['backend_type'] . "/" . $attribute['attribute_id'] . "/visibility";
        $dropdown['Required attributes'][$i]['style'] = "system storeviews-dependent";
        $dropdown['Required attributes'][$i]['type'] = "Product visibility Id or product visibility name (case unsensitive)";
        $dropdown['Required attributes'][$i]['options'] = $this->helperData->getVisibility();

        $i++;
        $attribute = $this->getAttributesList(["attribute_code"], [["eq" => "status"]])[0];
        $dropdown['Required attributes'][$i]['label'] = "Status";
        $dropdown['Required attributes'][$i]["id"] = "Attribute/" . $attribute['backend_type'] . "/" . $attribute['attribute_id'] . "/status";
        $dropdown['Required attributes'][$i]['style'] = "system storeviews-dependent";
        $dropdown['Required attributes'][$i]['type'] = "Product Status (enabled/disabled)";
        $dropdown['Required attributes'][$i]['value'] = implode(", ", self::ENABLE) . " or " . implode(", ", self::DISABLE);
        $dropdown['Required attributes'][$i]['options'] = $this->helperData->getBoolean();

        $i++;

        $attribute = $this->getAttributesList(["attribute_code"], [["eq" => "url_key"]])[0];
        $dropdown['Required attributes'][$i]['label'] = "Url key";
        $dropdown['Required attributes'][$i]['id'] = "Attribute/" . $attribute['backend_type'] . "/" . $attribute['attribute_id'] . "/url_key";
        $dropdown['Required attributes'][$i]['style'] = "static storeviews-dependent";
        $dropdown['Required attributes'][$i]['type'] = "Product Url key";
        $i++;

        return $dropdown;
    }

    /**
     * @param null $fieldset
     * @param bool $form
     * @param null $class
     * @return bool|null
     */
    public function getFields($fieldset = null, $form = false, $class = null)
    {

        if ($fieldset == null) {
            return true;
        }

        $options = [
            0 => 'Do nothing',
            1 => 'Disable the product',
            2 => 'Delete permanently the product',
            3 => 'Mark the product as out of stock',
            4 => 'Mark the product as out of stock and set qty to 0',
        ];

        if (!$this->framework->moduleIsEnabled("Magento_Inventory") && $this->framework->moduleIsEnabled("Wyomind_AdvancedInventory")) {
            unset($options[3]);
            $options[4] = 'Set the qty to 0';
        }


        $fieldset->addField('product_removal', 'select', [
            'name' => 'product_removal',
            'label' => __('Automatic action for missing products'),
            'note' => 'Action for each product that is missing from the data file',
            'options' => $options
        ]);

        if ($this->framework->moduleIsEnabled("Magento_Inventory")) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $sourceRepositoryFactory = $this->objectManager->create("\Magento\Inventory\Model\SourceRepository");
            $sources = $sourceRepositoryFactory->getList($searchCriteria);
            $sourceList = [];
            foreach ($sources->getItems() as $source) {
                $sourceList[] = ["value" => $source->getSourceCode(), "label" => $source->getName()];
            }

            $fieldset->addField('source_target', 'multiselect', [
                'name' => 'source_target[]',
                'label' => __('Targeted sources'),
                'note' => 'Target sources impacted by the action'
                    . '<script>
                var selectRemoval = document.getElementById("product_removal");
                var selectSource = document.getElementsByClassName("field-source_target")[0];
                selectRemoval.addEventListener("change", function () {
                    var value = selectRemoval.options[selectRemoval.selectedIndex].value;
                    
                    if (value == 3 || value == 4) {
                        selectSource.style.display = "block";
                    } else {
                        selectSource.style.display = "none";
                    }
                });
                var event = new Event("change");
                selectRemoval.dispatchEvent(event);
            </script>',
                'values' => $sourceList
            ]);
        } elseif ($this->framework->moduleIsEnabled("Wyomind_AdvancedInventory")) {
            $posCollection = $this->objectManager->create("\Wyomind\PointOfSale\Model\ResourceModel\PointOfSale\Collection");
            $sourceList = [];
            foreach ($posCollection->getItems() as $source) {
                $sourceList[] = ["value" => $source->getPlaceId(), "label" => $source->getName()];
            }

            $fieldset->addField('pos_target', 'multiselect', [
                'name' => 'pos_target[]',
                'label' => __('Targeted POS/WH'),
                'note' => 'Target POS/WH impacted by the action'
                    . '<script>
                var selectRemoval = document.getElementById("product_removal");
                var selectSource = document.getElementsByClassName("field-pos_target")[0];
                selectRemoval.addEventListener("change", function () {
                    var value = selectRemoval.options[selectRemoval.selectedIndex].value;
                    
                    if (value == 3 || value == 4) {
                        selectSource.style.display = "block";
                    } else {
                        selectSource.style.display = "none";
                    }
                });
                var event = new Event("change");
                selectRemoval.dispatchEvent(event);
            </script>',
                'values' => $sourceList
            ]);
        }

        $fieldset->addField('product_target', 'select', [
            'name' => 'product_target',
            'label' => __('Targeted Products'),
            'note' => 'Targeted products impacted by the action'
                . '<script>
                var selectRemoval = document.getElementById("product_removal");
                var selectTarget = document.getElementsByClassName("field-product_target")[0];
                selectRemoval.addEventListener("change", function () {
                    var value = selectRemoval.options[selectRemoval.selectedIndex].value;
                    if (value != 0) {
                        selectTarget.style.display = "block";
                    } else {
                        selectTarget.style.display = "none";
                    }
                });
                var event = new Event("change");
                selectRemoval.dispatchEvent(event);
            </script>',
            'options' => [
                0 => 'Only products related to current profile',
                1 => 'Only products not related to current profile',
                2 => 'All products'
            ]
        ]);

        return $fieldset;
    }

    /**
     * @param \Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @return array|bool
     */
    public function addModuleIf($profile)
    {
        $modules = ["System"];

        return $modules;
    }


    /**
     * @param array $mapping
     * @return array
     */
    public function getIndexes($mapping = [])
    {
        return $this->indexes;
    }

    /**
     * @param $key
     * @param $name
     */
    public function addIndex($key, $name)
    {
        $this->indexes[$key] = $name;
    }
}
