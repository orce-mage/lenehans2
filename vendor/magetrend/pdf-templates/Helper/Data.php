<?php
/**
 * MB "Vienas bitas" (Magetrend.com)
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */

namespace Magetrend\PdfTemplates\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Customer\Model\GroupManagement;
use Magetrend\PdfTemplates\Model\Config\Source\Adapter;
use Magetrend\PdfTemplates\Model\Config\Source\ShippingMethod;
use Magento\Store\Model\ScopeInterface;

/**
 * Module helper class
 *
 * @category MageTrend
 * @package  Magetend/PdfTemplates
 * @author   Edvinas Stulpinas <edwin@magetrend.com>
 * @license  http://opensource.org/licenses/osl-3.0.php Open Software License (OSL 3.0)
 * @link     https://www.magetrend.com/magento-2-pdf-invoice-pro
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * Module status xml path
     */
    const XML_PATH_GENERAL_IS_ACTIVE = 'pdftemplates/general/is_active';

    const XML_PATH_GENERAL_DEV = 'pdftemplates/general/dev';

    const XML_PATH_RTL_PRICE_FIX = 'pdftemplates/general/rtl_price_fix';

    const XML_PATH_TRANSLATE = 'pdftemplates/translate';

    const XML_PATH_CRON_TIMESTAMP = 'pdftemplates/cron/timestamp';

    const XML_PATH_QUOTE_ENABLED = 'pdftemplates/quote/is_active';

    const REGISTRY_IGNORE_KEY = 'mt_pdf_ignore';

    const FILENAME_ORDER = 'pdftemplates/additional/name_order';

    const FILENAME_ORDER_COLLECTION = 'pdftemplates/additional/name_order_collection';

    const FILENAME_INVOICE = 'pdftemplates/additional/name_invoice';

    const FILENAME_INVOICE_COLLECTION = 'pdftemplates/additional/name_invoice_collection';

    const FILENAME_SHIPMENT = 'pdftemplates/additional/name_shipment';

    const FILENAME_SHIPMENT_COLLECTION = 'pdftemplates/additional/name_shipment_collection';

    const FILENAME_CM = 'pdftemplates/additional/name_cm';

    const FILENAME_CM_COLLECTION = 'pdftemplates/additional/name_cm_collection';

    const CUSTOM_PAGE_SIZE = 'pdftemplates/additional/paper_size';

    const XML_PATH_DUPLEX_MODE = 'pdftemplates/additional/duplex_mode';

    const XML_PATH_SALES_PRICE_TAX = 'pdftemplates/sales/price_tax';

    const XML_PATH_SALES_SUBTOTAL_TAX = 'pdftemplates/sales/subtotal_tax';

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    public $storeManager;

    /**
     * @var DirectoryList
     */
    public $directoryList;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $date;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    public $jsonHelper;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    public $timeZone;

    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    public $moduleReader;

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    public $orderRepository;

    /**
     * @var \Magento\Tax\Model\Config
     */
    public $taxConfig;

    /**
     * @var \Magento\Framework\Filesystem
     */
    public $filesystem;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    public $currencyFactory;

    /**
     * @var array
     */
    private $currency = [];

    public $customerRepository;

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param DirectoryList $directoryList
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param \Magento\Framework\Stdlib\DateTime\Timezone $timezone
     * @param \Magento\Framework\Module\Dir\Reader $moduleReader
     * @param \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
     * @param \Magento\Tax\Model\Config $taxConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        DirectoryList $directoryList,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Magento\Framework\Stdlib\DateTime\Timezone $timezone,
        \Magento\Framework\Module\Dir\Reader $moduleReader,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Tax\Model\Config $taxConfig,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository
    ) {
        $this->storeManager = $storeManager;
        $this->directoryList = $directoryList;
        $this->date = $dateTime;
        $this->jsonHelper = $jsonHelper;
        $this->timeZone = $timezone;
        $this->moduleReader = $moduleReader;
        $this->orderRepository = $orderRepository;
        $this->taxConfig = $taxConfig;
        $this->filesystem = $filesystem;
        $this->currencyFactory = $currencyFactory;
        $this->customerRepository = $customerRepository;
        parent::__construct($context);
    }

    /**
     * Is module active in system config
     *
     * @param null $store
     * @return bool
     */
    public function isActive($store = null)
    {
        if ($this->scopeConfig->getValue(
            self::XML_PATH_GENERAL_IS_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        )) {
            return true;
        }
        return false;
    }

    /**
     * Returns translated text
     *
     * @param string $keyWord
     * @param int $storeId
     * @return mixed
     */
    public function translate($keyWord, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TRANSLATE . '/' . $keyWord,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Convert date to text
     *
     * @param $date
     * @param $messageBlock
     * @return \Magento\Framework\Phrase
     */
    public function getDateText($date, $messageBlock)
    {
        $beforeDateTime = strtotime($date);
        $currentDateTime = strtotime($this->date->gmtDate());
        $before1DayTime = strtotime("-1 day", $currentDateTime);
        if (date("d", $beforeDateTime) == date("d", $currentDateTime)) {
            $before = (int)(($currentDateTime - $beforeDateTime) / 60);
            if ($before <= 1) {
                return __($this->translate('before_one_minute'));
            } elseif ($before < 60) {
                return __($this->translate('before_x_minutes'), [$before]);
            } elseif ($before < (60 * 3)) {
                $beforeHour = (int)($before/60);
                if ($beforeHour == 1) {
                    return __($this->translate('before_one_hour'));
                } else {
                    return __($this->translate('before_x_hours'), [$beforeHour]);
                }
            } else {
                return __(
                    $this->translate('today_at'),
                    [$messageBlock->formatTime(date('h:i:s', $beforeDateTime))]
                );
            }
        } elseif (date("d", $beforeDateTime) == date("d", $before1DayTime)) {
            return __(
                $this->translate('yesterday_at'),
                [$messageBlock->formatTime(date('h:i:s', $beforeDateTime))]
            );
        } else {
            $beforeDays = (int)(($currentDateTime - $beforeDateTime) / (60*60*24));
            return __($this->translate('x_days_ago'), [$beforeDays]);
        }
    }

    /**
     * @param Y-m-d H:i:s string $date
     * @param $storeId
     * @param int $type
     * @return string
     */
    public function formatDate($date, $storeId, $type = \IntlDateFormatter::MEDIUM)
    {
        if ($this->singleStoreMode()) {
            $storeId = $this->getGlobalStoreId();
        }

        $localeCode = $this->scopeConfig
            ->getValue('general/locale/code', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
        return $this->timeZone->formatDateTime($date, $type, \IntlDateFormatter::NONE, $localeCode);
    }

    /**
     * Is there created and selected template
     *
     * @param $type
     * @param int $storeId
     * @return bool
     */
    public function isTemplateChanged($type, $storeId = 0, $orderId = null)
    {
        return $this->getPdfTemplateId($type, $storeId, $orderId) > 0;
    }

    /**
     * Returns template id
     *
     * @param $type
     * @param int $storeId
     * @return bool
     */
    public function getPdfTemplateId($type, $storeId = 0, $orderId = null)
    {
        $usage = $this->scopeConfig->getValue(
            'pdftemplates/pdf/usage',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        if ($usage == 0) {
            $pdfTemplate = $this->scopeConfig->getValue(
                'pdftemplates/pdf/'.$type.'_template',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
            return $pdfTemplate;
        }

        $customerGroup = GroupManagement::CUST_GROUP_ALL; //32000 - all groups
        $shippingMethod = ShippingMethod::ALL_SHIPPING_METHODS_CODE;

        if (is_numeric($orderId) && $orderId > 0) {
            try {
                $order = $this->orderRepository->get($orderId);
                $customerId = $order->getCustomerId();

                if ($customerId > 0) {
                    $custmer = $this->customerRepository->getById($customerId);
                    $customerGroup = $custmer->getGroupId();
                } else {
                    $customerGroup = $order->getCustomerGroupId();
                }

                $shippingMethod = !$order->getIsVirtual()?$order->getShippingMethod():$shippingMethod;
            } catch (NoSuchEntityException $e) {
                $customerGroup = GroupManagement::CUST_GROUP_ALL;
            }
        }

        $templateCustormerGroupMap = $this->scopeConfig->getValue(
            'pdftemplates/pdf/'.$type.'_template_customer_group',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($templateCustormerGroupMap)) {
            return 0;
        }

        if ($this->isSerialized($templateCustormerGroupMap)) {
            $map = $this->unserialize($templateCustormerGroupMap);
        } else {
            $map = $this->jsonHelper->jsonDecode($templateCustormerGroupMap);
        }

        if (empty($map)) {
            return 0;
        }

        $templateMap = [];
        foreach ($map as $option) {
            $templateMap[$option['customer_group']][$option['shipping_method']] = $option['pdf_template'];
        }

        if (isset($templateMap[$customerGroup][$shippingMethod])) {
            return $templateMap[$customerGroup][$shippingMethod];
        }

        if (isset($templateMap[$customerGroup][ShippingMethod::ALL_SHIPPING_METHODS_CODE])) {
            return $templateMap[$customerGroup][ShippingMethod::ALL_SHIPPING_METHODS_CODE];
        }

        if (isset($templateMap[GroupManagement::CUST_GROUP_ALL][$shippingMethod])) {
            return $templateMap[GroupManagement::CUST_GROUP_ALL][$shippingMethod];
        }

        if (isset($templateMap[GroupManagement::CUST_GROUP_ALL][ShippingMethod::ALL_SHIPPING_METHODS_CODE])) {
            return $templateMap[GroupManagement::CUST_GROUP_ALL][ShippingMethod::ALL_SHIPPING_METHODS_CODE];
        }

        return 0;
    }

    /**
     * Return module installation dir
     * @param string $path
     * @return string
     */
    public function getModuleViewDirectory($path = '', $moduleName = 'Magetrend_PdfTemplates')
    {
        return rtrim($this->moduleReader->getModuleDir(
            \Magento\Framework\Module\Dir::MODULE_VIEW_DIR,
            $moduleName
        ), '/').'/'.trim($path, '/').'/';
    }

    /**
     * Write to log file
     *
     * @param $message
     */
    public function log($message)
    {
        $this->_logger->error($message);
    }

    /**
     * Remove 'px' from string or array
     *
     * @param $data
     * @param $fields
     * @return array|string
     */
    public function removePx($data, $fields = [])
    {
        if (is_array($data)) {
            if (empty($fields)) {
                foreach ($data as $key => $value) {
                    $data[$key] = str_replace('px', '', $value);
                }
            } else {
                foreach ($fields as $key) {
                    if (!isset($data[$key])) {
                        continue;
                    }
                    $data[$key] = str_replace('px', '', $data[$key]);
                }
            }
        } else {
            $data = str_replace('px', '', $data);
        }
        return $data;
    }

    /**
     * Convert px to points
     *
     * @param $px
     * @return float|int
     */
    public function toPoint($px)
    {
        $px = str_replace('px', '', $px);
        $px = ((int)$px) * 72 / 96;
        return $px;
    }

    public function isEnabledOnFrontend($storeId = 0)
    {
        return $this->scopeConfig->getValue(
            'pdftemplates/general/frontend_is_active',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getAdapterName($storeId = 0)
    {
        $adapter = $this->scopeConfig->getValue(
            'pdftemplates/general/adapter',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        if (empty($adapter)) {
            return Adapter::ZEND_PDF;
        }

        return $adapter;
    }

    public function getColumnConfig($type)
    {
        $columnConfig = $this->scopeConfig->getValue(
            'pdftemplates/items/columns',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            0
        );

        foreach ($columnConfig as $key => $column) {
            if (!isset($column['is_active'][$type]) || $column['is_active'][$type] != 1) {
                unset($columnConfig[$key]);
                continue;
            }
            $columnConfig[$key]['key'] = $key;
        }
        usort($columnConfig, [$this, 'sortColumns']);

        $config = [];
        foreach ($columnConfig as $column) {
            $config[$column['key']] = $column;
        }

        return $config;
    }

    /**
     * Sort totals list
     *
     * @param  array $a
     * @param  array $b
     * @return int
     */
    public function sortColumns($a, $b)
    {
        if (!isset($a['sort_order']) || !isset($b['sort_order'])) {
            return 0;
        }

        if ($a['sort_order'] == $b['sort_order']) {
            return 0;
        }

        return $a['sort_order'] > $b['sort_order'] ? 1 : -1;
    }

    public function getPaymentConfig($paymentCode)
    {
        $config = $this->scopeConfig->getValue(
            'pdftemplates/payment',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            0
        );

        if (isset($config[$paymentCode])) {
            return $config[$paymentCode];
        }

        return [
            'renderer' => 'Magetrend\PdfTemplates\Model\Pdf\Element\Payment\DefaultRenderer'
        ];
    }

    public function getTrackColumnConfig()
    {
        $columnConfig = $this->scopeConfig->getValue(
            'pdftemplates/track_columns/columns',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            0
        );

        return $columnConfig;
    }

    public function singleStoreMode($storeId = 0)
    {
        $columnConfig = $this->scopeConfig->getValue(
            'pdftemplates/general/single_store_mode',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );

        return $columnConfig == 1;
    }

    public function getGlobalStoreId($storeId = 0)
    {
        return $this->scopeConfig->getValue(
            'pdftemplates/general/globa_store_id',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getPriceTaxMode($storeId = 0)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SALES_PRICE_TAX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getSubtotalTaxMode($storeId = 0)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SALES_SUBTOTAL_TAX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function breakItemOptions($storeId = 0)
    {
        return $this->scopeConfig->getValue(
            'pdftemplates/additional/brake_option_line',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getCustomPageSizes()
    {
        $sizes = $this->scopeConfig->getValue(
            self::CUSTOM_PAGE_SIZE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            0
        );

        if (empty($sizes)) {
            return [];
        }

        $data = [];
        $sizes = json_decode($sizes, true);
        if (!empty($sizes)) {
            foreach ($sizes as $size) {
                $data[] = $size;
            }
        }

        return $data;
    }

    public function getFolderBySize($size = \Zend_Pdf_Page::SIZE_A4)
    {
        return rtrim(strtolower(str_replace(':', '_', $size)), '_');
    }

    public function prepareFileName($fileName)
    {
        return str_replace('/', '_', $fileName);
    }

    private function isSerialized($value)
    {
        return (boolean) preg_match('/^((s|i|d|b|a|O|C):|N;)/', $value);
    }

    public function getTotalsSorting()
    {
        $sortOrderList = $this->scopeConfig->getValue(
            'pdftemplates/additional/total_sort_order',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );

        if (empty($sortOrderList)) {
            return [];
        }

        $data = [];
        $sortOrderList = json_decode($sortOrderList, true);
        if (!empty($sortOrderList)) {
            foreach ($sortOrderList as $totalLine) {
                $data[$totalLine['source_field']] = $totalLine['sort_order'];
            }
        }

        return $data;
    }

    public function getFileName($type, $params = [], $storeId = null)
    {
        if ($storeId === null) {
            $storeId = $this->storeManager->getStore()->getId();
        }
        $fileNameTemplate = $this->scopeConfig->getValue($type, ScopeInterface::SCOPE_STORE, $storeId);

        if (empty($fileNameTemplate)) {
            $fileNameTemplate = $this->getDefaultFileNameTemplate($type);
        }

        return (string)__($fileNameTemplate, $params);
    }

    public function getDefaultFileNameTemplate($type)
    {
        switch ($type) {
            case self::FILENAME_ORDER:
                return 'order_%increment_id.pdf';
            case self::FILENAME_ORDER_COLLECTION:
                return 'orders.pdf';
            case self::FILENAME_INVOICE:
                return 'invoice_%increment_id.pdf';
            case self::FILENAME_INVOICE_COLLECTION:
                return 'invoices.pdf';
            case self::FILENAME_SHIPMENT:
                return 'shipment_%increment_id.pdf';
            case self::FILENAME_SHIPMENT_COLLECTION:
                return 'shipments.pdf';
            case self::FILENAME_CM:
                return 'creditmemo_%increment_id.pdf';
            case self::FILENAME_CM_COLLECTION:
                return 'creditmemos.pdf';
        }

        return 'uknown_file_name.pdf';
    }

    public function isTaxSummaryEnabled($store)
    {
        return $this->taxConfig->displaySalesFullSummary($store);
    }

    public function serialize($string)
    {
        $serializer = \Zend\Serializer\Serializer::factory(\Zend\Serializer\Adapter\PhpSerialize::class);
        return $serializer->serialize($string);
    }

    public function unserialize($string)
    {
        $serializer = \Zend\Serializer\Serializer::factory(\Zend\Serializer\Adapter\PhpSerialize::class);
        return $serializer->unserialize($string);
    }

    public function removeInterlaceFromPng($path, $saveToTmp = false)
    {
        if (strpos(strtolower($path), '.png') === false || strpos(strtolower($path), 'nonInterlace.png') !== false) {
            return $path;
        }

        try {
            $img = imagecreatefrompng($path);
            imagealphablending($img, false);
            imagesavealpha($img, true);
            imageinterlace($img, 0);
        } catch (\Exception $e) {
            return $path;
        }

        if ($saveToTmp) {
            $destination = $this->filesystem->getDirectoryWrite(DirectoryList::TMP);
            $destination->create();
            $ext = explode('.', $path);
            $ext = end($ext);
            $uniqueId = uniqid(\Magento\Framework\Math\Random::getRandomNumber()) . time() . '.'.$ext;
            $path = $destination->getAbsolutePath('nonInterlacepng_'.$uniqueId);
        }

        imagepng($img, $path);
        return $path;
    }

    public function formatPrice($currecyCode, $price)
    {
        return $this->getCurrency($currecyCode)->formatPrecision($price, 2, [], false, false);
    }

    public function getCurrency($currecyCode)
    {
        if (!isset($this->currency[$currecyCode])) {
            $currency = $this->currencyFactory->create();
            $currency->load($currecyCode);
            $this->currency[$currecyCode] = $currency;
        }

        return $this->currency[$currecyCode];
    }

    public function getCurrencyCode($storeId = null)
    {
        return $this->storeManager->getStore($storeId)->getCurrentCurrency()->getCode();
    }

    public function getCurrencySymbol($storeId = null)
    {
        return $this->storeManager->getStore($storeId)->getCurrentCurrency()->getCurrencySymbol();
    }

    public function isQuoteEnabled()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_QUOTE_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    public function isDuplexModeEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_DUPLEX_MODE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }
    public function isPriceFixEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_RTL_PRICE_FIX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }
}