<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Helper;

/**
 * Class File
 *
 * @package Amasty\Orderexport\Helper
 */
class File extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @type
     */
    protected $profile;
    /**
     * @type
     */
    protected $filePath;
    /**
     * @type
     */
    protected $fileName;
    /**
     * @type
     */
    protected $fileHandler;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    /**
     * Store manager
     *
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * @var  \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var  \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var  \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @var \Magento\Cms\Model\Page
     */
    protected $_cmsPage;

    /**
     * @var \Magento\Cms\Model\Block
     */
    protected $_cmsBlock;

    /**
     * @var \Magento\Catalog\Model\Category
     */
    protected $_category;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;

    /**
     * Action flag
     *
     * @var \Magento\Framework\App\ActionFlag
     */
    protected $_actionFlag;

    /**
     * Order Model
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_modelOrder;

    /**
     * Order Invoice Model
     *
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $_modelOrderInvoice;

    /**
     * Order Invoice Model
     *
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_modelOrderConfig;

    /**
     * @var \Magento\Framework\Filesystem\Directory\Write
     */
    protected $_directory;


    /**
     * @param \Magento\Framework\Registry                                  $registry
     * @param \Magento\Framework\View\Result\PageFactory                   $resultPageFactory
     * @param \Magento\Framework\ObjectManagerInterface                    $objectManager
     * @param \Magento\Framework\Message\ManagerInterface                  $messageManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface         $localeDate
     * @param \Magento\Framework\Filesystem                                $filesystem
     * @param \Magento\Store\Model\StoreManagerInterface                   $storeManager
     * @param \Magento\Framework\View\LayoutFactory                        $layoutFactory
     * @param \Magento\ConfigurableProduct\Model\Product\Type\Configurable $catalogProductTypeConfigurable
     * @param \Magento\Framework\App\ActionFlag                            $actionFlag
     * @param \Magento\Catalog\Model\Product                               $product
     * @param \Magento\Framework\App\Helper\Context                        $context
     * @param \Magento\Cms\Model\Page                                      $cmsPage
     * @param \Magento\Sales\Model\Order                                   $modelOrder
     * @param \Magento\Sales\Model\Order\Invoice                           $_modelOrderInvoice
     * @param \Magento\Sales\Model\Order\Config                            $_modelOrderConfig
     * @param \Magento\Cms\Model\Block                                     $cmsBlock
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\View\LayoutFactory $layoutFactory,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable $catalogProductTypeConfigurable,
        \Magento\Framework\App\ActionFlag $actionFlag,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Cms\Model\Page $cmsPage,
        \Magento\Sales\Model\Order $modelOrder,
        \Magento\Sales\Model\Order\Invoice $_modelOrderInvoice,
        \Magento\Sales\Model\Order\Config $_modelOrderConfig,
        \Magento\Cms\Model\Block $cmsBlock
    ) {
        parent::__construct($context);
        $this->_registry                = $registry;
        $this->_resultPageFactory       = $resultPageFactory;
        $this->_objectManager           = $objectManager;
        $this->_messageManager          = $messageManager;
        $this->_localeDate              = $localeDate;
        $this->_filesystem              = $filesystem;
        $this->_storeManager            = $storeManager;
        $this->layoutFactory            = $layoutFactory;
        $this->_productTypeConfigurable = $catalogProductTypeConfigurable;
        $this->_directory               = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::ROOT);
        $this->_product                 = $product;
        $this->_actionFlag              = $actionFlag;
        $this->_cmsPage                 = $cmsPage;
        $this->_cmsBlock                = $cmsBlock;
        $this->_modelOrder              = $modelOrder;
        $this->_modelOrderInvoice       = $_modelOrderInvoice;
        $this->_modelOrderConfig        = $_modelOrderConfig;
    }

    /**
     * @param $file
     * @param $destination
     *
     * @return bool
     * @throws \Exception
     */
    public function zip($file, $destination)
    {
        if (class_exists('ZipArchive')) {
            $zip = new \ZipArchive;
            $res = $zip->open($destination, \ZIPARCHIVE::CREATE);

            if ($res !== true) {
                $e = new \Exception("Unable to create zip: '$res'.");
                $this->_messageManager->addException($e, "Unable to create zip: '$res'.", $res);
            }

            $fileName = substr($file, strrpos($file, '/') + 1);
            $zip->addFile($file, $fileName);
            $zip->close();

            return true;
        }

        return false;
    }

    /**
     * @param \Amasty\Orderexport\Model\Profiles $profile
     *
     * @return bool
     */
    public function openFileForWrite(&$profile)
    {
        $profile->setData('path', rtrim($profile->getData('path'), '/'));

        $date = $profile->getData('post_date_format') ? date($profile->getData('post_date_format')) : date('_d_m_Y_H_i_s');

        // add Date to folder (==2) or to filename (==1)
        if ($profile->getData('export_add_timestamp') == 2) {
            $profile->setData('path', $profile->getData('path') . '/' . $date);
        }
        if ($profile->getData('export_add_timestamp') == 1) {
            $profile->setData('filename', $profile->getData('filename') . $date);
        }

        // define full file path
        $filePath   = $profile->getData('path') .'/' . $profile->getData('filename') . '.' . ($profile->getData('format') ? 'xml' : 'csv');
        $exportFile = '/'.trim($this->_directory->getAbsolutePath(), '/') . '/' . $filePath;

        // make directory for export if it doesn't exist
        $dirname = dirname($exportFile);
        if (!is_dir($dirname)) {
            mkdir($dirname, 0755, true);
        }

        // save handler for opened file
        $this->fileHandler = fopen($exportFile, 'w+');
        $this->filePath    = $exportFile;

        // save data for opened file in Profile
        $profile->setData('file_path', $filePath);
        $profile->setData('file_path_full', $exportFile);

        return (bool)$this->fileHandler;
    }

    /**
     * @return bool
     */
    public function openMsXmlFile()
    {
        $content
             = '<?xml version="1.0" encoding="UTF-8"?>
                            <?mso-application progid="Excel.Sheet"?>
                            <Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:x="urn:schemas-microsoft-com:office:excel" xmlns:x2="http://schemas.microsoft.com/office/excel/2003/xml" xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet" xmlns:o="urn:schemas-microsoft-com:office:office" xmlns:html="http://www.w3.org/TR/REC-html40" xmlns:c="urn:schemas-microsoft-com:office:component:spreadsheet">
                                  <OfficeDocumentSettings xmlns="urn:schemas-microsoft-com:office:office"></OfficeDocumentSettings>
                                  <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel"></ExcelWorkbook>
                                  <Worksheet ss:Name="Sheet 1">
                                        <Table>';
        $res = $this->writeToFile($content);

        return (bool)$res;
    }

    /**
     * @return bool
     */
    public function openXmlFile($xmlMainTag)
    {
        $content
            = '<?xml version="1.0" encoding="UTF-8"?><' . $xmlMainTag . '>';
        $res = $this->writeToFile($content);

        return (bool)$res;
    }

    /**
     * @param string $content
     *
     * @return bool
     */
    public function writeToFile($content)
    {
        $res = fwrite($this->fileHandler, $content, mb_strlen($content));

        return (bool)$res;
    }

    protected function _prepareFields(&$fields, $arrayDelim = ',')
    {
        foreach ($fields as $key => $val) {
            if (is_array($val)) {
                $toImplode = [];
                foreach ($val as $el) {
                    $toImplode[] = str_replace($arrayDelim, '', $el);
                }
                $fields[$key] = implode($arrayDelim, $toImplode);
            } elseif ($this->isDateValid($val)) {
                $date = $this->_localeDate->date(new \DateTime($val));
                $fields[$key] = $date->format('Y-m-d H:i:s');
            }
        }
    }

    /**
     * Validate date
     *
     * @param string $date
     * @param string $format
     *
     * @return bool
     */
    protected function isDateValid($date, $format = 'Y-m-d H:i:s')
    {
        $d = \DateTime::createFromFormat($format, $date);

        return $d && $d->format($format) == $date;
    }

    /**
     * @param $fields
     * @param $delim
     * @param $encl
     * @param string $arrayDelim
     * @return bool
     */
    public function writeCSV($fields, $delim, $encl, $arrayDelim = ',')
    {
        $this->_prepareFields($fields, $arrayDelim);

        $res = fputcsv($this->fileHandler, array_values($fields), $delim, $encl);

        return (bool)$res;
    }

    /**
     * @param $fields
     * @param string $arrayDelim
     * @return bool
     */
    public function writeMsXML($fields, $arrayDelim = ',')
    {
        $this->_prepareFields($fields, $arrayDelim);

        $line = '<Row><Cell><Data ss:Type="String">'
                . implode('</Data></Cell><Cell><Data ss:Type="String">', $fields) .
                '</Data></Cell></Row>';
        $line = str_replace(["\n", "\r"], '', $line);
        $line .= PHP_EOL;

        $res = fwrite($this->fileHandler, $line);

        return (bool)$res;
    }

    /**
     * @param $fields
     * @param $xmlOrderTag
     * @param $xmlOrderItemsTag
     * @param $xmlOrderItemTag
     * @return bool
     */
    public function writeXML($fields, $xmlOrderTag, $xmlOrderItemsTag, $xmlOrderItemTag)
    {
        $orderItemsXml = [];
        $xml = ['<' . $xmlOrderTag . '>'];

        foreach ($fields as $name => $val) {
            if (!is_array($val)) {
                $xml[] = '<' . $name . '><![CDATA[' . $val . ']]></' . $name . '>';
            } else {
                foreach ($val as $orderItemId => $orderItemValue) {
                    if (!array_key_exists($orderItemId, $orderItemsXml)) {
                        $orderItemsXml[$orderItemId] = [];

                    }
                    $orderItemsXml[$orderItemId][] = '<' . $name . '><![CDATA[' . $orderItemValue . ']]></' . $name . '>';
                }
            }
        }

        if (count($orderItemsXml) > 0) {
            $xml[] = '<' . $xmlOrderItemsTag . '>';
            foreach ($orderItemsXml as $orderItemXml) {
                $xml[] = '<' . $xmlOrderItemTag . '>';
                $xml[] = implode('', $orderItemXml);
                $xml[] = '</' . $xmlOrderItemTag . '>';
            }
            $xml[] = '</' . $xmlOrderItemsTag . '>';
        }

        $xml[] = '</' . $xmlOrderTag . '>';

        $res = fwrite($this->fileHandler, implode('', $xml));

        return (bool)$res;
    }

    /**
     * @return bool
     */
    public function closeMsXmlFile()
    {
        $content
             = '              </Table>
                              </Worksheet>
                        </Workbook>';
        $res = $this->writeToFile($content);

        return (bool)$res;
    }

    /**
     * @return bool
     */
    public function closeXmlFile($xmlMainTag)
    {
        $content = '</' . $xmlMainTag . '>';
        $res = $this->writeToFile($content);

        return (bool)$res;
    }
}
