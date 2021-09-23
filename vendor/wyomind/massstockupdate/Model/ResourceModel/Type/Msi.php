<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */


namespace Wyomind\MassStockUpdate\Model\ResourceModel\Type;

/**
 * Class Stock
 * @package Wyomind\MassStockUpdate\Model\ResourceModel\Type
 */
class Msi extends \Wyomind\MassStockUpdate\Model\ResourceModel\Type\AbstractResource
{
    /**
     * @var \Magento\Inventory\Model\SourceItemRepositoryFactory
     */
    public $sourceRepositoryFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaInterface
     */
    public $searchCriteriaBuilder;

    /**
     * @var \Magento\Inventory\Model\ResourceModel\StockSourceLink\CollectionFactory
     */
    public $stockSourceLink;

    /**
     * Backorders config
     * @var
     */
    public $backorders;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    public $objectManager;

    /**
     * Min qty config
     * @var
     */
    public $minQty;

    /**
     * MSI stock data
     * @var array
     */
    public $stocks = [];

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    public $eventManager;

    /**
     * Msi constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Wyomind\Framework\Helper\Module $framework
     * @param \Wyomind\MassStockUpdate\Helper\Data $helperData
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Wyomind\Framework\Helper\Module $framework,
        \Wyomind\MassStockUpdate\Helper\Data $helperData,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory $entityAttributeCollection,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        $connectionName = null
    ) {
    
        $this->eventManager = $eventManager;
        $this->objectManager = $objectManager;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;

        parent::__construct($context, $framework, $helperData, $entityAttributeCollection, $connectionName);
    }

    /**
     * Collect the necessary table names
     */
    public function _construct()
    {
        if ($this->helperData->isMsiEnabled()) {
            $this->sourceRepositoryFactory = $this->objectManager->create("\Magento\Inventory\Model\SourceRepository");
            $this->stockSourceLink = $this->objectManager->create("\Magento\Inventory\Model\ResourceModel\StockSourceLink\Collection");
            foreach ($this->stockSourceLink as $stock) {
                $this->stocks[$stock->getStockId()][] = $stock->getSourceCode();
            }
        }

        $this->tableCpe = $this->getTable("catalog_product_entity");
        $this->tableIlsnc = $this->getTable("inventory_low_stock_notification_configuration");
        $this->tableIsi = $this->getTable("inventory_source_item");
        $this->tableCsi = $this->getTable("cataloginventory_stock_item");

        $this->backorders = $this->framework->getStoreConfig("cataloginventory/item_options/backorders");
        $this->minQty = $this->framework->getStoreConfig("cataloginventory/item_options/min_qty");
    }


    /**
     * Collect all fields and values
     * @param int $productId
     * @param string $value
     * @param array $strategy
     * @param \Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     */
    public function collect($productId, $value, $strategy, $profile)
    {
        list($field, $sourceCode) = $strategy["option"];

        $md5 = 'md5';

        $this->fields[$md5($productId)][$sourceCode]["source_code"] = $this->helperData->sanitizeField($sourceCode);
        if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
            $sku = "(SELECT sku from $this->tableCpe WHERE entity_id=$productId ORDER BY row_id DESC LIMIT 1)";
        } else {
            $sku = "(SELECT sku from $this->tableCpe WHERE entity_id=$productId)";
        }
        $this->fields[$md5($productId)][$sourceCode]["sku"] = $sku;

        switch ($field) {
            case 'quantity':
                if ($profile->getRelativeStockUpdate()) {
                    $value = "(quantity + " . $value . ")";
                }
                if ($profile->getAutoSetInstock()) {
                    $this->fields[$md5($productId)][$sourceCode]["status"] = "IF(" . $value . " > (SELECT IF(use_config_min_qty=1, " . $this->minQty . ", min_qty) FROM " . $this->tableCsi . " WHERE product_id = $productId),1,0)";
                }


                $this->fields[$md5($productId)][$sourceCode][$field] = $value;
                $this->qties[$md5($productId)][$sourceCode] = $value;
                $this->substractedStocks[$md5($productId)][$sourceCode] = "-IFNULL((SELECT `quantity` FROM `" . $this->tableIsi . "` WHERE `source_code`=" . $sourceCode . " AND  `sku`=" . $sku . " ),0)";

                break;
            case 'status':
                $this->fields[$md5($productId)][$sourceCode][$field] = $this->getValue($value);
                break;
            case 'notify_stock_qty':
                $this->fields[$md5($productId)][$sourceCode][$field] = $this->helperData->sanitizeField($value);
                break;
            case 'notify_stock_qty_use_default':
                $value = $this->getValue($value);
                if ($value) {
                    $this->fields[$md5($productId)][$sourceCode]["notify_stock_qty"] = "NULL";
                }
                break;

            default:
                $value = $this->helperData->sanitizeField($value);
                $this->fields[$md5($productId)][$sourceCode][$field] = $value;
        }


