<?php

/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Wyomind\MassStockUpdate\Model;

use Wyomind\Framework\Helper\Progress as ProgressHelper;
/**
 *
 * @exclude_var e
 */
class Profiles extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var string
     */
    public $module = "MassStockUpdate";
    public $entity = "Profile";
    /**
     * @var string
     */
    public $name = "Mass Stock Update";
    /**
     * @var array
     */
    public $_params = [];
    /**
     * @var array
     */
    public $_products = [];
    /**
     * @var array
     */
    public $_success = [];
    /**
     * @var array
     */
    public $_warnings = [];
    /**
     * @var array
     */
    public $_notices = [];
    /**
     * @var null|string
     */
    public $_helperClass = null;
    /**
     * @var null|ResourceModel\Product\CollectionFactory
     */
    public $_productCollectionFactory = null;
    /**
     * @var string
     */
    public $_identifierCode = "sku";
    /**
     * @var \Magento\Framework\Indexer\IndexerInterfaceFactory
     */
    public $_indexerFactory;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|null
     */
    public $_ioWrite = null;
    /**
     * @var \Magento\Framework\Filesystem\Directory\WriteInterface|null
     */
    public $_ioRead = null;
    /**
     * @var array
     */
    public $parentSku = [];
    /**
     * @var \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGeneratorFactory
     */
    public $_productUrlRewriteGenerator;
    public function __construct(\Wyomind\MassStockUpdate\Helper\Delegate $wyomind, \Magento\Framework\Model\Context $context, \Magento\Framework\Registry $registry, \Magento\Framework\Indexer\IndexerInterfaceFactory $indexerFactory, \Wyomind\MassStockUpdate\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory, \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGeneratorFactory $productUrlRewriteGenerator, \Magento\Framework\Model\ResourceModel\AbstractResource $abstractResource = null, \Magento\Framework\Data\Collection\AbstractDb $abstractDb = null, array $data = [])
    {
        $wyomind->constructor($this, $wyomind, __CLASS__);
        $this->_helperClass = "\\Wyomind\\" . $this->module . "\\Helper\\Data";
        $this->_indexerFactory = $indexerFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $resource = $this->_appResource;
        $read = $resource->getConnection("core_read");
        $this->tableCpe = $resource->getTableName("catalog_product_entity");
        $this->tableCpev = $resource->getTableName("catalog_product_entity_varchar");
        $this->tableEa = $resource->getTableName("eav_attribute");
        $this->_ioWrite = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        $this->_ioRead = $this->filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        $this->_productUrlRewriteGenerator = $productUrlRewriteGenerator;
        parent::__construct($context, $registry, $abstractResource, $abstractDb, $data);
        $this->progressHelper = $this->objectManager->create("Wyomind\\" . $this->module . "\\Helper\\Progress");
    }
    protected function _construct()
    {
        $this->_init('Wyomind\\' . $this->module . '\\Model\\ResourceModel\\Profiles');
    }
    /** Handle multiple files
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function multipleImport()
    {
        $filePath = $this->getFilePath();
        $files = $this->_storageHelper->evalRegexp($filePath, $this->getFileSystemType(), true, $this->getData());
        $rtn = [];
        if (empty($files)) {
            throw new \Exception(__("There is no source file available. Please check the source file path."));
        }
        foreach ($files as $key => $file) {
            $import = $this->setFilePath($file)->import($key, $filePath);
            $rtn = array_merge($rtn, $import);
        }
        return $rtn;
    }
    /** Core process (entry point)
     * @param $nth
     * @param $filePath
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function import($nth, $filePath)
    {
        try {
            $this->setActionTypeHistory('generate');
            $this->progressHelper->startObservingProgress($this->isLogEnabled(), $this->getId(), $this->getName());
            $this->_eventManager->dispatch("massupdateimport_start", ['profile' => $this]);
            $helperClass = $this->_helperClass;
            $helper = $this->objectManager->get($helperClass);
            if ($this->getEnabled() == 0) {
                throw new \Exception("The profile cannot run, the profile is disabled.");
            }
            $this->progressHelper->log("Starting " . $this->getName(), true);
            $this->progressHelper->log("Current import file " . $this->getFilePath(), true, ProgressHelper::PROCESSING, 0);
            $params = $this->extractParams();
            $profileMethod = $params['profile_method'];
            if ($profileMethod == 0) {
                $profileMethod = $helper::UPDATE;
            }
            $this->_products = [];
            $this->progressHelper->log("Importing data ");
            // retrieving data from file
            $data = $this->getImportData();
            if (isset($data['error']) && $data["error"] == "true") {
                $this->progressHelper->log("" . $data['message'], true, ProgressHelper::FAILED, 0);
                return;
            }
            // collect products : mapping product identifier / entity id
            $this->progressHelper->log(__("Collecting products"));
            $this->_identifierCode = $this->_params['identifier'] ?: "sku";
            $productCollection = $this->_productCollectionFactory->create()->getSkuAndIdentifierCollection($this->_identifierCode, $helper::ADDITIONAL_FIELDS);
            /* Extract additional attributes for a further use in custom PHP script */
            $attributes = $helper::ADDITIONAL_ATTR;
            if (count($attributes)) {
                $resource = $this->_appResource;
                foreach ($attributes as $attribute) {
                    if ($attribute == "price") {
                        $table = $resource->getTableName("catalog_product_index_price");
                        $productCollection->getSelect()->joinLeft($table . ' AS price', 'price.website_id=1 AND  price.entity_id=e.entity_id  AND price.customer_group_id=0', ["price" => "price"]);
                    } elseif ($attribute == "qty") {
                        $table = $resource->getTableName("cataloginventory_stock_item");
                        $productCollection->getSelect()->joinLeft($table . ' AS stock', 'stock.stock_id=1 AND  stock.product_id=e.entity_id', ["qty" => "qty"]);
                    } else {
                        $productCollection->addAttributeToSelect($attribute);
                    }
                }
            }
            foreach ($productCollection as $product) {
                $productData = [];
                foreach ($attributes as $attribute) {
                    $productData[$attribute] = $product->getData($attribute);
                }
                $this->_productData[trim($product->getData($this->_identifierCode))] = $productData;
                $this->_products[trim($product->getData($this->_identifierCode))] = $product->getId();
            }
            $this->progressHelper->log(__("%1 products collected", count($this->_products)));
            $modules = ["System"];
            // analyzing columns mapping
            $this->progressHelper->log(__("Analyzing columns mapping"));
            $columns = json_decode($this->_params['mapping']);
            if ($columns === null) {
                $columns = [];
            }
            $elements = [];
            if (is_array($columns)) {
                foreach ($columns as $column) {
                    $array = explode("/", $column->id);
                    $module = array_shift($array);
                    if ($module == "") {
                        continue;
                    }
                    $elements[$module][] = $array;
                    if (!in_array($module, $modules)) {
                        $modules[] = $module;
                    }
                    if ($this->getCreateConfigurableOnthefly()) {
                        if (in_array($column->configurable, [1, 2])) {
                            if ($module != "ConfigurableProduct") {
                                $module = "ConfigurableProducts" . $module;
                                if (!in_array($module, $modules)) {
                                    $modules[] = $module;
                                }
                            }
                        }
                    }
                }
            }
            foreach ($helper::MODULES as $module) {
                $resource = $this->objectManager->get("\\Wyomind\\" . $this->module . "\\Model\\ResourceModel\\Type\\" . $module);
                $resource->resetGlobal();
                $addModule = $resource->addModuleIf($this, $columns);
                if ($addModule != false) {
                    $modules = array_merge($modules, $addModule);
                }
            }
            $modules = array_unique($modules);
            $indexes = [];
            foreach ($modules as $module) {
                if ($module == "") {
                    continue;
                }
                $resource = $this->objectManager->get("\\Wyomind\\" . $this->module . "\\Model\\ResourceModel\\Type\\" . $module);
                $this->progressHelper->log("Collect data for {$module}");
                $resource->beforeCollect($this, $elements);
                if ($this->getpostProcessIndexers() > $helper::POST_PROCESS_INDEXERS_DISABLED) {
                    if ($this->getpostProcessIndexers() == $helper::POST_PROCESS_INDEXERS_AUTOMATICALLY) {
                        $indexes = array_unique($indexes + $resource->getIndexes($columns));
                    } elseif ($this->getpostProcessIndexers() == $helper::POST_PROCESS_INDEXERS_ONLY_SELECTED) {
                        $indexesToCheck = $resource->getIndexes($columns);
                        $indexesList = explode(',', $this->getpostProcessIndexersSelection());
                        foreach ($indexesToCheck as $k => $indexToCheck) {
                            if (in_array($indexToCheck, $indexesList)) {
                                $indexes[$k] = $indexToCheck;
                            }
                        }
                    }
                }
            }
            ksort($indexes);
            // creating sql script
            $this->progressHelper->log("Creating SQL script");
            // sql file where to generate requests
            if ($this->_params['sql']) {
                $sqlFile = $this->_storageHelper->getMageRootDir() . DIRECTORY_SEPARATOR . $this->_params['sql_path'] . DIRECTORY_SEPARATOR . $this->_params['sql_file'];
                $this->_storageHelper->mkdir($this->_storageHelper->getMageRootDir() . DIRECTORY_SEPARATOR . $this->_params['sql_path']);
            } else {
                $sqlFile = $this->_storageHelper->newTempFileName();
            }
            // open sql file
            $mode = !$nth || !$this->_params['sql'] ? "w" : "a";
            $outCh = $this->_storageHelper->fileOpen(dirname($sqlFile), basename($sqlFile), $mode);
            //$this->_storageHelper->fileWrite($outCh, "SET foreign_key_checks=0;\n");
            // calculate the percentage and the increment
            $reference = $this->_params['sql'] ? 100 : 50;
            $i = 1;
            $total = count($data['data']);
            $step = ceil($total / 100);
            //place this before any script you want to calculate time
            // create the sql queries for each row
            $percent = 0;
            foreach ($data['data'] as $row) {
                foreach ($row as $k => $value) {
                    if (isset($data['header'][$k])) {
                        $row[$data['header'][$k]] = $value;
                    }
                }
                $this->createSql($row, $modules, $columns, $profileMethod);
                if ($i % $step == 0 || $i + $step >= $total) {
                    $percent = round($i * $reference / $total);
                    $this->progressHelper->log("{$i} processed / {$total} lines", true, ProgressHelper::PROCESSING, $percent);
                }
                $i++;
            }
            // sort module by priority
            $moduleByOrder = $helper::MODULES;
            ksort($moduleByOrder);
            foreach ($moduleByOrder as $module) {
                if ($module == "") {
                    continue;
                }
                // add the comment to the sql file
                $resource = $this->objectManager->get("\\Wyomind\\" . $this->module . "\\Model\\ResourceModel\\Type\\" . $module);
                $head = "/************** " . strtoupper($module) . " ******************/
";
                $this->_storageHelper->fileWrite($outCh, $head);
                // get all sql queries
                $resource->afterCollect($this);
                foreach ($resource->queries as $key => $loop) {
                    $sqlQueriesArray = [];
                    if (is_array($loop)) {
                        foreach ($loop as $query) {
                            $queries = str_replace("
", " ", $query);
                            if (is_array($queries)) {
                                $queries = implode("__BREAKLINE__", $queries);
                            }
                            $queries = explode('__BREAKLINE__', $queries);
                            foreach ($queries as $q) {
                                $sqlQueriesArray[] = $q;
                            }
                        }
                    }
                    $sqlQueries = implode("
", $sqlQueriesArray);
                    // write to file
                    $this->_storageHelper->fileWrite($outCh, $sqlQueries . "
");
                }
                $resource->queries = [];
                // add the log message for each module
                $this->progressHelper->log("Execute after collect for {$module}", true, ProgressHelper::PROCESSING, $percent);
            }
            // close generated sql file
            $this->_storageHelper->fileClose($outCh);
            // execute sql file if not in sql mode
            if (!$this->_params['sql']) {
                $percent = $this->executeSqlFile($indexes, $sqlFile, false);
                $this->_storageHelper->deleteFile(dirname($sqlFile), basename($sqlFile));
                $this->postProcess();
            }
            foreach ($modules as $module) {
                if ($module == "") {
                    continue;
                }
                $this->progressHelper->log("Execute after process for {$module}", true, ProgressHelper::PROCESSING, $percent);
                $resource = $this->objectManager->get("\\Wyomind\\" . $this->module . "\\Model\\ResourceModel\\Type\\" . $module);
                $resource->afterProcess($this);
            }
            $willbe = "";
            if ($this->_params['sql']) {
                $willbe = "will be ";
            }
            $log = ["notice" => [], "warning" => [], "success" => []];
            $msg = "";
            if (count($this->_warnings)) {
                //. implode(", ", array_slice($this->_warnings, 0, 20)
                $msg = count($this->_warnings) . " " . __("products %1ignored", $willbe);
                $log["notice"] = $msg;
            }
            if (count($this->_notices)) {
                $msg = count($this->_notices) . " " . __("products %1updated.", $willbe);
                $log["warning"] = $msg;
            }
            if (count($this->_success)) {
                $msg = count($this->_success) . " " . __("products %1imported.", $willbe);
                $log["success"] = $msg;
            }
            $this->progressHelper->log(print_r($msg, true), true, "SUCCEEDED", 100);
            $this->setLastImportReport($this->generateReport($willbe));
            $this->setImportedAt($this->_dateTime->gmtDate('Y-m-d H:i:s'));
            $this->setFilePath($filePath);
            $this->save();
            $this->_eventManager->dispatch("massupdateimport_success", ['profile' => $this]);
            $this->progressHelper->stopObservingProgress();
            return $log;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->progressHelper->log("" . $e->getMessage(), true, ProgressHelper::FAILED, 0);
            $this->_eventManager->dispatch("massupdateimport_failure", ['profile' => $this, 'error' => $e]);
            throw new \Magento\Framework\Exception\LocalizedException(__("<b>Unable to process the profile</b><br> %1", $e->getMessage()));
        }
    }
    /**
     * @return bool
     */
    protected function isLogEnabled()
    {
        return $this->_framework->getStoreConfig(strtolower($this->module) . '/settings/log') ? true : false;
    }
    /** Get the profile configuration
     * @param null $request
     * @return array
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    protected function extractParams($request = null)
    {
        //        if (empty($this->_params)) {
        $this->progressHelper->log(__("Collecting parameters"), false);
        $resource = $this->_appResource;
        $read = $resource->getConnection("core_read");
        $table = $resource->getTableName(strtolower($this->module) . "_profiles");
        $fields = $read->describeTable($table);
        foreach (array_keys($fields) as $field) {
            $this->_params[$field] = $request !== null && (is_string($request->getParam($field)) || is_array($request->getParam($field))) ? $request->getParam($field) : $this->getData($field);
        }
        $this->progressHelper->log(__("Parameters collected"), false);
        //        }
        return $this->_params;
    }
    /** Get the data to import
     * @param null $request
     * @param $limit
     * @param bool $isOutput
     * @return array|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getImportData($request = null, $limit = INF, $isOutput = false)
    {
        try {
            $this->progressHelper->log(__("Retrieving data"), false);
            if ($request == null) {
                $params = $this->extractParams($request);
            } else {
                $params = $request->getParams();
            }
            if ($params['file_path'] == "") {
                return ['error' => true, 'message' => __("No data preview available until source file is added.<br/><br/>Minimize this screen, and add a new source file under the \"File Location\" settings.")];
            }
            /* retrieve the file containing the data to update */
            $tmpFile = $this->_storageHelper->getImportFile($params);
            /* retrieve the data contained in the file to update */
            $data = $this->_dataHelper->getData($tmpFile, $params, $limit, $isOutput);
            if (isset($data["data"])) {
                $this->progressHelper->log(__("Data retrieved : %1 rows found", count($data['data'])), false);
            }
            /* remove tmp file */
            $this->progressHelper->log(__('Removing tmp file : %1', $tmpFile), false);
            $this->_storageHelper->deleteFile(dirname($tmpFile), basename($tmpFile));
            return $data;
        } catch (\Exception $e) {
            throw new \Magento\Framework\Exception\LocalizedException(__("Error: %1", $e->getMessage()));
        }
    }
    /**
     * row contains update information, custom rules already applied
     * @param $cell
     * @param $modules
     * @param $columns
     * @param $profileMethod
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createSql($cell, $modules, $columns, $profileMethod)
    {
        $helperClass = $this->_helperClass;
        $helper = $this->objectManager->get($helperClass);
        try {
            // Reset the stored Data for each module
            foreach ($modules as $module) {
                $resource = $this->objectManager->get("\\Wyomind\\" . $this->module . "\\Model\\ResourceModel\\Type\\" . $module);
                $resource->reset();
            }
            // Apply custom script on identifier row
            if (!isset($cell[$this->getIdentifierOffset()])) {
                return;
            }
            $additionalData = [];
            if (isset($this->_productData[trim($cell[$this->getIdentifierOffset()])])) {
                $additionalData = $this->_productData[trim($cell[$this->getIdentifierOffset()])];
            }
            $identifier = $this->_dataHelper->execPhp($this->getIdentifierScript(), $cell, $cell[$this->getIdentifierOffset()], $additionalData);
            if ($identifier === false) {
                return;
            } elseif ($identifier === true) {
                $identifier = "";
            }
            //collect data in $cell variable
            foreach ($columns as $column) {
                if (isset($column->index) && $column->index != "" && isset($cell[$column->index])) {
                    $cell[$column->source] = $cell[$column->index];
                }
            }
            // get the parent sku if configurable on the fly
            if ($this->getCreateConfigurableOnthefly()) {
                foreach ($columns as $i => $column) {
                    if ($column->id == "ConfigurableProduct/parentSku") {
                        if (isset($column->index) && $column->index != '') {
                            $parentSku = $cell[$column->index];
                        }
                        $parentSku = $this->_dataHelper->execPhp($column->scripting, $cell, $parentSku, $additionalData);
                        if ($parentSku === false || $parentSku === true) {
                            unset($parentSku);
                        }
                        break;
                    }
                }
            }
            // does the product exist ?
            $productExists = true;
            $parentExists = true;
            if (!isset($this->_products[$identifier])) {
                $productExists = false;
            }
            if (isset($parentSku) && !isset($this->_products[$parentSku])) {
                $parentExists = false;
            }
            $productId = null;
            $parentProductId = null;
            // check if the row must be processed depending on the profile method
            $updateOnly = $profileMethod == $helper::UPDATE;
            $importOnly = $profileMethod == $helper::IMPORT;
            $updateAndImport = $profileMethod == $helper::UPDATEIMPORT;
            if ($updateOnly && $productExists || $importOnly && !$productExists || $updateAndImport) {
                // initialize the system modules
                $resource = $this->objectManager->get("\\Wyomind\\" . $this->module . "\\Model\\ResourceModel\\Type\\System");
                if (strtolower($this->module) == "massproductimport") {
                    if ($this->getCreateConfigurableOnthefly()) {
                        $configurableProductResource = $this->objectManager->get("\\Wyomind\\" . $this->module . "\\Model\\ResourceModel\\Type\\ConfigurableProductsSystem");
                    }
                }
                if ($productExists) {
                    $productId = (int) $this->_products[$identifier];
                    $resource->fields = [];
                } else {
                    $resource->collect($productId, $identifier, ["option" => [$this->_identifierCode]], $this);
                }
                if ($parentExists) {
                    if (isset($parentSku)) {
                        $parentProductId = (int) $this->_products[$parentSku];
                    }
                    if (strtolower($this->module) == "massproductimport") {
                        if ($this->getCreateConfigurableOnthefly()) {
                            $configurableProductResource->fields = [];
                        }
                    }
                } else {
                    if (isset($parentSku)) {
                        if ($this->getCreateConfigurableOnthefly()) {
                            $configurableProductResource->collect($productId, $parentSku, ["option" => [$this->_identifierCode]], $this);
                        }
                    }
                }
                // the product exist !
                // preparing data required to generate the requests
                // for each column
                $counter = 0;
                $skipped = false;
                $cell["identifier"] = $identifier;
                foreach ($columns as $column) {
                    if (!isset($column->importupdate)) {
                        $column->importupdate = 2;
                    }
                    switch ($column->importupdate) {
                        // new product only
                        case 0:
                            if ($productExists) {
                                continue 2;
                            }
                            break;
                        // existing product only
                        case 1:
                            if (!$productExists) {
                                continue 2;
                            }
                            break;
                    }
                    if (!$column->enabled) {
                        continue;
                    }
                    if ($column->enabled) {
                        if ($skipped) {
                            continue;
                        }
                        $self = "";
                        if (isset($column->index) && $column->index != "" && isset($cell[$column->index])) {
                            // attribute is mapped with one data source
                            if (is_string($cell[$column->index])) {
                                $self = trim($cell[$column->index]);
                            } else {
                                $self = $cell[$column->index];
                            }
                        } else {
                            // attribute is mapped with a custom value
                            if ($column->scripting == "") {
                                $self = $column->default;
                            }
                        }
                        if ($this->_framework->moduleIsEnabled("Wyomind_MassProductImport")) {
                            if (isset($column->rule)) {
                                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                                $MpiHelperData = $objectManager->create("\\Wyomind\\MassProductImport\\Helper\\Data");
                                $self = $MpiHelperData->replacementRules($column->rule, $self);
                            }
                        }
                        if ($column->scripting != "") {
                            try {
                                $self = $this->_dataHelper->execPhp($column->scripting, $cell, $self, $additionalData);
                                if ($self === false) {
                                    $skipped = true;
                                    continue;
                                } elseif ($self === true) {
                                    continue;
                                }
                            } catch (\Exception $e) {
                                throw new \Magento\Framework\Exception\LocalizedException(__("Error in script for %1 :%2", $column->label, nl2br(htmlentities($e->getMessage()))));
                            }
                        }
                        $strategyId = explode("/", $column->id);
                        $module = array_shift($strategyId);
                        $strategy["storeviews"] = $column->storeviews;
                        $strategy["option"] = $strategyId;
                        if ($module == null) {
                            continue;
                        }
                        // if product doesnt  exists
                        // if configurable on the fly
                        if (!$this->getCreateConfigurableOnthefly()) {
                            $column->configurable = 0;
                        }
                        /**
                         * Apply to all products excepted if only for Configurable product created on the fly
                         */
                        if (in_array($column->configurable, [0, 2])) {
                            if (!$productExists) {
                                $sku = $identifier;
                                if ($this->_identifierCode == "sku") {
                                    $id = "(SELECT entity_id FROM " . $this->tableCpe . " WHERE sku=" . $this->_dataHelper->sanitizeField($sku) . " LIMIT 1)";
                                } else {
                                    $id = "(SELECT entity_id FROM " . $this->tableCpev . " AS cpev WHERE value='" . $sku . "' AND attribute_id=(SELECT attribute_id FROM " . $this->tableEa . " WHERE attribute_code='" . $this->_identifierCode . "') LIMIT 1)";
                                }
                            } else {
                                $id = $productId;
                            }
                            $resource = $this->objectManager->get("\\Wyomind\\" . $this->module . "\\Model\\ResourceModel\\Type\\" . $module);
                            $resource->collect($id, $self, $strategy, $this);
                        }
                        if ($this->getCreateConfigurableOnthefly()) {
                            /**
                             * Apply to Configurable products created on the fly
                             */
                            if (in_array($column->configurable, [1, 2])) {
                                // Configurable product already processed or doens't have to be processed
                                if (isset($parentSku) && !isset($this->parentSku[$parentSku])) {
                                    if (!$parentExists) {
                                        $sku = $parentSku;
                                        if ($this->_identifierCode == "sku") {
                                            $id = "(SELECT entity_id FROM " . $this->tableCpe . " WHERE sku=" . $this->_dataHelper->sanitizeField($sku) . " LIMIT 1)";
                                        } else {
                                            $id = "(SELECT entity_id FROM " . $this->tableCpev . " AS cpev WHERE value='" . $sku . "' AND attribute_id=(SELECT attribute_id FROM " . $this->tableEa . " WHERE attribute_code='" . $this->_identifierCode . "') LIMIT 1)";
                                        }
                                    } else {
                                        $id = $parentProductId;
                                    }
                                    /**
                                     * In that particular case we need to move towards ConfigurableProduct
                                     */
                                    if ($module == "ConfigurableProduct" && $strategy["option"][0] == "attributes") {
                                        $resource = $this->objectManager->get("\\Wyomind\\" . $this->module . "\\Model\\ResourceModel\\Type\\ConfigurableProduct");
                                    } else {
                                        $resource = $this->objectManager->get("\\Wyomind\\" . $this->module . "\\Model\\ResourceModel\\Type\\ConfigurableProducts" . $module);
                                    }
                                    $resource->collect($id, $self, $strategy, $this);
                                }
                            }
                        }
                    }
                }
                foreach ($modules as $module) {
                    if ($module == "") {
                        continue;
                    }
                    /* do not create configurable product on the fly
                     * or sku is skipped or is not mapped
                     * or configurable product already processed
                     */
                    if (stristr($module, "ConfigurableProducts") != false && (!$this->getCreateConfigurableOnthefly() || !isset($parentSku) || isset($this->parentSku[$parentSku]))) {
                        continue;
                    }
                    // if product doesnt  exist
                    if (stristr($module, "ConfigurableProducts") != false) {
                        if (!$parentExists) {
                            $sku = $parentSku;
                            if ($this->_identifierCode == "sku") {
                                $id = "(SELECT entity_id FROM " . $this->tableCpe . " WHERE sku=" . $this->_dataHelper->sanitizeField($sku) . " LIMIT 1)";
                            } else {
                                $id = "(SELECT entity_id FROM " . $this->tableCpev . " AS cpev WHERE value='" . $sku . "' AND attribute_id=(SELECT attribute_id FROM " . $this->tableEa . " WHERE attribute_code='" . $this->_identifierCode . "') LIMIT 1)";
                            }
                        } else {
                            $id = $parentProductId;
                        }
                    } else {
                        if (!$productExists) {
                            $sku = $identifier;
                            if ($this->_identifierCode == "sku") {
                                $id = "(SELECT entity_id FROM " . $this->tableCpe . " WHERE sku=" . $this->_dataHelper->sanitizeField($sku) . " LIMIT 1)";
                            } else {
                                $id = "(SELECT entity_id FROM " . $this->tableCpev . " AS cpev WHERE value='" . $sku . "' AND attribute_id=(SELECT attribute_id FROM " . $this->tableEa . " WHERE attribute_code='" . $this->_identifierCode . "') LIMIT 1)";
                            }
                        } else {
                            $id = $productId;
                        }
                    }
                    $resource = $this->objectManager->get("\\Wyomind\\" . $this->module . "\\Model\\ResourceModel\\Type\\" . $module);
                    $resource->prepareQueries($id, $this);
                }
                // delete from the queries the product that are in the file
                $resource = $this->objectManager->get("\\Wyomind\\" . $this->module . "\\Model\\ResourceModel\\Type\\System");
                $resource->updateQueries($productId, $this);
                if (strtolower($this->module) == "massproductimport") {
                    $resource->updateQueries($parentProductId, $this);
                }
                if ($productExists && !isset($this->_notices[$identifier])) {
                    $this->_notices[$identifier] = $identifier;
                } elseif (!isset($this->_success[$identifier])) {
                    $this->_success[$identifier] = $identifier;
                }
                if (isset($parentSku) && !isset($this->_notices[$parentSku])) {
                    if ($parentExists) {
                        $this->_notices[$parentSku] = $parentSku;
                    } elseif (!isset($this->_success[$parentSku])) {
                        $this->_success[$parentSku] = $parentSku;
                    }
                }
            } else {
                $this->_warnings[] = $identifier;
            }
            // configurable product already processed
            if (strtolower($this->module) == "massproductimport" && isset($parentSku)) {
                $this->parentSku[$parentSku] = $parentSku;
            }
        } catch (\Exception $e) {
            $this->progressHelper->log($e->getMessage(), true, ProgressHelper::ERROR);
            throw new \Magento\Framework\Exception\LocalizedException(__("%1", $e->getMessage()));
        }
    }
    /** Execute the SQL queries
     * @param array $indexes
     * @param bool $sqlFile
     * @param bool $dryRun
     * @return false|float|int
     * @throws \Exception
     */
    public function executeSqlFile($indexes = [], $sqlFile = false, $dryRun = true)
    {
        $this->progressHelper->startObservingProgress($this->isLogEnabled(), $this->getId(), $this->getName(), true);
        $this->progressHelper->log("Start importing sql queries for " . $this->getName(), true);
        if (!$sqlFile) {
            $sqlFile = $this->_storageHelper->getMageRootDir() . DIRECTORY_SEPARATOR . $this->getSqlPath() . DIRECTORY_SEPARATOR . $this->getSqlFile();
        }
        $file = $this->_storageHelper->fileOpen(dirname($sqlFile), basename($sqlFile), 'r');
        $writeConnection = $this->_appResource->getConnection('core_write');
        $writeConnection->beginTransaction();
        $counter = 0;
        $total = 0;
        try {
            while (($line = $this->_storageHelper->fileReadLine($file)) !== false) {
                $total++;
            }
        } catch (\Magento\Framework\Exception\FileSystemException $e) {
            $file = $this->_storageHelper->fileOpen(dirname($sqlFile), basename($sqlFile), 'r');
        }
        $step = ceil($total / 100);
        $reference = $dryRun ? 0 : 50;
        try {
            while (($request = $this->_storageHelper->fileReadLine($file)) !== false) {
                try {
                    if (trim($request) != "") {
                        $writeConnection->rawQuery($request);
                    }
                    if ($counter % $step == 0 || $counter + $step >= $total) {
                        $percent = round($counter * (100 - $reference) / $total) + $reference;
                        $this->progressHelper->log("{$counter} processed / {$total} queries", true, ProgressHelper::PROCESSING, $percent);
                    }
                    $counter++;
                } catch (\Exception $e) {
                    $writeConnection->rollback();
                    $this->progressHelper->log(__("Error in SQL query: %1", $request), false);
                    $this->progressHelper->log("Error in SQL query: " . str_replace(";", "", $request), true, ProgressHelper::FAILED, 0);
                    $this->_eventManager->dispatch("massupdateimport_sql_error", ['profile' => $this, 'error' => $e, 'request' => $request]);
                    throw new \Exception(__("SQL error in {$request}.<br/>Error was [%1]<br><b>All updates have been rollback</b>", $e->getMessage()));
                    break;
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            // OEF
            if ($counter % $step == 0 || $counter + $step >= $total) {
                $percent = round($counter * (100 - $reference) / $total) + $reference;
                $this->progressHelper->log("{$counter} processed / {$total} queries", true, ProgressHelper::PROCESSING, $percent);
            }
            $this->progressHelper->log(__("%1 sql queries executed", $counter), true, ProgressHelper::PROCESSING, $percent);
            $writeConnection->commit();
            foreach ($indexes as $index) {
                if ($index == "catalog_url") {
                    $resource = $this->objectManager->get("\\Wyomind\\MassProductImport\\Model\\ResourceModel\\Type\\Attribute");
                    $skus = array_merge($this->_notices, $this->_success);
                    $columns = json_decode($this->_params['mapping']);
                    $resource->getIndexes($columns);
                    $storeIds = $resource->urlRewriteStoreViews;
                    foreach ($storeIds as $storeId) {
                        if (!$storeId) {
                            continue;
                        }
                        $collection = $productCollection = $this->_productCollectionFactory->create();
                        $collection->addStoreFilter($storeId)->setStoreId($storeId);
                        $collection->addFieldToFilter("sku", ["in" => $skus]);
                        $collection->addAttributeToSelect(['url_path', 'url_key'], true);
                        $list = $collection->load();
                        foreach ($list as $product) {
                            $product->setStoreId($storeId);
                            $this->_urlPersistInterface->deleteByData([\Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_ID => $product->getId(), \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::ENTITY_TYPE => \Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator::ENTITY_TYPE, \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::REDIRECT_TYPE => 0, \Magento\UrlRewrite\Service\V1\Data\UrlRewrite::STORE_ID => $storeId]);
                            try {
                                $this->_urlPersistInterface->replace($this->_productUrlRewriteGenerator->create()->generate($product));
                            } catch (\Exception $e) {
                                $this->progressHelper->log(__('Duplicated url for %1', $product->getId()), true);
                            }
                        }
                    }
                    $msg = ucwords(str_replace("_", " ", $index)) . " " . __("re-indexed");
                    $this->progressHelper->log("{$msg}", true, ProgressHelper::PROCESSING, 100);
                    continue;
                }
                $process = $this->_indexerFactory->create()->load($index);
                $process->reindexAll();
                $msg = ucwords(str_replace("_", " ", $index)) . " " . __("re-indexed");
                $this->progressHelper->log("{$msg}", true, ProgressHelper::PROCESSING, 100);
            }
            $this->progressHelper->log("Sql file has been executed", true, ProgressHelper::SUCCEEDED, $percent);
            $this->progressHelper->stopObservingProgress();
            return $percent;
        }
    }
    /**
     * Execute post process action
     */
    public function postProcess()
    {
        $helperClass = $this->_helperClass;
        $helper = $this->objectManager->get($helperClass);
        $rootdir = rtrim($this->_storageHelper->getMageRootDir(), "/");
        if ($this->_params['post_process_action'] == $helper::POST_PROCESS_ACTION_MOVE) {
            if ($this->_params['file_system_type'] == $helper::LOCATION_MAGENTO) {
                $this->_storageHelper->moveFile($rootdir . DIRECTORY_SEPARATOR . ltrim(dirname($this->getFilePath()), "\\/"), basename($this->getFilePath()), $rootdir . DIRECTORY_SEPARATOR . ltrim($this->_params['post_process_move_folder'], "\\/"), basename($this->getFilePath()));
            } elseif ($this->_params['file_system_type'] == $helper::LOCATION_FTP) {
                $this->_storageHelper->ftpFileAction($this->_params, 'move');
            }
        } elseif ($this->_params['post_process_action'] == $helper::POST_PROCESS_ACTION_DELETE) {
            if ($this->_params['file_system_type'] == $helper::LOCATION_MAGENTO) {
                $this->_storageHelper->deleteFile($rootdir . DIRECTORY_SEPARATOR . ltrim(dirname($this->getFilePath()), "\\/"), basename($this->getFilePath()));
            } elseif ($this->_params['file_system_type'] == $helper::LOCATION_FTP) {
                $this->_storageHelper->ftpFileAction($this->_params, 'delete');
            }
        }
    }
    /** Prepare the report
     * @param $willBe
     * @return string
     */
    public function generateReport($willBe)
    {
        $html = "";
        if (count($this->_warnings)) {
            $html .= "<h3>" . count($this->_warnings) . " " . __("products %1ignored", $willBe) . "</h3>";
            $html .= "<p>" . implode(", ", $this->_warnings) . "</p>";
        }
        if (count($this->_notices)) {
            $html .= "<h3>" . count($this->_notices) . " " . __("products %1updated.", $willBe) . "</h3>";
            $html .= "<p>" . implode(", ", $this->_notices) . "</p>";
        }
        if (count($this->_success)) {
            $html .= "<h3>" . count($this->_success) . " " . __("products %1imported.", $willBe) . "</h3>";
            $html .= "<p>" . implode(", ", $this->_success) . "</p>";
        }
        return $html;
    }
}