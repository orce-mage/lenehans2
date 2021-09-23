<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Wyomind\MassStockUpdate\Helper;

use Wyomind\MassStockUpdate\JSONPath\JSONPath;

/**
 * Class Data
 * @package Wyomind\MassStockUpdate\Helper
 * 
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     *
     */
    const FIELD_IMPLODE = ",";
    /**
     *
     */
    const LOCATION_MAGENTO = 1;
    /**
     *
     */
    const LOCATION_FTP = 2;
    /**
     *
     */
    const LOCATION_URL = 3;
    /**
     *
     */
    const LOCATION_WEBSERVICE = 4;
    /**
     *
     */
    const LOCATION_DROPBOX = 5;
    /**
     *
     */
    const IS_MAGENTO_EXPORT_YES = 1;
    /**
     *
     */
    const IS_MAGENTO_EXPORT_NO = 2;
    /**
     *
     */
    const POST_PROCESS_ACTION_NOTHING = 0;
    /**
     *
     */
    const POST_PROCESS_ACTION_DELETE = 1;
    /**
     *
     */
    const POST_PROCESS_ACTION_MOVE = 2;
    /**
     *
     */
    const POST_PROCESS_INDEXERS_DISABLED = 0;
    /**
     *
     */
    const POST_PROCESS_INDEXERS_AUTOMATICALLY = 1;
    /**
     *
     */
    const POST_PROCESS_INDEXERS_ONLY_SELECTED = 2;
    /**
     *
     */
    const NO = 0;
    /**
     *
     */
    const YES = 1;
    /**
     *
     */
    const TMP_FOLDER = "/var/tmp/massstockupdate/";
    /**
     *
     */
    const UPLOAD_DIR = "/var/upload/";
    /**
     *
     */
    const TMP_FILE_PREFIX = "massstockupdate_";
    /**
     *
     */
    const TMP_FILE_EXT = "orig";
    /**
     *
     */
    const CSV = 1;
    /**
     *
     */
    const XML = 2;
    /**
     *
     */
    const JSON = 3;
    /**
     *
     */
    const UPDATE = 1;
    /**
     *
     */
    const IMPORT = 2;
    /**
     *
     */
    const UPDATEIMPORT = 3;

    /**
     *
     */
    const ADDITIONAL_ATTR = [];
    /**
     *
     */
    const ADDITIONAL_FIELDS = [];
    /**
     *
     */
    const MODULES = [
        10 => "System",
        30 => "AdvancedInventory",
        40 => "Stock",
        45 => "Msi",
        100 => "Ignored"];
    /**
     * @var string
     */
    public $module = "MassStockUpdate";
    /**
     * @var \Magento\Framework\Filesystem\Driver\FileFactory|null
     */
    public $_driverFileFactory = null;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface|null
     */
    public $_attributeRepository = null;
    /**
     * @var \Magento\Framework\ObjectManager\ObjectManager|null
     */
    protected $_objectManager = null;
    /**
     * @var \Magento\Store\Model\StoreManager|null
     */
    public $_storeManager = null;

    /**
     * @var null|\Wyomind\Framework\Helper\Module
     */
    public $_framework = null;

    /**
     * @var null|string
     */
    public $jsonAttributes = null;

    /**
     * @var \Magento\Catalog\Model\ProductFactory|null
     */
    public $productModelFactory = null;
    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $productCollectionFactory;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\Filesystem\Driver\FileFactory $driverFileFactory
     * @param \Wyomind\Framework\Helper\Module $framework
     * @param \Magento\Store\Model\StoreManager $storeManager
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Magento\Catalog\Model\ProductFactory $productModelFactory
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Filesystem\Driver\FileFactory $driverFileFactory,
        \Wyomind\Framework\Helper\Module $framework,
        \Magento\Store\Model\StoreManager $storeManager,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Magento\Catalog\Model\ProductFactory $productModelFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
    
        parent::__construct($context);
        $this->_driverFileFactory = $driverFileFactory;
        $this->productModelFactory = $productModelFactory;
        $this->_framework = $framework;
        $this->_attributeRepository = $attributeRepository;
        $this->_objectManager = $objectManager;
        $this->_storeManager = $storeManager;
        $this->productCollectionFactory = \Magento\Framework\App\ObjectManager::getInstance()->get("Magento\Catalog\Model\ResourceModel\Product\CollectionFactory");
    }

    /**
     * @return string
     */
    public function getMaxFileSize()
    {
        static $max_size = -1;

        if ($max_size < 0) {
// Start with post_max_size.
            $post_max_size = $this->parseSize(ini_get('post_max_size'));
            if ($post_max_size > 0) {
                $max_size = $post_max_size;
            }

// If upload_max_size is less, then reduce. Except if upload_max_size is
// zero, which indicates no limit.
            $upload_max = $this->parseSize(ini_get('upload_max_filesize'));
            if ($upload_max > 0 && $upload_max < $max_size) {
                $max_size = $upload_max;
            }
        }
        return $this->readableSize($max_size);
    }

    /**
     * @param string $size
     * @return float
     */
    public function parseSize($size)
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9\.]/', '', $size); // Remove the non-numeric characters from the size.
        if ($unit) {
// Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        } else {
            return round($size);
        }
    }

    /**
     * @param string $size
     * @return string
     */
    public function readableSize($size)
    {
        $i = 0;
        $unit = ["b", "kb", "mb", "gb", "tb", "pb", "eb", "zb", "yb"];
        while ($size > 1024) {
            $i++;
            $size = $size / 1024;
        }
        return $size . ucfirst($unit[$i]);
    }

    /**
     * @param string $string
     * @return bool
     */
    function isJSON($string)
    {
        return is_string($string) && is_array(json_decode($string, true)) && (json_last_error() == JSON_ERROR_NONE) ? true : false;
    }

    /**
     * @param $file
     * @param $params
     * @param $limit
     * @param bool $isOutput
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getData(
        $file,
        $params,
        $limit = INF,
        $isOutput = false
    ) {
    
        try {
            $data = [];
            $driverFile = $this->_driverFileFactory->create();


            $counter = 0;
            $mapping = json_decode($params['mapping']);

            $headers = [];
            $colors = [null];
            $tags = [null];
            if ($isOutput) {
                $headers[] = $params["identifier"];
                if ($mapping) {
                    foreach ($mapping as $column) {
                        if ($column->enabled) {
                            $headers[] = $column->label;
                            $colors[] = $column->color;
                            $tags[] = $column->tag;
                        }
                    }
                }
            }

            switch ($params["file_type"]) {
                case self::CSV:
                    $inCh = $driverFile->fileOpen($file, 'r');
                    // if no header row reserve the first row to place the headers
                    if (!$params['has_header']) {
                        $counter++;
                    }
                    $i = 0;
                    $previous = [];

                    $cells = [];

                    $identifier = $params["identifier"];
                    $identifiers = [];
                    while ($counter <= $limit && ($cell = $driverFile->fileGetCsv($inCh, 0, $params['field_delimiter'])) != false) {
                        $cell = array_map(
                            function ($tmp) {
                                if (!mb_detect_encoding($tmp, 'UTF-8', true)) {
                                    return utf8_encode($tmp);
                                } else {
                                    return $tmp;
                                }
                            },
                            $cell
                        );

                        $cells[] = $cell;

                        $identifiers[] = $cell[(int)$params["identifier_offset"]];
                    }


                    $globalAdditionalData = [];
                    if (!empty(self::ADDITIONAL_ATTR)) {
                        $attribute = \Magento\Framework\App\ObjectManager::getInstance()->get("Wyomind\MassStockUpdate\Model\ResourceModel\Type\System");

                        $tableEaov = $attribute->getTable('eav_attribute_option_value');
                        $tableEao = $attribute->getTable('eav_attribute_option');
                        $tableEa = $attribute->getTable('eav_attribute');
                        $read = $attribute->getConnection();

                        $select = "SELECT eao.option_id,value,store_id,ea.attribute_id, attribute_code FROM " . $tableEao . " AS eao
                                   INNER JOIN " . $tableEa . " as ea ON ea.attribute_id = eao.attribute_id
                                   INNER JOIN " . $tableEaov . " AS eaov ON eao.option_id = eaov.option_id
                                   WHERE store_id=0";

                        $dropdownLabels = $read->fetchAll($select);

                        foreach ($dropdownLabels as $attributeLabel) {
                            $attributeId = $attributeLabel["attribute_code"];
                            $optionId = $attributeLabel["option_id"];
                            $attributeLabels[$attributeId][$optionId] = $attributeLabel["value"];
                        }

                        $identifiersChunks = array_chunk($identifiers, 500);
                        foreach ($identifiersChunks as $chunk) {
                            /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
                            $collection = $this->productCollectionFactory->create();

                            $collection->addAttributeToFilter($identifier, ["in" => $chunk]);
                            $collection->addAttributeToSelect(array_merge([$identifier], self::ADDITIONAL_ATTR));
                            //var_dump($collection->getSelect()->__toString());
                            foreach ($collection as $prod) {
                                $globalAdditionalData[$prod->getData($identifier)] = [];
                                foreach (self::ADDITIONAL_ATTR as $att) {
                                    if (!isset($attributeLabels[$att][$prod->getData($att)])) {
                                        $globalAdditionalData[$prod->getData($identifier)][$att] = $prod->getData($att);
                                    } else {
                                        $globalAdditionalData[$prod->getData($identifier)][$att] = $attributeLabels[$att][$prod->getData($att)];
                                    }
                                }
                            }
                        }
                    }

                    foreach ($cells as $cell) {
                        if ($limit <= $counter) {
                            break;
                        }


                        $rangeCondition = $this->getLineRangeCondition($params['line_filter'], $i, $cell[(int)$params["identifier_offset"]], $cell, $params['has_header']);
                        $i++;
                        // if range condition returns FALSE
                        if (!$params['has_header'] && $rangeCondition == false || $params['has_header'] && $counter > 0 && $rangeCondition == false) {
                            continue;
                        }


                        $additionalData = $globalAdditionalData[$cell[(int)$params["identifier_offset"]]] ?? [];
//                        if (!empty(self::ADDITIONAL_ATTR)) {
//                            /** @var \Magento\Catalog\Model\Product $productModel */
//                            $productModel = $this->productModelFactory->create();
//                            $product = $productModel->loadByAttribute($params["identifier"], $cell[(int)$params["identifier_offset"]], self::ADDITIONAL_ATTR);
//
//                            if ($product) {
//                                foreach ($product->getData() as $key => $value) {
//                                    if (in_array($key, self::ADDITIONAL_ATTR)) {
//                                        $additionalData[$key] = $value;
//
//                                    }
//                                }
//
//                            }
//                        }

                        if ($isOutput) {
                            $skipped = false;
                            $data[$counter] = [];
                            try {
                                $identifier_value = $this->execPhp($params["identifier_script"], $cell, $cell[(int)$params["identifier_offset"]], $additionalData);
                            } catch (\Exception $e) {
                                $rtn['status'] = "error";
                                $rtn['message'] = __("Error in script for $column->label :") . nl2br(htmlentities($e->getMessage()));
                                return $rtn;
                            }
                            if ($identifier_value === false) {
                                $skipped = true;
                                $identifier_value = "<i class='skipped'> " . __("skip this cell and next cells") . "</i>";
                            } elseif ($identifier_value === true) {
                                $identifier_value = "<i class='skipped'> " . __("skip only this cell") . "</i>";
                            }
                            $data[$counter][] = $identifier_value;
                            $cell["identifier"] = $identifier_value;

                            foreach ($mapping as $column) {
                                if (isset($column->index) && $column->index != "") {
                                    $cell[$column->source] = $cell[$column->index];
                                }
                            }

                            if ($mapping) {
                                foreach ($mapping as $column) {
                                    $self = "";


                                    if ($column->enabled) {
                                        if ($skipped === true) {
                                            $self = "<i class='skipped'> " . __("skipped") . "</i>";
                                            $data[$counter][] = $self;
                                            continue;
                                        }
                                        if (isset($column->index) && $column->index != "") {
// attribute is mapped with one data source
                                            $self = $cell[$column->index];
                                        } else {
// attribute is mapped with a custom value
                                            if ($column->scripting == "") {
                                                $self = $column->default;
                                            }
                                        }
                                        $before = $self;
                                        if ($this->_framework->moduleIsEnabled("Wyomind_MassProductImport")) {
                                            if (isset($column->rule)) {
                                                $self = $this->replacementRules($column->rule, $self);
                                            }
                                            $after = $self;
                                            if ($before != $after) {
                                                $self = "<span class='replacement'>" . __("Replacement Rule = ") . "<i> " . $after . "</i></span>" . "<br><span class='previous'>" . __("Original value = ") . " <i>" . $before . "</i></span>";
                                                if ($column->scripting != "") {
                                                    $self = $after;
                                                }
                                            }
                                        }
                                        if ($column->scripting != "") {
                                            try {
                                                $self = $this->execPhp($column->scripting, $cell, $self, $additionalData);

                                                if ($self === false) {
                                                    $skipped = true;
                                                    $self = "<i class='skipped'> " . __("skip this cell and next cells") . "</i>";
                                                    $data[$counter][] = $self;
                                                    continue;
                                                } elseif ($self === true) {
                                                    $self = "<i class='skipped'> " . __("skip only this cell") . "</i>";
                                                    $data[$counter][] = $self;
                                                    continue;
                                                }
                                            } catch (\Exception $e) {
                                                $rtn['status'] = "error";
                                                $rtn['message'] = __("Error in script for $column->label :") . nl2br(htmlentities($e->getMessage()));
                                                return $rtn;
                                            }
                                            $after = $self;
                                            if ($before != $after) {
                                                if ($before == "") {
                                                    $before = __("null");
                                                }
                                                if ($after == "") {
                                                    $after = __("null");
                                                }
                                                $self = "<span class='dynamic'>" . __("Dynamic value = ") . "<i> " . $after . "</i></span>" . "<br><span class='previous'>" . __("Original value = ") . " <i>" . $before . "</i></span>";
                                            }
                                        }
                                        $data[$counter][] = $self;
                                    }
                                }
                            }
                            /**
                             * MAGENTO EXPORT FILE READER
                             */
                            if ($params['is_magento_export'] == self::IS_MAGENTO_EXPORT_YES) {
                                if (empty($data[$counter][(int)$params["identifier_offset"]]) && !empty($previous)) {
                                    $previous = $this->arrayMerge($previous, $data[$counter]);
                                    $data[$counter - 1] = $previous;
                                    continue;
                                } else {
                                    $previous = $data[$counter];
                                }
                            }
                        } else {
                            $data[$counter] = $cell;
                            /**
                             * MAGENTO EXPORT FILE READER
                             */
                            if ($params['is_magento_export'] == self::IS_MAGENTO_EXPORT_YES) {
                                if (empty($cell[(int)$params["identifier_offset"]]) && !empty($previous)) {
                                    $previous = $this->arrayMerge($previous, $cell);
                                    $data[$counter - 1] = $previous;
                                    continue;
                                } else {
                                    $previous = $cell;
                                }
                            }
                        }


                        $counter++;
                    }

                    // if has header then get the first row
                    if ($params['has_header']) {
                        if (!$isOutput) {
                            $headers = array_shift($data);

                            // Fill in empty headers with corresponding index
                            for ($i = 0; $i < count($headers); $i++) {
                                if ($headers[$i] == '') {
                                    $headers[$i] = (string) $i;
                                }
                            }

                            $length = !empty($headers);
                            for ($i = 0; $i < $length; $i++) {
                                if (trim($headers[$i]) == "") {
                                    $headers[$i] = 'Empty header ' . $i;
                                }
                            }
                        } else {
                            array_shift($data);
                        }
                    } // it has no header row then create the default headers
                    else {
                        if (!$isOutput) {
                            $nbColumns = 0;
                            if (isset($data[1])) {
                                $nbColumns = count($data[1]);
                            }

                            for ($i = 0; $i < $nbColumns; $i++) {
                                if (false === array_key_exists($i, $headers)) {
                                    $headers[$i] = 'Empty header ' . $i;
                                }
                            }
                        }
                    }


                    break;
                case self::JSON:
                    $json = json_decode($driverFile->fileGetContents($file), true);

                    if ($json == null) {
                        $exc = new \Magento\Framework\Exception\LocalizedException(__("Invalid Json file %1.", $params["file_path"]));
                        throw $exc;
                    }
                    $json = (new JSONPath($json))->find($params['xml_xpath_to_product'])->getData();
                    if (empty($json)) {
                        $exc = new \Magento\Framework\Exception\LocalizedException(__("'%1' is not a valid JSON node", $params['xml_xpath_to_product']));
                        throw $exc;
                    }
                    if (isset($json[0][0])) {
                        $json = $json[0];
                    }
                    if (!is_array($json)) {
                        $exc = new \Magento\Framework\Exception\LocalizedException(__("No products were found for `%1`. Please check the JSON Path.", $params['xml_xpath_to_product']));
                        throw $exc;
                    }
                    if ($params["preserve_xml_column_mapping"]) {
                        try {
                            if (!isset($structure)) {
                                $structure = json_decode($params["xml_column_mapping"], true);
                            }
                        } catch (\Exception $e) {
                            $exc = new \Magento\Framework\Exception\LocalizedException(__("Invalid Json string for the JSON structure."));
                            throw $exc;
                        }
                        if (!count($headers) && !$isOutput) {
                            $headers = array_keys($structure);
                        }
                    }


                    $i = 0;
                    foreach ($json as $productModel) {
                        $cell = [];
// automatic XML Structure
                        if ($limit != -1 && $counter > $limit) {
                            break;
                        }


                        if (!$params["preserve_xml_column_mapping"]) {
//use the longest headers rows
                            if (count($headers) < count(array_keys((array)$productModel)) && !$isOutput) {
                                $headers = array_keys((array)$productModel);
                                $headers = array_unique($headers);
                            }
                            $columns = array_keys((array)$productModel);

                            foreach ($columns as $x => $key) {
                                $xmlElement = (array)$productModel[$key];
                                if (count($xmlElement) >= 1) {
                                    $cell[$x] = $productModel[$key];
                                } else {
                                    $cell[$x] = "";
                                }
                                if ($isOutput) {
                                    $cell[$key] = $cell[$x];
                                }
                            }
                        } // user defined XML Structure
                        else {
                            try {
                                if (!isset($structure)) {
                                    $structure = json_decode($params["xml_column_mapping"], true);
                                    if (json_last_error() != JSON_ERROR_NONE) {
                                        $exc = new \Magento\Framework\Exception\LocalizedException(__("Invalid Json string for the JSON structure."));
                                        throw $exc;
                                    }
                                }
                            } catch (\Exception $e) {
                                $exc = new \Magento\Framework\Exception\LocalizedException(__("Invalid Json string for the JSON structure."));
                                throw $exc;
                            }

                            if (!count($headers) && !$isOutput) {
                                $headers = array_keys($structure);
                            }
//                            $product = new \SimpleXMLElement($product->asXML());
                            $x = 0;
                            foreach ($structure as $header => $jsonPath) {
                                $result = (new JSONPath($productModel))->find($jsonPath)->getData();


                                if (isset($result[0])) {
                                    if (is_array($result[0])) {
                                        $cell[$x] = $result;
                                    } else {
                                        $cell[$x] = trim($result[0]);
                                    }
                                } else {
                                    $cell[$x] = "";
                                }


                                if ($isOutput) {
                                    $cell[$header] = $cell[$x];
                                }
                                $x++;
                            }
                        }
                        if (!isset($cell[(int)$params["identifier_offset"]])) {
                            continue;
                        }
                        $rangeCondition = $this->getLineRangeCondition($params['line_filter'], $i, $cell[(int)$params["identifier_offset"]], $cell, false);
                        $i++;
                        if ($rangeCondition == false) {
                            continue;
                        }

                        $additionalData = [];

                        if (!empty(self::ADDITIONAL_ATTR)) {

                            /** @var \Magento\Catalog\Model\Product $productModel */
                            $productModel = $this->productModelFactory->create();
                            $product = $productModel->loadByAttribute($params["identifier"], $cell[(int)$params["identifier_offset"]], self::ADDITIONAL_ATTR);

                            if ($product) {
                                foreach ($product->getData() as $key => $value) {
                                    if (in_array($key, self::ADDITIONAL_ATTR)) {
                                        $additionalData[$key] = $value;
                                    }
                                }
                            }
                        }
                        if ($isOutput) {
                            $skipped = false;
                            $data[$counter] = [];
                            try {
                                $identifier_value = $this->execPhp($params["identifier_script"], $cell, $cell[(int)$params["identifier_offset"]], $additionalData);
                            } catch (\Exception $e) {
                                $rtn['status'] = "error";
                                $rtn['message'] = __("Error in script for $column->label :") . nl2br(htmlentities($e->getMessage()));
                                return $rtn;
                            }
                            if ($identifier_value === false) {
                                $skipped = true;
                                $identifier_value = "<i class='skipped'> " . __("skip this cell and next cells") . "</i>";
                            } elseif ($identifier_value === true) {
                                $identifier_value = "<i class='skipped'> " . __("skip only this cell") . "</i>";
                            }
                            $data[$counter][] = $identifier_value;
                            $cell["identifier"] = $identifier_value;


                            foreach ($mapping as $column) {
                                $self = "";
                                if (isset($column->index) && $column->index != "" && isset($cell[$column->index])) {
                                    //
                                    $cell[$column->source] = $cell[$column->index];
                                } else {
                                    $cell[$column->source] = "";
                                }

                                if ($column->enabled) {
                                    if ($skipped === true) {
                                        $self = "<i class='skipped'> " . __("skipped") . "</i>";
                                        $data[$counter][] = $self;
                                        continue;
                                    }
                                    if (isset($column->index) && $column->index != "" && isset($cell[$column->index])) {
// attribute is mapped with one data source
                                        $self = $cell[$column->index];
                                    } else {
// attribute is mapped with a custom value
                                        if ($column->scripting == "") {
                                            $self = $column->default;
                                        }
                                    }
                                    if ($this->_framework->moduleIsEnabled("Wyomind_MassProductImport")) {
                                        $before = $self;
                                        if (isset($column->rule)) {
                                            $self = $this->replacementRules($column->rule, $self);
                                        }
                                        $after = $self;
                                        if ($before != $after) {
                                            $self = "<span class='replacement'>" . __("Replacement Rule = ") . "<i> " . $after . "</i></span>" . "<br><span class='previous'>" . __("Original value = ") . " <i>" . $before . "</i></span>";
                                        }
                                    }
                                    if ($column->scripting != "") {
                                        $before = $self;

                                        try {
                                            $self = $this->execPhp($column->scripting, $cell, $self, $additionalData);
                                            if ($self === false) {
                                                $skipped = true;
                                                $self = "<i class='skipped'> " . __("skip this cell and next cells") . "</i>";
                                                $data[$counter][] = $self;
                                                continue;
                                            } elseif ($self === true) {
                                                $self = "<i class='skipped'> " . __("skip only this cell") . "</i>";
                                                $data[$counter][] = $self;
                                                continue;
                                            }
                                        } catch (\Exception $e) {
                                            $rtn['status'] = "error";
                                            $rtn['message'] = __("Error in script for $column->label :") . nl2br(htmlentities($e->getMessage()));
                                            return $rtn;
                                        }
                                        $after = $self;
                                        if ($before != $after) {
                                            if ($before == "") {
                                                $before = __("null");
                                            }
                                            if ($after == "") {
                                                $after = __("null");
                                            }
                                            $self = "<span class='dynamic'>" . __("Dynamic value = ") . "<i> " . $after . "</i></span>" . "<br><span class='previous'>" . __("Original value = ") . " <i>" . $before . "</i></span>";
                                        }
                                    }
                                    $data[$counter][] = $self;
                                }
                            }
                        } else {
                            $data[$counter] = $cell;
                        }


                        $counter++;
                    }
                    break;
                case self::XML:
                    $search = ["g:", "ss:", "x:", "xs:", "xmlns:", "i:", "xmlns:msdata", "msdata:", "xmlns"];
                    $replace = ["g_", "ss_", "x_", "xs_", "xmlns_", "i_", "xmlns_msdata", "msdata_", "xmlnamespace"];
                    $xml = (new \SimpleXMLElement(str_replace($search, $replace, $driverFile->fileGetContents($file)), LIBXML_NOCDATA))->xpath($params['xml_xpath_to_product']);
                    if (!count($xml)) {
                        if ($params["preserve_xml_column_mapping"]) {
                            try {
                                if (!isset($structure)) {
                                    $structure = json_decode($params["xml_column_mapping"], true);
                                }
                            } catch (\Exception $e) {
                                $exc = new \Magento\Framework\Exception\LocalizedException(__("Invalid Json string for the XML structure."));
                                throw $exc;
                            }
                            if (!count($headers) && !$isOutput) {
                                $headers = array_keys($structure);
                            }
                        } else {
                            $exc = new \Magento\Framework\Exception\LocalizedException(__("No products were found for `%1`. Please check the XPath.", $params['xml_xpath_to_product']));
                            throw $exc;
                        }
                    }

                    $i = 0;
                    foreach ($xml as $productModel) {
                        $cell = [];
// automatic XML Structure
                        if ($limit != -1 && $counter > $limit) {
                            break;
                        }


                        if (!$params["preserve_xml_column_mapping"]) {
//use the longest headers rows
                            if (count($headers) < count(array_keys((array)$productModel)) && !$isOutput) {
                                $headers = array_keys((array)$productModel);
                                $headers = array_unique($headers);
                            }
                            $columns = array_keys((array)$productModel);

                            foreach ($columns as $x => $key) {
                                $xmlElement = (array)$productModel->$key;
                                if (count($xmlElement) >= 1) {
                                    if (trim($productModel->$key->__toString()) != "") {
                                        $cell[$x] = $productModel->$key->__toString();
                                    } else {
                                        $cell[$x] = $productModel->$key;
                                    }
                                } else {
                                    $cell[$x] = "";
                                }
                                if ($isOutput) {
                                    $cell[$key] = $cell[$x];
                                }
                            }
                        } // user defined XML Structure
                        else {
                            try {
                                if (!isset($structure)) {
                                    $structure = json_decode($params["xml_column_mapping"], true);
                                    if (json_last_error() != JSON_ERROR_NONE) {
                                        $exc = new \Magento\Framework\Exception\LocalizedException(__("Invalid Json string for the XML structure."));
                                        throw $exc;
                                    }
                                }
                            } catch (\Exception $e) {
                                $exc = new \Magento\Framework\Exception\LocalizedException(__("Invalid Json string for the XML structure."));
                                throw $exc;
                            }

                            if (!count($headers) && !$isOutput) {
                                $headers = array_keys($structure);
                            }
//                            $product = new \SimpleXMLElement($product->asXML());
                            $x = 0;
                            foreach ($structure as $header => $xpath) {
                                $result = $productModel->xpath($xpath);

                                if (isset($result[0])) {
                                    $cell[$x] = trim($result[0]->__toString());

                                    if (count($result[0]) != 0) {
                                        $cell[$x] = $result[0];
                                    }
                                    $splitted = explode("/", $xpath);
                                    $last = array_pop($splitted);
                                    if (substr($last, 0, 1) == "@") {
                                        $cell[$x] = (string)$result[0][substr($last, 1)];
                                    }
                                } else {
                                    $cell[$x] = "";
                                }


                                if ($isOutput) {
                                    $cell[$header] = $cell[$x];
                                }
                                $x++;
                            }
                        }

                        if (!isset($cell[(int)$params["identifier_offset"]])) {
                            continue;
                        }
                        $rangeCondition = $this->getLineRangeCondition($params['line_filter'], $i, $cell[(int)$params["identifier_offset"]], $cell, false);
                        $i++;
                        if ($rangeCondition == false) {
                            continue;
                        }
                        $additionalData = [];

                        if (!empty(self::ADDITIONAL_ATTR)) {

                            /** @var \Magento\Catalog\Model\Product $productModel */
                            $productModel = $this->productModelFactory->create();
                            $product = $productModel->loadByAttribute($params["identifier"], $cell[(int)$params["identifier_offset"]], self::ADDITIONAL_ATTR);

                            if ($product) {
                                foreach ($product->getData() as $key => $value) {
                                    if (in_array($key, self::ADDITIONAL_ATTR)) {
                                        $additionalData[$key] = $value;
                                    }
                                }
                            }
                        }
                        if ($isOutput) {
                            $skipped = false;
                            $data[$counter] = [];
                            try {
                                $identifier_value = $this->execPhp($params["identifier_script"], $cell, $cell[(int)$params["identifier_offset"]], $additionalData);
                            } catch (\Exception $e) {
                                $rtn['status'] = "error";
                                $rtn['message'] = __("Error in script for $column->label :") . nl2br(htmlentities($e->getMessage()));
                                return $rtn;
                            }
                            if ($identifier_value === false) {
                                $skipped = true;
                                $identifier_value = "<i class='skipped'> " . __("skip this cell and next cells") . "</i>";
                            } elseif ($identifier_value === true) {
                                $identifier_value = "<i class='skipped'> " . __("skip only this cell") . "</i>";
                            }
                            $data[$counter][] = $identifier_value;
                            $cell["identifier"] = $identifier_value;


                            foreach ($mapping as $column) {
                                $self = "";
                                if (isset($column->index) && $column->index != "" && isset($cell[$column->index])) {
                                    //
                                    $cell[$column->source] = $cell[$column->index];
                                } else {
                                    $cell[$column->source] = "";
                                }

                                if ($column->enabled) {
                                    if ($skipped === true) {
                                        $self = "<i class='skipped'> " . __("skipped") . "</i>";
                                        $data[$counter][] = $self;
                                        continue;
                                    }
                                    if (isset($column->index) && $column->index != "" && isset($cell[$column->index])) {
// attribute is mapped with one data source
                                        $self = $cell[$column->index];
                                    } else {
// attribute is mapped with a custom value
                                        if ($column->scripting == "") {
                                            $self = $column->default;
                                        }
                                    }
                                    if ($this->_framework->moduleIsEnabled("Wyomind_MassProductImport")) {
                                        $before = $self;
                                        if (isset($column->rule)) {
                                            $self = $this->replacementRules($column->rule, $self);
                                        }
                                        $after = $self;
                                        if ($before != $after) {
                                            $self = "<span class='replacement'>" . __("Replacement Rule = ") . "<i> " . $after . "</i></span>" . "<br><span class='previous'>" . __("Original value = ") . " <i>" . $before . "</i></span>";
                                        }
                                    }
                                    if ($column->scripting != "") {
                                        $before = $self;

                                        try {
                                            $self = $this->execPhp($column->scripting, $cell, $self, $additionalData);
                                            if ($self === false) {
                                                $skipped = true;
                                                $self = "<i class='skipped'> " . __("skip this cell and next cells") . "</i>";
                                                $data[$counter][] = $self;
                                                continue;
                                            } elseif ($self === true) {
                                                $self = "<i class='skipped'> " . __("skip only this cell") . "</i>";
                                                $data[$counter][] = $self;
                                                continue;
                                            }
                                        } catch (\Exception $e) {
                                            $rtn['status'] = "error";
                                            $rtn['message'] = __("Error in script for $column->label :") . nl2br(htmlentities($e->getMessage()));
                                            return $rtn;
                                        }
                                        $after = $self;
                                        if ($before != $after) {
                                            if ($before == "") {
                                                $before = __("null");
                                            }
                                            if ($after == "") {
                                                $after = __("null");
                                            }
                                            $self = "<span class='dynamic'>" . __("Dynamic value = ") . "<i> " . $after . "</i></span>" . "<br><span class='previous'>" . __("Original value = ") . " <i>" . $before . "</i></span>";
                                        }
                                    }
                                    $data[$counter][] = $self;
                                }
                            }
                        } else {
                            $data[$counter] = $cell;
                        }

                        $counter++;
                    }

                    break;
            }

            return ['error' => "false", 'header' => $headers, 'tag' => $tags, 'color' => $colors, 'data' => array_values($data)];
        } catch (\Throwable $e) {
            $exc = new \Magento\Framework\Exception\LocalizedException(__("%1", $e->getMessage()));
            throw $exc;
        }
    }

    /**
     * Line filter
     * @param string $parameters
     * @param int $lineNumber
     * @param $identifier string
     * @param array $cell
     * @return boolean
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getLineRangeCondition(
        $parameters,
        $lineNumber,
        $identifier,
        $cell = [],
        $hasHeader = true
    ) {
    
        $upTo = false;
        $range = false;
        $equal = false;
        $pregMatch = false;
        $rangeCondition = true;


        if ($parameters) {
            $rtn = $this->execPhp($parameters, $cell);
            if ($rtn === false || $rtn === true) {
                return $rtn;
            }
            $regExp = "/#([^#]+)#/";
            preg_match_all($regExp, $parameters, $matches);
            $identifiers = $matches[0];
            foreach ($matches[0] as $exp) {
                $parameters = str_replace($exp, "", $parameters);
            }

            $parameters = array_merge(array_filter(explode(',', $parameters)), $identifiers);


            foreach ($parameters as $value) {
                if (preg_match($regExp, $value) && (!$hasHeader || $lineNumber > 0)) {
                    if (false == $pregMatch) {
                        $pregMatch = preg_match($value, $identifier);
                    }
                } elseif (false !== strpos($value, '+')) {
                    $value = str_replace(' ', '', $value);
                    if (false === $upTo) {
// From line - to the end (e.g 2+)
                        $upTo = $lineNumber >= $value;
                    }
                } elseif (false !== strpos($value, '-')) {
                    $value = str_replace(' ', '', $value);

                    if (false === $range) {
// From - To line (e.g 15-20)
                        $fromTo = explode('-', $value);
                        $from = $lineNumber >= $fromTo[0];
                        $to = $lineNumber <= $fromTo[1];
                        $range = $from && $to;
                    }
                } else {
                    $value = str_replace(' ', '', $value);
                    if (false === $equal) {
// One line
                        $equal = $lineNumber == $value;
                    }
                }
            }
            //var_dump($equal, $range, $upTo);

            $rangeCondition = $equal || $range || $upTo || $pregMatch;
        }

        return $rangeCondition;
    }

    /**
     * @param string $script
     * @param null $cell
     * @param null $self
     * @return mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execPhp($script, &$cell = null, $self = null, $additionalData = [])
    {
        // Restore all break lines in the php code
        $script = str_replace("__LINE_BREAK__", "\n", $script);
        // Transform any call to $this->skip, to a return $this->skipSomething() instruction
        $script = str_replace('$this->skip', 'return $this->skip', $script);
        if (preg_match("#^<\?(php)?(.*)(\?>)?$#mi", $script)) {
            try {
                return eval("?>" . $script . " return \$self;");
            } catch (\Throwable $e) {
                $exc = new \Magento\Framework\Exception\LocalizedException(__("\nError in:\n %1 \n\nError message:n %2 \n\n", $script, $e->getMessage()));
                throw $exc;
            }
        }

        return $self;
    }

    /**
     * @param array $array1
     * @param array $array2
     * @return array
     */
    function arrayMerge($array1 = [], $array2 = [])
    {
        foreach ($array1 as $key => $value) {
            if (isset($array2[$key]) && $array2[$key] != "") {
                $array1[$key] .= self::FIELD_IMPLODE . $array2[$key];
            }
        }
        return $array1;
    }

    /**
     * @return array
     */
    public function getJsonAttributes()
    {
        if (is_null($this->jsonAttributes)) {
            $dropdown = [];

            /* Store views */
            $stores = [];
            $w = 0;
            $g = 0;
            $s = 0;

            $websites = $this->_storeManager->getWebsites();
            $stores["label"] = __("Default value");
            $stores["value"] = "0";

            foreach ($websites as $website) {
                $stores["children"][$w]["label"] = $website->getName();
                $g = 0;
                $storegroups = $website->getGroupCollection();
                foreach ($storegroups as $storegroup) {
                    $stores["children"][$w]["children"][$g]["label"] = $storegroup->getName();
                    $s = 0;
                    $storeviews = $storegroup->getStoreCollection();
                    foreach ($storeviews as $storeview) {
                        $stores["children"][$w]["children"][$g]["children"][$s]["label"] = $storeview->getName();
                        $stores["children"][$w]["children"][$g]["children"][$s]["value"] = $storeview->getStoreId();
                        $stores["children"][$w]["children"][$g]["children"][$s]["code"] = $storeview->getCode();
                        $s++;
                    }
                    $g++;
                }
                $w++;
            }

            $dropdown["storeviews"] = $stores;

            foreach ($this::MODULES as $module) {
                $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
                $resource = $objectManager->get("\Wyomind\\" . $this->module . "\Model\ResourceModel\Type\\" . $module);
                $options = $resource->getDropdown($this);
                $dropdown = array_merge($dropdown, $options);
            }
            $this->jsonAttributes = $dropdown;
        }
        return $this->jsonAttributes;
    }

    /**
     * @return array
     */
    public function getFieldDelimiters()
    {
        return [
            ';' => ';',
            ', ' => ', ',
            '|' => '|',
            "\t" => '\tab',
        ];
    }

    /**
     * @return array
     */
    public function getFieldEnclosures()
    {
        return [
            "none" => 'none',
            '"' => '"',
            '\'' => '\'',
        ];
    }

    /**
     * @return array
     */
    public function getProductIdentifiers()
    {
        $typeCode = \Magento\Catalog\Api\Data\ProductAttributeInterface::ENTITY_TYPE_CODE;
        $searchCriteria = $this->_objectManager->create('\Magento\Framework\Api\SearchCriteria');
        $attributeList = $this->_attributeRepository->getList($typeCode, $searchCriteria)->getItems();

        $uId = [];
        $uId[] = ["is_unique" => 1, "label" => "ID", "value" => "entity_id"];
        foreach ($attributeList as $attribute) {
            if ($attribute->getIsUnique()) {
                $uId[] = ["is_unique" => $attribute->getIsUnique(), "label" => $attribute->getDefaultFrontendLabel(), "value" => $attribute->getAttributeCode()];
            }
        }

        return $uId;
    }

    /**
     * @return array
     */
    public function getBoolean()
    {
        return [
            1 => (string)("Enabled"),
            0 => (string)("Disabled")
        ];
    }

    /**
     * @return array
     */
    public function getBackorders()
    {
        return [
            0 => (string)("No Backorders"),
            1 => (string)("Backorders allowed"),
            2 => (string)("Backorders allowed and notify customer")
        ];
    }

    /**
     * @param $fields
     * @param $value
     * @param null $uniqueParameter
     * @param bool $multipleGroup
     * @return mixed
     */
    function prepareFields($fields, $value, $uniqueParameter = null, $multipleGroup = false)
    {
        $data = [];

        $groups = $this->parseGroups($value, $multipleGroup);
        if ($multipleGroup) {
            return $groups;
        }
        if (isset($groups)) {
            foreach ($groups as $k => $group) {
                $parameters = $this->parseParameters($group);

                foreach ($parameters["variable"] as $key => $value) {
                    if ($uniqueParameter && count($parameters["variable"]) == 1 && $parameters['variable'][$key] == null) {
                        $data[$uniqueParameter] = $parameters["value"][$key];
                    } else {
                        $data[strtolower($parameters["variable"][$key])] = $parameters["value"][$key];
                    }
                }

                break;
            }
        }


        $rtn = [];
        foreach ($fields as $field => $default) {
            if (!isset($data[$field])) {
                $rtn[$field] = $default;
            } else {
                $rtn[$field] = $data[$field];
            }

            if ($field == "price") {
                $rtn["price_type"] = "'fixed'";
                if (stristr($rtn["price"], "%")) {
                    $rtn["price_type"] = "'percent'";
                }
                $rtn["price"] = str_replace(["%", ","], null, $rtn["price"]);
            }

            if ($rtn[$field] instanceof \Zend_Db_Expr) {
                $rtn[$field] = $rtn[$field]->__toString();
            } else {
                $rtn[$field] = $this->sanitizeField($rtn[$field]);
            }
        }


        return $rtn;
    }

    /**
     * @param $value
     * @param bool $multipleGroup
     * @return mixed
     */
    public function parseGroups($value, $multipleGroup = false)
    {
        preg_match_all("#(?<title>[^\[]*)(?<groups>(\[[^\]]*)\]+)#m", $value, $matches);

        if (isset($matches["groups"])) {
            if (!$multipleGroup && isset($matches["groups"][0])) {
                //[label=option label 1|sku=option sku 1|position=99 | price=13.00][label=option label 2|sku=option sku 1|position=99 | price=13.12]
                preg_match_all("#\[(?<group>[^\]]*)\]#m", $matches["groups"][0], $groups);

                return $groups["group"];
            } else {
                return $matches["groups"];
            }
        }
    }

    /**
     * @param $value
     * @return mixed
     */
    public function parseParameters($value)
    {
        // variable=value | variable=value

        preg_match_all("#((?:(?:(?:(?<variable>[^=]*)=)?(?<value>[^|\]]+)+)\|?))#", $value, $matches);
        return $matches;
    }

    /**
     * @param $value
     * @return string
     */
    function sanitizeField($value)
    {
        return "'" . str_replace(["\'", "'"], ["'", "''"], $value) . "'";
    }

    /**
     * @param $value
     * @return string
     */
    function unSanitizeField($value)
    {
        return substr(str_replace("''", "'", $value), 1, strlen($value) - 2);

    }

    /**
     * @param $attributes
     * @param null $prefix
     * @return array
     */
    public function prepareStorewiewsParameters($attributes, $prefix = null)
    {
        $list = [];
        foreach ($this->getStoreviews($attributes) as $storeview) {
            $code = (!empty($prefix)) ? "_" . $storeview["code"] : $storeview["code"];
            if (!is_array($prefix)) {
                $list[$prefix . $code] = null;
            } else {
                foreach ($prefix as $s) {
                    $list[$s . $code] = null;
                }
            }
        }
        return $list;
    }

    /**
     * @param $attributes
     * @return array
     */
    public function getStoreviews($attributes)
    {
        $storeviews = [];

        foreach ($attributes["storeviews"]["children"] as $website) {
            if (isset($website["children"])) {
                foreach ($website["children"] as $store) {
                    if (isset($store["children"])) {
                        foreach ($store["children"] as $storeview) {
                            $storeviews[] = $storeview;
                        }
                    }
                }
            }
        }
        return $storeviews;
    }

    /**
     * @param $value
     * @return mixed
     * @throws \Exception
     */
    public function getValue($value)
    {
        try {
            //Custom option Field[.....]
            preg_match("#(?<title>[^\[]*)(?<groups>(\[[^\]]*\])+)?#", $value, $matches);
            return trim($matches["title"]);
        } catch (\Exception $message) {
            throw new \Exception(__("'%1' is not a valid syntax", [$value]));
        }
    }

    /**
     * Is MSI enabled
     * @return bool
     */
    public function isMsiEnabled()
    {

        return version_compare($this->_framework->getMagentoVersion(), "2.3.0", ">=") && $this->_framework->moduleIsEnabled("Magento_InventorySales");
    }

    /**
     * @param $input
     * @return string
     */
    function fromCamelCase($input)
    {
        preg_match_all('!([A-Z][A-Z0-9]*(?=$|[A-Z][a-z0-9])|[A-Za-z][a-z0-9]+)!', $input, $matches);
        $ret = $matches[0];
        foreach ($ret as &$match) {
            $match = ucfirst($match);
        }
        return implode(' ', $ret);
    }

    /**
     * @return boolean
     */
    protected function skipCell()
    {
        return true;
    }

    /**
     * @return boolean
     */
    protected function skip()
    {
        return $this->skipRow();
    }

    /**
     * @return boolean
     */
    protected function skipRow()
    {
        return false;
    }
}