        parent::collect($productId, $value, $strategy, $profile);
    }

    /**
     * Prepare the mysql queries
     * @param int $productId
     * @param \Wyomind\MassSockUpdate\Model\ResourceModel\Profile $profile
     * @return array|void
     */
    public function prepareQueries($productId, $profile)
    {
        $md5 = "md5";
        if (isset($this->fields[$md5($productId)])) {
            foreach ($this->fields[$md5($productId)] as $source) {
                $data = [];
                if (isset($source["quantity"])) {
                    $data["quantity"] = $source["quantity"];
                }

                if (isset($source["status"])) {
                    $data["status"] = $source["status"];
                }

                if (!empty($data)) {
                    $data = array_merge($data, ["source_code" => $source["source_code"], "sku" => $source["sku"]]);
                    $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableIsi, $data);
                }

                if (isset($source["notify_stock_qty"])) {
                    $notify_stock_qty = $source["notify_stock_qty"];
                    $data = ["source_code" => $source["source_code"], "sku" => $source["sku"], "notify_stock_qty" => $notify_stock_qty];
                    $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableIlsnc, $data);
                }


                $this->eventManager->dispatch("massstockupdate_resourcemodel_msi_preparequeries", ["resource" => $this, "source" => $source]);
            }

            if ($this->helperData->isMsiEnabled()) {
                foreach ($this->stocks as $stockId => $sources) {
                    $go = false;
                    foreach ($sources as $source) {
                        if (isset($this->fields[$md5($productId)][$source])) {
                            $go = true;
                        }
                    }
                    if (!$go) {
                        continue;
                    }

                    if ($stockId == '1') {
                        if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
                            $sku = "(SELECT sku from $this->tableCpe WHERE entity_id=$productId ORDER BY row_id DESC LIMIT 1)";
                        } else {
                            $sku = "(SELECT sku from $this->tableCpe WHERE entity_id=$productId)";
                        }
                        $stockStatus = " IF ((SELECT SUM(quantity) FROM " . $this->tableIsi . " WHERE sku=" . $sku . " AND source_code IN ('" . implode("','", $sources) . "')) > IF(use_config_min_qty=1, " . $this->minQty . ", min_qty) OR (backorders>0 AND use_config_backorders=0) "
                            . " OR (use_config_backorders=1 AND $this->backorders>0),1,0)";

                        $data = [
                            "stock_id" => 1,
                            "is_in_stock" => $stockStatus,
                            "qty" => "(SELECT SUM(quantity) FROM " . $this->tableIsi . "  WHERE sku=" . $sku . " AND source_code IN ('" . implode("','", $sources) . "'))",
                            "product_id" => $productId
                        ];
                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->tableCsi, $data);
                    } else {
                        $this->table = $this->getTable("inventory_stock_" . $stockId);
                        if ($this->framework->moduleIsEnabled("Magento_Enterprise")) {
                            $sku = "(SELECT sku from $this->tableCpe WHERE entity_id=$productId ORDER BY row_id DESC LIMIT 1)";
                        } else {
                            $sku = "(SELECT sku from $this->tableCpe WHERE entity_id=$productId)";
                        }

                        $stockStatus = " IF ((SELECT SUM(quantity) FROM " . $this->tableIsi . "  WHERE sku=" . $sku . " AND source_code IN ('" . implode("','", $sources) . "')) > (SELECT IF(use_config_min_qty=1, " . $this->minQty . ", min_qty) FROM " . $this->tableCsi . " WHERE product_id = $productId) OR ((SELECT backorders FROM " . $this->tableCsi . " WHERE product_id=$productId) >0 AND (SELECT use_config_backorders FROM " . $this->tableCsi . " WHERE product_id=$productId)=0) "
                            . " OR ((SELECT use_config_backorders FROM " . $this->tableCsi . " WHERE product_id=$productId)=1 AND $this->backorders>0),1,0)";

                        $data = [
                            "is_salable" => $stockStatus,
                            "quantity" => "(SELECT SUM(quantity) FROM " . $this->tableIsi . " WHERE sku=" . $sku . " AND source_code IN ('" . implode("','", $sources) . "'))",
                            "sku" => $sku
                        ];
                        $this->queries[$this->queryIndexer][] = $this->createInsertOnDuplicateUpdate($this->table, $data);
                    }
                }
            }
        }

        parent::prepareQueries($productId, $profile);
    }

    /**
     * Get the dropdown options
     * @return array
     */
    public function getDropdown()
    {
        $this->inc=0;
        $this->dropdown = [];
        /* STOCK MAPPING */
        if ($this->helperData->isMsiEnabled()) {
            $searchCriteria = $this->searchCriteriaBuilder->create();
            $sources = $this->sourceRepositoryFactory->getList($searchCriteria);
            $i = 0;
            foreach ($sources->getItems() as $source) {
                $this->dropdown['Multi Stock Inventory'][$i]['label'] = $source->getName() . " [" . $source->getSourceCode() . "] | Quantity";
                $this->dropdown['Multi Stock Inventory'][$i]["id"] = "Msi/quantity/" . $source->getSourceCode();
                $this->dropdown['Multi Stock Inventory'][$i]['style'] = "stock no-configurable";
                $this->dropdown['Multi Stock Inventory'][$i]['type'] = __("Stock for '" . $source->getName() . "'");
                $this->dropdown['Multi Stock Inventory'][$i]['value'] = $this->int;
                $i++;

                $this->dropdown['Multi Stock Inventory'][$i]['label'] = $source->getName() . " [" . $source->getSourceCode() . "] | Notify Qty";
                $this->dropdown['Multi Stock Inventory'][$i]["id"] = "Msi/notify_stock_qty/" . $source->getSourceCode();
                $this->dropdown['Multi Stock Inventory'][$i]['style'] = "stock no-configurable";
                $this->dropdown['Multi Stock Inventory'][$i]['type'] = __("Notify the qty for '" . $source->getName() . "'");
                $this->dropdown['Multi Stock Inventory'][$i]['value'] = $this->int;
                $i++;

                $this->dropdown['Multi Stock Inventory'][$i]['label'] = $source->getName() . " [" . $source->getSourceCode() . "] | Use default for notify Qty";
                $this->dropdown['Multi Stock Inventory'][$i]["id"] = "Msi/notify_stock_qty_use_default/" . $source->getSourceCode();
                $this->dropdown['Multi Stock Inventory'][$i]['style'] = "stock no-configurable";
                $this->dropdown['Multi Stock Inventory'][$i]['type'] = __("Use default for notify the qty for '" . $source->getName() . "'");
                $this->dropdown['Multi Stock Inventory'][$i]['value'] = $this->smallint;
                $this->dropdown['Multi Stock Inventory'][$i]['options'] = $this->helperData->getBoolean();
                $i++;

                $this->dropdown['Multi Stock Inventory'][$i]['label'] = $source->getName() . " [" . $source->getSourceCode() . "] | Stock Status";
                $this->dropdown['Multi Stock Inventory'][$i]["id"] = "Msi/status/" . $source->getSourceCode();
                $this->dropdown['Multi Stock Inventory'][$i]['style'] = "stock no-configurable";
                $this->dropdown['Multi Stock Inventory'][$i]['type'] = __("Stock status for notify the qty for '" . $source->getName() . "'");
                $this->dropdown['Multi Stock Inventory'][$i]['value'] = $this->smallint;
                $this->dropdown['Multi Stock Inventory'][$i]['options'] = $this->helperData->getBoolean();
                $i++;

                $this->inc=$i;
                $this->eventManager->dispatch("massstockupdate_resourcemodel_msi_getdropdown", ["resource" => $this, "inc" => $i, "source" => $source]);
                $i=$this->inc;
            }
        }

        return $this->dropdown;
    }

    /**
     * @param array $mapping
     * @return array
     */
    public function getIndexes($mapping = [])
    {
        return [5 => "cataloginventory_stock", 6 => "inventory"];
    }
}
