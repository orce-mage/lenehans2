<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2020 Amasty (https://www.amasty.com)
 * @package Amasty_Orderexport
 */


namespace Amasty\Orderexport\Helper;

use Amasty\Base\Model\Serializer;
use Amasty\Orderexport\Model\Profiles;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Model\Product;
use Magento\Cms\Model\Block;
use Magento\Cms\Model\Page;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\ActionFlag;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\Write;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\View\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\Order\Invoice;
use Magento\Store\Model\StoreManagerInterface;

class Export extends AbstractHelper
{
    const TYPE_CSV = '0';
    const TYPE_XML = '2';
    const TYPE_MS_XML = '1';

    /**
     * @var Write
     */
    protected $directory;

    /**
     * @var TimezoneInterface
     */
    protected $localeDate;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var  PageFactory
     */
    protected $resultPageFactory;

    /**
     * @var ObjectManagerInterface
     */
    protected $objectManager;

    /**
     * @var  ManagerInterface
     */
    protected $messageManager;

    /**
     * @var Filesystem
     */
    protected $filesystem;

    /**
     * @var  Product
     */
    protected $product;

    /**
     * @var Page
     */
    protected $cmsPage;

    /**
     * @var Block
     */
    protected $cmsBlock;

    /**
     * @var Category
     */
    protected $category;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var ActionFlag
     */
    protected $actionFlag;

    /**
     * @var Order
     */
    protected $modelOrder;

    /**
     * @var Invoice
     */
    protected $modelOrderInvoice;

    /**
     * @var Config
     */
    protected $modelOrderConfig;

    /**
     * @type mixed
     */
    private $fileHandler;

    /**
     * @type mixed
     */
    private $filePath;

    /**
     * @type mixed
     */
    private $attachmentFilePath;

    /**
     * @var Profiles
     */
    private $profile;

    /**
     * @var Uploader
     */
    private $helperUploader;

    /**
     * @var File
     */
    private $helperFile;

    /**
     * @var Filter
     */
    protected $helperFilter;

    /**
     * @var array
     */
    protected $mapping;

    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * Export constructor.
     * @param Registry $registry
     * @param PageFactory $resultPageFactory
     * @param ObjectManagerInterface $objectManager
     * @param ManagerInterface $messageManager
     * @param TimezoneInterface $localeDate
     * @param StoreManagerInterface $storeManager
     * @param LayoutFactory $layoutFactory
     * @param Configurable $catalogProductTypeConfigurable
     * @param ActionFlag $actionFlag
     * @param Product $product
     * @param Context $context
     * @param Page $cmsPage
     * @param Order $modelOrder
     * @param Filesystem $filesystem
     * @param Invoice $modelOrderInvoice
     * @param Config $modelOrderConfig
     * @param Block $cmsBlock
     * @param File $helperFile
     * @param Uploader $helperUploader
     * @param Filter $helperFilter
     * @param Serializer $serializer
     * @throws FileSystemException
     */
    public function __construct(
        Registry $registry,
        PageFactory $resultPageFactory,
        ObjectManagerInterface $objectManager,
        ManagerInterface $messageManager,
        TimezoneInterface $localeDate,
        StoreManagerInterface $storeManager,
        LayoutFactory $layoutFactory,
        Configurable $catalogProductTypeConfigurable,
        ActionFlag $actionFlag,
        Product $product,
        Context $context,
        Page $cmsPage,
        Order $modelOrder,
        Filesystem $filesystem,
        Invoice $modelOrderInvoice,
        Config $modelOrderConfig,
        Block $cmsBlock,
        File $helperFile,
        Uploader $helperUploader,
        Filter $helperFilter,
        Serializer $serializer
    ) {
        parent::__construct($context);
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->registry = $registry;
        $this->resultPageFactory = $resultPageFactory;
        $this->objectManager = $objectManager;
        $this->messageManager = $messageManager;
        $this->localeDate = $localeDate;
        $this->filesystem = $filesystem;
        $this->storeManager = $storeManager;
        $this->layoutFactory = $layoutFactory;
        $this->productTypeConfigurable = $catalogProductTypeConfigurable;
        $this->product = $product;
        $this->actionFlag = $actionFlag;
        $this->cmsPage = $cmsPage;
        $this->cmsBlock = $cmsBlock;
        $this->modelOrder = $modelOrder;
        $this->modelOrderInvoice = $modelOrderInvoice;
        $this->modelOrderConfig = $modelOrderConfig;
        $this->helperFile = $helperFile;
        $this->helperUploader = $helperUploader;
        $this->helperFilter = $helperFilter;
        $this->serializer = $serializer;
    }

    /**
     * @param Profiles $profile
     * @return array|mixed
     */
    protected function getMapping(Profiles $profile)
    {
        if ($this->mapping === null) {
            $this->mapping = $profile->getData('field_mapping')
                ? $this->serializer->unserialize($profile->getData('field_mapping'))
                : [];
        }

        return $this->mapping;
    }

    /**
     * @param array $mapping
     * @param array $orderData
     * @param array $orderItemsData
     * @return array
     */
    protected function prepareOrderBasedRows(
        array $mapping,
        array $orderData,
        array $orderItemsData
    ) {
        return [$this->prepareOrderBasedFields($mapping, $orderData, $orderItemsData)];
    }

    /**
     * @param array $mapping
     * @param array $orderData
     * @param array $orderItemsData
     * @return array
     */
    protected function prepareOrderItemBasedRows(
        array $mapping,
        array $orderData,
        array $orderItemsData
    ) {
        $rows = [];

        foreach ($orderItemsData as $orderItemId => $orderItemData) {
            $row = [];

            if (count($mapping) > 0) {
                foreach ($mapping as $config) {
                    if (array_key_exists('value', $config)) {
                        $column = $config['value'];

                        if (array_key_exists($column, $orderItemData)) {
                            $row[$column] = $this->prepareValue($orderItemData[$column]);
                        } elseif (array_key_exists($column, $orderData)) {
                            $row[$column] = $this->prepareValue($orderData[$column]);
                        }
                    }
                }
            } else { //all fields export
                foreach ($orderData as $key => $val) {
                    $row[$key] = $this->prepareValue($val);
                }

                foreach ($orderItemData as $key => $val) {
                    $row[$key] = $this->prepareValue($val);
                }
            }

            $rows[] = $row;
        }

        return $rows;
    }

    protected function prepareValue($value)
    {
        return str_replace(["\n", "\r"], ' ', $value);
    }

    /**
     * @param array $mapping
     * @param array $orderData
     * @param array $orderItemsData
     * @return array
     */
    protected function prepareOrderBasedFields(
        array $mapping,
        array $orderData,
        array $orderItemsData
    ) {
        $fields = [];

        if (count($mapping) > 0) {
            foreach ($mapping as $config) {
                if (array_key_exists('value', $config)) {
                    $column = $config['value'];

                    if (array_key_exists($column, $orderData)) {
                        $fields[$column] = $orderData[$column];
                    } else {
                        $fields = $this->getFieldsFromOrderItems($orderItemsData, $column);
                    }
                }
            }
        } else { //all fields export
            foreach ($orderData as $key => $val) {
                $fields[$key] = $this->prepareValue($val);
            }

            foreach ($orderItemsData as $orderItemId => $orderItemData) {
                foreach ($orderItemData as $column => $value) {
                    if (!array_key_exists($column, $fields)) {
                        $fields[$column] = [];
                    }
                    
                    $fields[$column][$orderItemId] = $this->prepareValue($value);
                }
            }

            unset($fields['entity_id_track']);
        }

        return $fields;
    }

    private function getFieldsFromOrderItems($orderItemsData, $column)
    {
        $fields = [];
        foreach ($orderItemsData as $orderItemId => $orderItemData) {
            if (array_key_exists($column, $orderItemData)) {
                $fields[$column][$orderItemId] = $this->prepareValue($orderItemData[$column]);
            }
        }
        return $fields;
    }

    /**
     * @param $splitOrderItems
     * @param array $orderData
     * @param array $ordersItemsData
     * @param array $mapping
     * @return array
     */
    protected function getRows(
        $splitOrderItems,
        array $orderData,
        array $ordersItemsData,
        array $mapping
    ) {
        $rows = [];
        $orderItemsData = array_key_exists($orderData['entity_id_track'], $ordersItemsData) ?
            $ordersItemsData[$orderData['entity_id_track']] : [];

        if ($splitOrderItems) {
            $rows = $this->prepareOrderBasedRows($mapping, $orderData, $orderItemsData);
        } else {
            $rows = $this->prepareOrderItemBasedRows($mapping, $orderData, $orderItemsData);
        }

        return $rows;
    }

    /**
     * @param $ordersDataSelection
     * @param $profile
     * @return bool
     * @throws \Zend_Db_Select_Exception
     * @throws \Zend_Json_Exception
     */
    // phpcs:disable Generic.Metrics.NestingLevel.TooHigh
    public function exportOrders($ordersDataSelection, &$profile)
    {
        $lastOrderId = 0;
        $lastInvoiceId = 0;
        $this->profile = $profile;
        $exportType = $this->profile->getData('format');
        $fileHead = $this->profile->getData('export_include_fieldnames');
        $fileHeadCont = false;
        $mapping = $this->getMapping($profile);
        $splitOrderItems = $profile->getSplitOrderItems();
        $splitOrderItemsDelim = $profile->getSplitOrderItemsDelim();
        $xmlMainTag = $profile->getXmlMainTag();
        $xmlOrderTag = $profile->getXmlOrderTag();
        $xmlOrderItemTag = $profile->getXmlOrderItemTag();
        $xmlOrderItemsTag = $profile->getXmlOrderItemsTag();
        $staticFieldsBefore = [];
        $staticFieldsAfter = [];

        /** Collect before and after Static fields */
        if ($staticFields = $profile->getStaticFields()) {
            $staticFields = \Zend_Json::decode($staticFields);
            foreach ($staticFields['label'] as $i => $label) {
                if (!empty($label)) {
                    if (!empty($staticFields['position'][$i])) {
                        $staticFieldsAfter[$label] = $staticFields['value'][$i];
                    } else {
                        $staticFieldsBefore[$label] = $staticFields['value'][$i];
                    }
                }
            }
        }

        // initiate file for export
        if (!$this->helperFile->openFileForWrite($this->profile)) {
            $this->messageManager->addErrorMessage(
                __('We could not create file for export. Please, check file path and folder rights for write.')
            );

            return false;
        }

        if ($exportType === self::TYPE_XML) {
            $this->helperFile->openXmlFile($xmlMainTag);
        } elseif ($exportType === self::TYPE_MS_XML) {
            $this->helperFile->openMsXmlFile();
        }

        /**@var Order $order */
        // run through all orders and export them
        $pagesTotalNum = $ordersDataSelection->getLastPageNumber();
        $currentPage = 1;

        do {
            // load collection page by page
            $ordersDataSelection->setCurPage($currentPage);
            $ordersDataSelection->load();
            $ordersItemsData = $this->helperFilter->getItemsForOrders(
                $profile,
                $ordersDataSelection
            );
            $ordersItems = $this->helperFilter->getOrdersCollection($ordersDataSelection)
                ->getItems();

            foreach ($ordersDataSelection->getData() as $orderData) {
                if (array_key_exists($orderData['entity_id_track'], $ordersItems)) {
                    /** @var Order $order */
                    $order = $ordersItems[$orderData['entity_id_track']];
                    $rows = $this->getRows($splitOrderItems, $orderData, $ordersItemsData, $mapping);

                    foreach ($rows as $fields) {
                        // XML format
                        if (!empty($staticFieldsBefore)) {
                            $fields = $staticFieldsBefore + $fields;
                        }

                        if (!empty($staticFieldsAfter)) {
                            $fields = $fields + $staticFieldsAfter;
                        }

                        if ($exportType === self::TYPE_XML) {
                            // output line
                            $this->helperFile->writeXML($fields, $xmlOrderTag, $xmlOrderItemsTag, $xmlOrderItemTag);
                        } elseif ($exportType === self::TYPE_MS_XML) {
                            // prepare XML header if needed & first time output
                            if ($fileHead && !$fileHeadCont) {
                                $fileHeadCont = $this->helperFile->writeMsXML(array_keys($fields));
                            }

                            // output line
                            $this->helperFile->writeMsXML(array_values($fields), $splitOrderItemsDelim);
                        } else { //CSV format
                            // prepare CSV header if needed & first time output
                            if ($fileHead && !$fileHeadCont) {
                                $fileHeadCont = $this->helperFile->writeCSV(
                                    array_keys($fields),
                                    $this->profile->getData('csv_delim'),
                                    $this->profile->getData('csv_enclose')
                                );
                            }

                            // output line
                            $this->helperFile->writeCSV(
                                array_values($fields),
                                $this->profile->getData('csv_delim'),
                                $this->profile->getData('csv_enclose'),
                                $splitOrderItemsDelim
                            );
                        }
                    }
                    // change order status
                    if ($this->profile->getData('post_status')) {
                        $history = $order->addStatusHistoryComment(
                            __('Amasty Orderexport processing'),
                            $this->profile->getData('post_status')
                        );
                        $history->setIsVisibleOnFront(1);
                        $history->setIsCustomerNotified(0);
                        $history->save();

                        if (!$this->registry->registry('amorderexport_auto_run_triggered')) {
                            $this->registry->register('amorderexport_auto_run_triggered', true);
                        }

                        $order->save();
                    };

                    // save max order id and invoice id
                    if ($lastOrderId < $order->getId()) {
                        $lastOrderId = $order->getId();
                        $lastOrderIncrementId = $order->getIncrementId();
                    }

                    if ($lastInvoiceId < $order->getInvoiceCollection()->getLastItem()->getId()) {
                        $lastInvoiceId = $order->getInvoiceCollection()->getLastItem()->getId();
                        $lastInvoiceIncrementId = $order->getInvoiceCollection()->getLastItem()->getIncrementId();
                    }
                }
            }

            // increment vars
            $currentPage++;
            //clear collection and free memory
            $ordersDataSelection->clear();
        } while ($currentPage <= $pagesTotalNum);

        // save stats after run
        $this->profile->setData('run_records', $ordersDataSelection->count());
        $this->profile->unsetData('filename');

        if ($this->profile->getData('increment_auto') && $lastOrderId) {

            $this->profile->setData('filter_number_from', $lastOrderIncrementId);
            $this->profile->setData('filter_number_to', '');
        }

        if ($this->profile->getData('invoice_increment_auto') && $lastInvoiceId) {
            $this->profile->setData('filter_invoice_from', $lastInvoiceIncrementId);
            $this->profile->setData('filter_invoice_to', '');
        }

        // close all opened tags in XML file
        if ($exportType === self::TYPE_XML) {
            $this->helperFile->closeXmlFile($xmlMainTag);
        } elseif ($exportType === self::TYPE_MS_XML) {
            $this->helperFile->closeMsXmlFile();
        }

        // archive file
        $this->attachmentFilePath = $this->profile->getData('file_path_full') . '.zip';
        $this->helperFile->zip($this->profile->getData('file_path_full'), $this->attachmentFilePath);

        // send via Email if needed
        if ($this->profile->getData('email_use')) {
            $this->sendEmail();
        }

        // send via FTP if needed
        if ($this->profile->getData('ftp_use')) {
            $this->helperUploader->uploadFile($this->profile, $this->profile->getData('file_path_full'));
        }

        // save inner var for outer use
        $this->profile->save();
        $profile = $this->profile;

        return true;
    }
    // phpcs:enable

    /**
     *  Send profile data row via Email
     */
    private function sendEmail()
    {
        if ($this->profile->getData('email_address')) {
            if ($this->profile->getData('last_increment_id')) {
                $emailSubject = $this->profile->getData('email_subject')
                    . '_'
                    . $this->profile->getData('last_increment_id');
            } else {
                $emailSubject = $this->profile->getData('email_subject');
            }

            $this->profile->setData('email_subject', $emailSubject);
            $this->attachmentFilePath = $this->profile->getData('file_path_full');

            if ($this->profile->getData('email_compress')) {
                $this->attachmentFilePath .= '.zip';
                $this->helperFile->zip($this->profile->getData('file_path_full'), $this->attachmentFilePath);
            }

            $this->sendExported();
        }
    }

    /**
     *  Send exported file via Email
     */
    private function sendExported()
    {
        $fromValue = 'trans_email/ident_general/email';

        if ($this->profile->getData('email_from')) {
            $fromValue = $this->profile->getData('email_from');
        }

        $from = $this->scopeConfig->getValue($fromValue);
        $message = "";
        $headers = "From: $from";
        // boundary
        //phpcs:ignore
        $semiRand = md5(time());
        $boundary = "==Multipart_Boundary_x{$semiRand}x";
        // headers for attachment
        $headers .= "\nMIME-Version: 1.0\n" . "Content-Type: multipart/mixed;\n" . " boundary=\"{$boundary}\"";
        // multiPart boundary
        $message .= "\r\n\r\n"
            . __('Message was sent from Amasty OrderExport and includes orders export file attached.')
            . "\r\n\r\n"
            . __('Please, do not answer on this email.')
            . "\r\n\r\n";
        $message = "--{$boundary}\n" . "Content-Type: text/plain; charset=\"iso-8859-1\"\n" .
            "Content-Transfer-Encoding: 7bit\n\n" . $message . "\n\n";
        // preparing attachments
        $message .= "--{$boundary}\n";
        // phpcs:disable
        $fp = @fopen($this->attachmentFilePath, "rb");
        $data = @fread($fp, filesize($this->attachmentFilePath));
        @fclose($fp);
        $data = chunk_split(base64_encode($data));
        $baseName = basename($this->attachmentFilePath);
        $fileSize = filesize($this->attachmentFilePath);
        // phpcs:enable
        $message .= "Content-Type: application/octet-stream; name=\""
            . $baseName
            . "\"\n"
            . "Content-Description: "
            . $baseName
            . "\n"
            . "Content-Disposition: attachment;\n"
            . " filename=\""
            . $baseName
            . "\"; size="
            . $fileSize
            . ";\n"
            . "Content-Transfer-Encoding: base64\n\n"
            . $data
            . "\n\n";
        $message .= "--{$boundary}--";
        $returnPath = "-f" . $from;
        // phpcs:ignore
        mail(
            $this->profile->getData('email_address'),
            $this->profile->getData('email_subject'),
            $message,
            $headers,
            $returnPath
        );
    }
}
