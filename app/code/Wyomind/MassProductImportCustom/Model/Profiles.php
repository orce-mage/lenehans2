<?php

/**
 * Copyright © 2021 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassProductImportCustom\Model;

use Wyomind\Framework\Helper\Progress as ProgressHelper;

/**
 * Class Profiles
 * @package Wyomind\MassStockUpdate\Model
 */
class Profiles extends \Wyomind\MassProductImport\Model\Profiles
{
    /**
     * SKU PREFIX
     */
    const SKU_PREFIX = "TP_";

    /**
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
                        $productCollection->getSelect()->joinLeft($table . ' AS price', 'price.website_id=1 AND  price.entity_id=e.entity_id  AND price.customer_group_id=0', array("price" => "price"));
                    } elseif ($attribute == "qty") {
                        $table = $resource->getTableName("cataloginventory_stock_item");
                        $productCollection->getSelect()->joinLeft($table . ' AS stock', 'stock.stock_id=1 AND  stock.product_id=e.entity_id', array("qty" => "qty"));
                    } else {
                        $productCollection->addAttributeToSelect($attribute);
                    }
                }

            }

            foreach ($productCollection as $product) {
                $productData = array();
                foreach ($attributes as $attribute) {

                    $productData[$attribute] = $product->getData($attribute);

                }
                $this->_productData[trim($product->getData($this->_identifierCode))] = $productData;
                $sku = trim($product->getData($this->_identifierCode));
                if (substr($sku, 0, 3) == self::SKU_PREFIX) {
                    $sku = substr($sku, 3);
                }
                $this->_products[$sku] = $product->getId();

            }

            $this->progressHelper->log(__("%1 products collected", count($this->_products)));

            $modules = ["System"];

// analyzing columns mapping
            $this->progressHelper->log(__("Analyzing columns mapping"));
            $columns = json_decode($this->_params['mapping']);
            if ($columns === NULL) {
                $columns = array();
            }

            $elements = array();

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
                        if (in_array($column->configurable, array(1, 2))) {
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
                $resource = $this->objectManager->get("\Wyomind\\" . $this->module . "\Model\ResourceModel\Type\\" . $module);
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
                $resource = $this->objectManager->get("\Wyomind\\" . $this->module . "\Model\ResourceModel\Type\\" . $module);

                $this->progressHelper->log("Collect data for $module");
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
            $mode = (!$nth || !$this->_params['sql']) ? "w" : "a";
            $outCh = $this->_storageHelper->fileOpen(dirname($sqlFile), basename($sqlFile), $mode);

//$this->_storageHelper->fileWrite($outCh, "SET foreign_key_checks=0;\n");
// calculate the percentage and the increment
            $reference = ($this->_params['sql']) ? 100 : 50;
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
                    $this->progressHelper->log("$i processed / $total lines", true, ProgressHelper::PROCESSING, $percent);
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
                $resource = $this->objectManager->get("\Wyomind\\" . $this->module . "\Model\ResourceModel\Type\\" . $module);
                $head = "/************** " . strtoupper($module) . " ******************/\n";
                $this->_storageHelper->fileWrite($outCh, $head);
// get all sql queries
                $resource->afterCollect($this);

                foreach ($resource->queries as $key => $loop) {
                    $sqlQueriesArray = [];
                    if (is_array($loop)) {
                        foreach ($loop as $query) {
                            $queries = str_replace("\n", " ", $query);
                            if (is_array($queries)) {
                                $queries = implode("__BREAKLINE__", $queries);
                            }
                            $queries = explode('__BREAKLINE__', $queries);
                            foreach ($queries as $q) {
                                $sqlQueriesArray[] = $q;
                            }
                        }
                    }
                    $sqlQueries = implode("\n", $sqlQueriesArray);
// write to file
                    $this->_storageHelper->fileWrite($outCh, $sqlQueries . "\n");
                }
                $resource->queries = array();
// add the log message for each module
                $this->progressHelper->log("Execute after collect for $module", true, ProgressHelper::PROCESSING, $percent);
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

                $this->progressHelper->log("Execute after process for $module", true, ProgressHelper::PROCESSING, $percent);

                $resource = $this->objectManager->get("\Wyomind\\" . $this->module . "\Model\ResourceModel\Type\\" . $module);
                $resource->afterProcess($this);
            }

            $willbe = "";
            if ($this->_params['sql']) {
                $willbe = "will be ";
            }

            $log = array("notice" => [], "warning" => [], "success" => []);
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
}
