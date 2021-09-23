<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Model\ResourceModel\Type;

/**
 * Class AdvancedInventory
 * @package Wyomind\MassStockUpdate\Model\ResourceModel\Type
 */
class AdvancedInventory extends \Wyomind\MassStockUpdate\Model\ResourceModel\Type\AbstractResource
{
    /**
     * @var string
     */
    public $module = "MassStockUpdate";
    /**
     * @var
     */
    public $fields;
    /**
     * @var
     */
    public $_tableItems;
    /**
     * @var
     */
    public $_stockId;
    /**
     * @var
     */
    public $_autoInc;
    /**
     * @var array
     */
    public $_substractedStocks = [];
    /**
     * @var array
     */
    public $_sum = [];
    /**
     * @var
     */
    public $_pointOfSaleModel;
    /**
     * @var \Magento\Framework\Module\ModuleList|null
     */
    public $_moduleList = null;
    /**
     * @var mixed
     */
    public $_itemCollectionFactory;
    /**
     * @var mixed
     */
    public $_stockCollectionFactory;

    /**
     * AdvancedInventory constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Wyomind\Framework\Helper\Module $framework
     * @param \Wyomind\MassStockUpdate\Helper\Data $helperData
     * @param \Magento\Framework\Module\ModuleList $moduleList
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Wyomind\Framework\Helper\Module $framework,
        \Wyomind\MassStockUpdate\Helper\Data $helperData,
        \Magento\Framework\Module\ModuleList $moduleList,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection,
        $connectionName = null
    ) {
    
        $this->_moduleList = $moduleList;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if ($this->isAdvancedInventoryEnabled()) {
            $this->_itemCollectionFactory = $objectManager->create("\Wyomind\AdvancedInventory\Model\ResourceModel\Item\CollectionFactory");
            $this->_stockCollectionFactory = $objectManager->create("\Wyomind\AdvancedInventory\Model\ResourceModel\Stock\CollectionFactory");
        }

        parent::__construct($context, $framework, $helperData, $entityAttributeCollection, $connectionName);
    }

    /**
     * @return bool
     */
    public function isAdvancedInventoryEnabled()
    {
        $advancedInventory = $this->_moduleList->getOne("Wyomind_AdvancedInventory");
        return $advancedInventory != null;
    }

    /**
     *
     */
    public function _construct()
    {

        $this->_init('advancedinventory_stock', 'id');
        $this->table = $this->getTable("advancedinventory_stock");
        $this->_tableItems = $this->getTable("advancedinventory_item");
        $this->_backorders = $this->framework->getStoreConfig("cataloginventory/item_options/backorders");
    }

    /**
     *
     */
    public function reset()
    {
        $this->fields = [];
        $this->_sum = [];
        $this->_substractedStocks = [];
    }

    /**
     * @param array $mapping
     * @return array
     */
    public function getIndexes($mapping = [])
    {
        return [5 => "cataloginventory_stock"];
    }

    /**
     * @param int $productId
     * @param string $value
     * @param array $strategy
     * @param \Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     */
    public function collect($productId, $value, $strategy, $profile)
    {
        $md5 = "md5";
        if ($strategy["option"][0] == "multistock_enabled") {
            $val = (int)$this->getValue($value);
            $data = [
                "product_id" => $productId,
                "multistock_enabled" => $val
            ];
            $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->_tableItems, $data);
        } else {
            $placeId = $strategy["option"][0];
            $field = $strategy["option"][1];
            if (!isset($this->fields[$md5($productId)][$placeId])) {
                $this->fields[$md5($productId)][$placeId] = [
                    "product_id" => $productId,
                    "item_id" => "(SELECT id  FROM `" . $this->_tableItems . "` WHERE product_id=" . $productId . ")",
                    "place_id" => $placeId
                ];
            }

            if ($field == "quantity_in_stock") {
                if (!isset($this->_sum[$md5($productId)])) {
                    $this->_sum[$md5($productId)] = 0;
                }
                $this->_sum[$md5($productId)] += $value;
            }
            $this->fields[$md5($productId)][$placeId][$field] = $this->getValue($value);

            $this->_substractedStocks[$placeId] = "-IFNULL((SELECT `quantity_in_stock` FROM `" . $this->table . "` WHERE `place_id`=" . $placeId . " AND  `product_id`=" . $productId . " ),0)";
        }
        parent::collect($productId, $value, $strategy, $profile);
    }

    /**
     * @param int $productId
     * @param \Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @return array|void
     */
    public function prepareQueries($productId, $profile)
    {

        $md5 = "md5";
        if (isset($this->fields[$md5($productId)])) {
            $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->_tableItems, ["product_id" => $productId, "multistock_enabled" => 1]);
            foreach ($this->fields[$md5($productId)] as $warehouse) {
                $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->table, $warehouse);
            }
        }
        if (isset($this->_sum[$md5($productId)])) {
            $totalQty = "(SELECT (IFNULL(SUM(`quantity_in_stock`),0) " . implode('', $this->_substractedStocks) . "+" . $this->_sum[$md5($productId)] . ") FROM " . $this->table . " WHERE `product_id`=$productId)";
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $stock = $objectManager->get("\Wyomind\\" . $this->module . "\Model\ResourceModel\Type\Stock");
            $stock->qtyField = $totalQty;
            $stock->backorderField = "((SELECT MAX(IF((backorder_allowed>0 AND use_config_setting_for_backorders=0) OR (use_config_setting_for_backorders=1 AND $this->_backorders>0) OR (manage_stock=0),1,0)) FROM $this->table WHERE product_id=$productId GROUP BY product_id)=1)";
            $stock->fields["qty"] = $totalQty;
        }

        parent::prepareQueries($productId, $profile);
    }

    /**
     * @return array
     */
    public function getDropdown()
    {
        $dropdown = [];
        /* Advanced Inventory */
        if ($this->isAdvancedInventoryEnabled()) {
            $attributes = [
                "Qty" => "quantity_in_stock",
                "Manage stock" => "manage_stock",
                "Backorders allowed" => "backorder_allowed",
                "Use config settings for backorders" => "use_config_setting_for_backorders"
            ];

            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $this->_pointOfSaleModel = $objectManager->get("\Wyomind\PointOfSale\Model\PointOfSale");

            $places = $this->_pointOfSaleModel->getPlaces();


            foreach ($places as $p) {
                $i = 0;
                foreach ($attributes as $name => $field) {
                    $dropdown[$p->getName()][$i]['label'] = $p->getName() . " - " . $name;
                    $dropdown[$p->getName()][$i]["id"] = "AdvancedInventory/" . $p->getId() . "/" . $field;
                    $dropdown[$p->getName()][$i]['style'] = "AdvancedInventory";
                    if ($name == "quantity_in_stock") {
                        $dropdown[$p->getName()][$i]['type'] = $this->decimal;
                        $dropdown[$p->getName()][$i]['value'] = "";
                    } else {
                        $dropdown[$p->getName()][$i]['type'] = $this->smallint;
                        $dropdown[$p->getName()][$i]['value'] = implode(", ", self::ENABLE) . " or " . implode(", ", self::DISABLE);
                        $dropdown['Stocks'][$i]['options'] = $this->helperData->getBoolean();
                        $dropdown['Stocks'][$i]['newable'] = false;
                    }
                    $i++;
                }
            }
        }
        return $dropdown;
    }
}
