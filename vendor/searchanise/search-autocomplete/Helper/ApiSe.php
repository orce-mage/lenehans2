<?php

namespace Searchanise\SearchAutocomplete\Helper;

use Searchanise\SearchAutocomplete\Model\Configuration;
use Searchanise\SearchAutocomplete\Model\Format as SeFormat;
use Searchanise\SearchAutocomplete\Helper\Data as SearchaniseHelper;
use Searchanise\SearchAutocomplete\Helper\Notification as SeNotification;
use Searchanise\SearchAutocomplete\Model\QueueFactory;
use Searchanise\SearchAutocomplete\Model\Queue;
use Searchanise\SearchAutocomplete\Helper\Logger as SeLogger;
use Searchanise\SearchAutocomplete\Helper\ApiProducts;
use Searchanise\SearchAutocomplete\Helper\ApiPages;
use Searchanise\SearchAutocomplete\Helper\ApiCategories;
use Searchanise\SearchAutocomplete\Exception\SignupException;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadataInterface;
use Magento\Framework\Json\Helper\Data as JsonHelper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Module\ResourceInterface;
use Magento\Framework\App\State as AppState;
use Magento\Framework\App\Request\Http as HttpRequest;
use Magento\Framework\HTTP\PhpEnvironment\Response as HttpResponse;
use Magento\Framework\Profiler as MagentoProfiler;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Backend\App\Area\FrontNameResolver;
use Magento\Backend\Model\Auth\SessionFactory as BackendSessionFactory;
use Magento\Backend\Model\UrlInterface;
use Magento\Store\Model\Store as StoreModel;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\App\Emulation as AppEmulation;
use Magento\Customer\Model\GroupManagement;
use Magento\Customer\Model\Group as CustomerGroup;
use Magento\Customer\Model\SessionFactory as CustomerSessionFactory;

class ApiSe extends AbstractHelper
{
    const COMPRESS_RATE = 5;
    const PLATFORM_NAME = 'magento2';
    const CONFIG_PREFIX = 'se_';

    // The "All" variant of the items per page menu is replaced with this value
    // if the "Allow All Products per Page" option is active.
    const MAX_PAGE_SIZE = 100;

    const SUGGESTIONS_MAX_RESULTS = 1;
    const FLOAT_PRECISION = 2; // for server float = decimal(12,2)
    const LABEL_FOR_PRICES_USERGROUP = 'se_price_';

    const EXPORT_STATUS_QUEUED     = 'queued';
    const EXPORT_STATUS_START      = 'start';
    const EXPORT_STATUS_PROCESSING = 'processing';
    const EXPORT_STATUS_SENT       = 'sent';
    const EXPORT_STATUS_DONE       = 'done';
    const EXPORT_STATUS_SYNC_ERROR = 'sync_error';
    const EXPORT_STATUS_NONE       = 'none';

    const SE_ADDON_STATUS_DISABLED = 'disabled';
    const SE_ADDON_STATUS_ENABLED  = 'enabled';
    const SE_ADDON_STATUS_DELETED  = 'deleted';

    const NOT_USE_HTTP_REQUEST     = 'not_use_http_request';
    const NOT_USE_HTTP_REQUEST_KEY = 'Y';

    const FL_SHOW_STATUS_ASYNC     = 'show_status';
    const FL_SHOW_STATUS_ASYNC_KEY = 'Y';

    const ASYNC_STATUS_OK = 'OK';

    const SUPPORT_EMAIL = 'feedback@searchanise.com';
    const TEST_CONNECTION_TIMEOUT = 5;

    const INTERNAL_MODULE_NAME   = 'Searchanise_SearchAutocomplete';
    const MODULE_STATUS_ENABLED  = 'Y';
    const MODULE_STATUS_DISABLED = 'D';

    public static $consoleEmail = '';

    /**
     * @var string
     */
    private $parentPrivateKeySe;

    /**
     * @var array
     */
    private $privateKeySe = [];

    /**
     * @var array
     */
    public static $exportStatusTypes = [
        self::EXPORT_STATUS_QUEUED,
        self::EXPORT_STATUS_START,
        self::EXPORT_STATUS_PROCESSING,
        self::EXPORT_STATUS_SENT,
        self::EXPORT_STATUS_DONE,
        self::EXPORT_STATUS_SYNC_ERROR,
        self::EXPORT_STATUS_NONE,
    ];

    /**
     * @var array
     */
    public $seStoreIds = [];

    /**
     * @var CustomerSessionFactory
     */
    private $customerSession;

    /**
     * @var BackednCustomerSessionFactory
     */
    private $adminSession;

    /**
     * @var UrlInterface
     */
    private $backendUrl;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SeFormat
     */
    private $format;

    /**
     * @var JsonHelper
     */
    private $jsonHelper;

    /**
     * @var SearchaniseHelper
     */
    private $dataHelper;

    /**
     * @var SeNotification
     */
    private $notificationHelper;

    /**
     * @var SeLogger
     */
    private $loggerHelper;

    /**
     * @var ProductMetadataInterface
     */
    private $productMetadata;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var AppState
     */
    private $appState;

    /**
     * @var QueueFactory
     */
    private $queueFactory;

    /**
     * @var ApiProducts
     */
    private $apiProductsHelper;

    /**
     * @var ApiPages
     */
    private $apiPagesHelper;

    /**
     * @var ApiCategories
     */
    private $apiCategoriesHelper;

    /**
     * @var ResourceInterface
     */
    private $moduleResource;

    /**
     * @var HttpResponse
     */
    private $httpResponse = null;

    /**
     * @var HttpRequest
     */
    private $httpRequest;

    public function __construct(
        Context $context,
        ProductMetadataInterface $productMetadata,
        CustomerSessionFactory $customerSessionFactory,
        BackendSessionFactory $adminSessionFactory,
        UrlInterface $backendUrl,
        StoreManagerInterface $storeManager,
        JsonHelper $jsonHelper,
        TimezoneInterface $localeDate,
        ResourceInterface $moduleResource,
        AppState $appState,
        AppEmulation $appEmulation,
        HttpRequest $httpRequest,
        Configuration $configuration,
        SeFormat $format,
        Searchanisehelper $dataHelper,
        SeNotification $notificationHelper,
        QueueFactory $queueFactory,
        SeLogger $loggerHelper,
        ApiProducts $apiProducts,
        ApiPages $apiPagesHelper,
        ApiCategories $apiCategoriesHelper
    ) {
        $this->configuration = $configuration;
        $this->customerSession = $customerSessionFactory->create();
        $this->adminSession = $adminSessionFactory->create();
        $this->backendUrl = $backendUrl;
        $this->storeManager = $storeManager;
        $this->format = $format;
        $this->jsonHelper = $jsonHelper;
        $this->dataHelper = $dataHelper;
        $this->notificationHelper = $notificationHelper;
        $this->productMetadata = $productMetadata;
        $this->loggerHelper = $loggerHelper;
        $this->localeDate = $localeDate;
        $this->moduleResource = $moduleResource;
        $this->appState = $appState;
        $this->appEmulation = $appEmulation;
        $this->httpRequest = $httpRequest;
        $this->queueFactory = $queueFactory;
        $this->apiProductsHelper = $apiProducts;
        $this->apiPagesHelper = $apiPagesHelper;
        $this->apiCategoriesHelper = $apiCategoriesHelper;

        parent::__construct($context);
    }

    /**
     * Returns css search input selector
     *
     * @return string
     */
    public function getSearchInputSelector()
    {
        return $this->configuration->getValue(Configuration::XML_PATH_SEARCH_INPUT_SELECTOR, null, '');
    }

    /**
     * Format date using current locale options
     *
     * @param  timestamp|int
     * @param  string        $format
     * @param  bool          $showTime
     *
     * @return string
     */
    public function formatDate($timestamp = null, $format = \IntlDateFormatter::SHORT, $showTime = false)
    {
        if (empty($timestamp)) {
            return '';
        }

        return $this->localeDate->formatDate((new \DateTime)->setTimestamp($timestamp), $format, $showTime);
    }

    /**
     * Get module status for the store view
     *
     * @param  int|null $storeId    Store identifier
     * @param  string   $moduleName Module Name
     *
     * @return string
     */
    public function getStatusModule($storeId = null, $moduleName = self::INTERNAL_MODULE_NAME)
    {
        if (empty($moduleName)) {
            return self::MODULE_STATUS_DISABLED;
        }

        return $this->configuration->getValue('advanced/modules_disable_output/' . $moduleName, $storeId)
            ? self::MODULE_STATUS_DISABLED
            : self::MODULE_STATUS_ENABLED;
    }

    /**
     * Check module status for the store view
     *
     * @param  number $storeId    Store identifier
     * @param  string $moduleName Module Name
     *
     * @return bool
     */
    public function checkStatusModule($storeId = null, $moduleName = self::INTERNAL_MODULE_NAME)
    {
        return $this->getStatusModule($storeId, $moduleName) == self::MODULE_STATUS_ENABLED;
    }

    /**
     * Returns api_key for store
     *
     * @param  number $storeId Store identifier
     *
     * @return string
     */
    public function getApiKey($storeId = null)
    {
        return $this->configuration->getValue(
            Configuration::XML_PATH_API_KEY,
            $this->storeManager->getStore($storeId)->getId()
        );
    }

    /**
     * Returns all registered api keys
     *
     * @return array
     */
    public function getApiKeys()
    {
        $key_ids = [];
        $stores = $this->getStores();

        if (!empty($stores)) {
            foreach ($stores as $store) {
                $key_ids[$store->getId()] = $this->getApiKey($store->getId());
            }
        }

        return $key_ids;
    }

    /**
     * Delete all keys for the stores
     *
     * @param array $storeIds       Store identifier
     * @param bool  $unsetStoreData Unset Store data
     *
     * @return boolean
     */
    public function deleteKeys(array $storeIds = [], $unsetStoreData = false)
    {
        $stores = $this->getStores($storeIds);

        if (!empty($stores)) {
            foreach ($stores as $store) {
                $this->deleteKeysForStore($store, $unsetStoreData);
            }
        }

        return true;
    }

    /**
     * Removes Searchanise data for store
     *
     * @param StoreModel $store
     * @param  bool  $unsetStoreData Unset Store data
     *
     * @return bool
     */
    public function deleteKeysForStore(StoreModel $store, $unsetStoreData = false)
    {
        if ($store) {
            $this->sendAddonStatusRequest(self::SE_ADDON_STATUS_DELETED, $store);

            if ($unsetStoreData == true) {
                $this->setApiKey('', $store->getId());
                $this->setPrivateKey('', $store->getId());
                $this->setExportStatus('', $store->getId());
            }

            $this->queueFactory->create()->deleteKeys((array)$store->getId());

            return true;
        }

        return false;
    }

    /**
     * Checks if the private key exists
     *
     * @param int|null $storeId Store identifier
     *
     * @return bool
     */
    public function checkPrivateKey($storeId = null)
    {
        $key = $this->getPrivateKey($storeId);

        return !empty($key);
    }

    /**
     * Sets api key for store
     *
     * @param string $value     Api key
     * @param int|null $storeId Store identifier
     */
    public function setApiKey(string $apiKey, $storeId = null)
    {
        $this->configuration->setValue(Configuration::XML_PATH_API_KEY, $apiKey, $storeId);
    }

    /**
     * Returns Searchanise service url
     *
     * @param bool $onlyHttp Returns http or https url
     *
     * @return string
     */
    public function getServiceUrl($onlyHttp = true)
    {
        $ret = $this->configuration->getValue(Configuration::XML_PATH_SERVICE_URL);

        if (!$onlyHttp) {
            if ($this->storeManager->getStore()->isCurrentlySecure()) {
                $ret = str_replace('http://', 'https://', $ret);
            }
        }

        return $ret;
    }

    /**
     * Get 'not_use_http_request' param for URL
     *
     * @return string
     */
    public function getParamNotUseHttpRequest()
    {
        return self::NOT_USE_HTTP_REQUEST . '=' . self::NOT_USE_HTTP_REQUEST_KEY;
    }

    /**
     * Returns Searchanise resync url
     *
     * @return string
     */
    public function getReSyncLink()
    {
        return 'searchanise/searchanise/resync';
    }

    /**
     * Returns Searchanise option url
     */
    public function getOptionsLink()
    {
        return 'searchanise/searchanise/options';
    }

    /**
     * Returns connection link
     *
     * @return string
     */
    public function getConnectLink()
    {
        return 'searchanise/searchanise/signup';
    }

    /**
     * Returns Searchanise link
     *
     * @return string
     */
    public function getSearchaniseLink()
    {
        return 'searchanise/searchanise/index';
    }

    /**
     * Returns module link
     *
     * @return string
     */
    public static function getModuleLink()
    {
        return 'searchanise/searchanise/index';
    }

    /**
     * Gets async link
     *
     * @param  boolean $flNotUserHttpRequest
     *
     * @return string
     */
    public function getAsyncLink($flNotUserHttpRequest = false)
    {
        $link = 'searchanise/async/';

        if ($flNotUserHttpRequest) {
            $link .= '?' . $this->getParamNotUseHttpRequest();
        }

        return $link;
    }

    /**
     * Form and get async url
     *
     * @param bool $flNotUserHttpRequest Use https link or http
     * @param int  $storeId              Store identifier
     * @param bool $flCheckSecure        Check if secure
     *
     * @return string
     */
    public function getAsyncUrl($flNotUserHttpRequest = false, $storeId = null, $flCheckSecure = true)
    {
        return $this->getUrl(
            $this->getAsyncLink(false),
            $flNotUserHttpRequest,
            $storeId,
            $flCheckSecure,
            [
            '_nosid' => true,
            ]
        );
    }

    /**
     * Returns admin module url
     *
     * @return string
     */
    public function getModuleUrl()
    {
        return $this->backendUrl->getUrl($this->getModuleLink());
    }

    /**
     * Build query from the array
     *
     * @param  string   $dispatch             Dispatch for URL
     * @param  boolean  $flNotUserHttpRequest
     * @param  null|int $store_id             Store identifier
     * @param  boolean  $flCheckSecure
     * @param  array    $params               Additional params
     *
     * @return string
     */
    public function getUrl(
        $dispatch,
        $flNotUserHttpRequest = false,
        $storeId = null,
        $flCheckSecure = true,
        array $params = []
    ) {
        if ($storeId != '') {
            $prevStoreId = $this->storeManager->getStore()->getId();
            // need for generate correct url
            if ($prevStoreId != $storeId) {
                $this->storeManager->setCurrentStore($storeId);
            }
        }

        $defaultParams = [];

        $params = array_merge($defaultParams, $params);

        if ($flCheckSecure) {
            if ($this->storeManager->getStore()->isCurrentlySecure()) {
                $params['_secure'] = true;
            }
        }

        $params['store'] = $this->storeManager->getStore();
        $url = $this->_urlBuilder->getUrl($dispatch, $params);

        if ($flNotUserHttpRequest) {
            $url .= strpos($url, '?') === false ? '?' : '&';
            $url .= $this->getParamNotUseHttpRequest();
        }

        if ($storeId != '') {
            if ($prevStoreId != $storeId) {
                $this->storeManager->setCurrentStore($prevStoreId);
            }
        }

        return $url;
    }

    /**
     * Check 'AutoInstall' flag
     *
     * @return bool
     */
    public function checkAutoInstall()
    {
        // ToDo: remove this wrapper (?)
        return $this->configuration->checkAutoInstall();
    }

    /**
     * Check if cron async is enabled
     *
     * @return bool
     */
    public function checkCronAsync()
    {
        return
            $this->configuration->getValue(Configuration::XML_PATH_CRON_ASYNC_ENABLED)
            && !$this->getIsIndexEnabled();
    }

    /**
     * Check if ajax async is enabled
     *
     * @return bool
     */
    public function checkAjaxAsync()
    {
        return
            $this->configuration->getValue(Configuration::XML_PATH_AJAX_ASYNC_ENABLED)
            && !$this->getIsIndexEnabled();
    }

    /**
     * Checks if object syncronisation is enabled
     *
     * @return bool
     */
    public function checkObjectAsync()
    {
        return
            $this->configuration->getValue(Configuration::XML_PATH_OBJECT_ASYNC_ENABLED)
            && !$this->getIsIndexEnabled();
    }

    /**
     * Gets if Searchanise indexer is enabled
     *
     * @return bool
     */
    public function getIsIndexEnabled()
    {
        return $this->configuration->getIsIndexEnabled();
    }

    /**
     * Returns max page size
     *
     * @return int
     */
    public function getMaxPageSize()
    {
        return $this->configuration->getMaxPageSize();
    }

    /**
     * Returns addon options
     *
     * @return array
     */
    public function getAddonOptions()
    {
        $ret = [];

        $ret['service_ur']         = $this->getServiceUrl();
        $ret['parent_private_key'] = $this->getParentPrivateKey();
        $ret['private_key']        = $this->getPrivateKeys();
        $ret['api_key']            = $this->getApiKeys();
        $ret['export_status']      = $this->getExportStatuses();

        $ret['last_request'] = $this->formatDate(
            $this->configuration->getLastRequest(),
            \IntlDateFormatter::MEDIUM,
            true
        );
        $ret['last_resync']  = $this->formatDate(
            $this->configuration->getLastResync(),
            \IntlDateFormatter::MEDIUM,
            true
        );

        $ret['addon_status']  = $this->getStatusModule() == self::MODULE_STATUS_ENABLED ? 'enabled' : 'disabled';
        $ret['addon_version'] = $this->moduleResource->getDataVersion(self::INTERNAL_MODULE_NAME);

        $ret['core_edition'] = $this->productMetadata->getEdition();
        $ret['core_version'] = $this->getMagentoVersion();
        $ret['core_version_info'] = $this->getVersionInfo();

        return $ret;
    }

    /**
     * Update current module version
     *
     * @return bool
     */
    public function updateInsalledModuleVersion()
    {
        $currentVersion = $this->moduleResource->getDataVersion(self::INTERNAL_MODULE_NAME);

        return $this->configuration->setInsalledModuleVersion($currentVersion);
    }

    /**
     * Check if module is updated
     *
     * @return bool
     */
    public function checkModuleIsUpdated()
    {
        $currentVersion = $this->moduleResource->getDataVersion(self::INTERNAL_MODULE_NAME);

        return $this->configuration->getInsalledModuleVersion() != $currentVersion;
    }

    /**
     * Returns magento version
     *
     * @return string
     */
    public function getMagentoVersion()
    {
        return $this->productMetadata->getVersion();
    }

    /**
     * Returns magento version information
     *
     * @return array
     */
    public function getVersionInfo()
    {
        $versionInfo = [];
        $version = $this->getMagentoVersion();

        if (!empty($version)) {
            list($major, $minor, $revision) = explode('.', $version);

            $versionInfo = [
                'major'     => $major,
                'minor'     => $minor,
                'revision'  => $revision,
                'patch'     => '',
                'stability' => '',
                'number'    => ''
            ];
        }

        return $versionInfo;
    }

    /**
     * Returns Searchanise search widget url
     *
     * @param bool $onlyHttp HTTPS or HTTP link
     *
     * @return string
     */
    public function getSearchWidgetsLink($onlyHttp = true)
    {
        return $this->getServiceUrl($onlyHttp) . '/widgets/v1.0/init.js';
    }

    /**
     * Returns if need to show out of stock products
     *
     * @return bool
     */
    public function getIsShowOutOfStockProducts()
    {
        return $this->configuration
            ->getIsShowOutOfStockProducts();
    }

    /**
     * Returns true if need to use secure urls on frontend
     *
     * @return bool
     */
    public function getIsUseSecureUrlsInFrontend($store)
    {
        return $this->configuration->getIsUseSecureUrlsInFrontend();
    }

    /**
     * Returns se price format according store settings
     *
     * @param int|null Store identifier
     *
     * @return array
     */
    public function getPriceFormat($storeId = null)
    {
        $store = $this->storeManager->getStore($storeId);
        $locale_code = $this->configuration->getValue(
            \Magento\Directory\Helper\Data::XML_PATH_DEFAULT_LOCALE,
            $store->getStoreId()
        );
        $currency_code = $store->getCurrentCurrencyCode();

        $price_format = $this->format->getPriceFormat($locale_code, $currency_code);
        $position_price = strpos($price_format['pattern'], '%s');
        $symbol = str_replace('%s', '', $price_format['pattern']);

        $se_rate = 1;
        $rate = $store->getCurrentCurrencyRate();

        if (!empty($rate)) {
            $se_rate = 1 / $rate;
        }

        $price_format = [
            'rate'                => $se_rate, // It requires inverse value.
            'decimals'            => $price_format['precision'],
            'decimals_separator'  => $price_format['decimal_symbol'],
            'thousands_separator' => $price_format['group_symbol'],
            'symbol'              => $symbol,
            'after'               => $position_price == 0,
        ];

        return $price_format;
    }

    /**
     * Returns label for usergroup
     *
     * @return string
     */
    public function getCurLabelForPricesUsergroup()
    {
        $customer_group_id = $this->customerSession->getCustomerGroupId();
        $customer_usergroup_ids = [
            $this->getLabelForPricesUsergroup() . GroupManagement::CUST_GROUP_ALL
        ];

        if (!$this->customerSession->isLoggedIn()) {
            $customer_usergroup_ids[] = $this->getLabelForPricesUsergroup() . CustomerGroup::NOT_LOGGED_IN_ID;
        }

        if ($customer_group_id) {
            $customer_usergroup_ids[] = $this->getLabelForPricesUsergroup() . $customer_group_id;
        }

        return implode('|', array_unique($customer_usergroup_ids));
    }

    /**
     * Returns label for price usergroup
     *
     * @return string
     */
    public static function getLabelForPricesUsergroup()
    {
        return self::LABEL_FOR_PRICES_USERGROUP;
    }

    /**
     * Returns float precision
     *
     * @return int
     */
    public static function getFloatPrecision()
    {
        return self::FLOAT_PRECISION;
    }

    /**
     * Returns max suggestion count
     *
     * @return int
     */
    public static function getSuggestionsMaxResults()
    {
        return self::SUGGESTIONS_MAX_RESULTS;
    }

    /**
     * Excape characters
     *
     * @param  string $str
     *
     * @return string|mixed
     */
    public static function escapingCharacters($str)
    {
        $ret = '';

        if ($str != '') {
            $str = trim($str);

            if ($str != '') {
                $str = str_replace('|', '\|', $str);
                $str = str_replace(',', '\,', $str);

                $ret = $str;
            }
        }

        return $ret;
    }

    /**
     * Main signup function to get keys for stores
     *
     * @param StoreModel $curStore         Current store
     * @param boolean    $showNotification Flag to show notifications
     * @param boolean    $flSendRequest    Flag to send the request
     *
     * @throw SignupException
     * @return boolean
     * @TODO:  Check $curStore object class
     */
    public function signup($curStore = null, $showNotification = true, $flSendRequest = true)
    {
        static $isShowed = false;
        $email = '';
        $connected = true;
        ignore_user_abort(true);
        set_time_limit(0);

        if (php_sapi_name() == 'cli') {
            if (!empty(self::$consoleEmail)) {
                $email = self::$consoleEmail;
            } else {
                $this->loggerHelper->log("ApiSeHelper::signup() console signup not supported!", SeLogger::TYPE_DEBUG);
                return false;
            }
        }

        if (empty($email) && $this->adminSession && $this->adminSession->hasUser()) {
            $email = $this->adminSession->getUser()->getEmail();
        }

        if (empty($email)) {
            $this->loggerHelper->log("ApiSeHelper::signup() admin email is empty!", SeLogger::TYPE_DEBUG);
            return false;
        }

        $this->loggerHelper->log("ApiSeHelper::signup() Started", SeLogger::TYPE_DEBUG);

        $stores = !empty($curStore) ? [$curStore->getId() => $curStore] : $this->getStores();
        $parentPrivateKey = $this->getParentPrivateKey();

        foreach ($stores as $store) {
            $privateKey = $this->getPrivateKey($store->getStoreId());

            if (!empty($privateKey)) {
                if ($flSendRequest) {
                    if ($store->getIsActive()) {
                        $this->sendAddonStatusRequest(self::SE_ADDON_STATUS_ENABLED, $store);
                    } else {
                        $this->sendAddonStatusRequest(self::SE_ADDON_STATUS_DISABLED, $store);
                    }
                }

                continue;
            }

            if ($showNotification == true && empty($isShowed)) {
                $this->echoConnectProgress('Connecting to Searchanise..', $this->httpResponse);
                $isShowed = true;
            }

            $url = $this->getUrl('', false, $store->getId(), true, [
                '_nosid' => true,
                '_query' => '___store=' . $store->getCode(),
            ]);

            if (!(strstr($url, 'http'))) {
                $base_url = $this->storeManager->getStore()->getBaseUrl();
                $url = str_replace('index.php/', $base_url, $url);
            }

            list($h, $response) = $this->httpRequest(
                \Zend_Http_Client::POST,
                $this->getServiceUrl() . '/api/signup/json', [
                    'url'                => $url,
                    'email'              => $email,
                    'version'            => $this->configuration->getServerVersion(),
                    'platform'           => self::PLATFORM_NAME,
                    'parent_private_key' => $parentPrivateKey,
                ],
                [],
                [],
                $this->configuration->getRequestTimeout()
            );

            if ($showNotification == true) {
                $this->echoConnectProgress('.', $this->httpResponse);
            }

            if (!empty($response) && $responseKeys = $this->parseResponse($response, true)) {
                $apiKey = empty($responseKeys['keys']['api']) ? false : $responseKeys['keys']['api'];
                $privateKey = empty($responseKeys['keys']['private']) ? false : $responseKeys['keys']['private'];

                if (empty($apiKey) || empty($privateKey)) {
                    $this->loggerHelper->log("ApiSeHelper::signup() Empty apikey returned!");
                    return false;
                }

                if (empty($parentPrivateKey)) {
                    $this->setParentPrivateKey($privateKey);
                    $parentPrivateKey = $privateKey;
                }

                $this->setApiKey($apiKey, $store->getId());
                $this->setPrivateKey($privateKey, $store->getId());
            } else {
                $connected = false;
                $this->loggerHelper->log("ApiSeHelper::signup() Invalid response from server!");

                if ($showNotification == true) {
                    $this->echoConnectProgress(' Error<br />', $this->httpResponse);
                }

                break;
            }

            $this->setExportStatus(self::EXPORT_STATUS_NONE, $store->getStoreId());
        }

        if ($connected) {
            if ($this->checkAutoInstall()) {
                $this->configuration->setAutoInstall();
            }

            if ($showNotification) {
                $this->echoConnectProgress(' Done<br/>', $this->httpResponse);
                $this->notificationHelper->setNotification(
                    SeNotification::TYPE_NOTICE,
                    __('Searchanise'),
                    __('Congratulations, you\'ve just connected to Searchanise')
                );
            }
        }

        $this->loggerHelper->log("ApiSeHelper::signup() Finished", ['status' => $connected], SeLogger::TYPE_DEBUG);

        return $connected;
    }

    /**
     * Set current output response
     *
     * @param HttpResponse $response
     */
    public function setHttpResponse(HttpResponse $response = null)
    {
        $this->httpResponse = $response;
        $this->loggerHelper->setResponseContext($response);

        return $this;
    }

    /**
     * Adds progress to response
     *
     * @param string       $text
     * @param HttpResponse $response
     */
    public function echoConnectProgress($text, HttpResponse $response = null)
    {
        if (!empty($response) && !empty($text)) {
            $response->appendBody($text);
        }
    }

    /**
     * Send addon status
     *
     * @param string     $status   Addons status (enabled/disabled/deleted)
     * @param StoreModel $curStore Current store
     *
     * @return bool
     */
    public function sendAddonStatusRequest($status = self::SE_ADDON_STATUS_ENABLED, StoreModel $curStore = null)
    {
        $stores = !empty($curStore) ? [$curStore] : $this->getStores();

        if (!empty($stores)) {
            foreach ($stores as $store) {
                $privateKey = $this->getPrivateKey($store->getStoreId());
                $this->sendRequest('/api/state/update/json', $privateKey, ['addon_status' => $status], true);
            }

            return true;
        }

        return false;
    }

    /**
     * Send Searchanise request
     *
     * @param string $urlPart    Searchanise url path
     * @param string $privateKey Private key
     * @param array  $data       Data for send
     * @param bool   $onlyHttp   If true, https url will be used
     *
     * @return bool|array
     */
    public function sendRequest($urlPart, $privateKey, array $data, $onlyHttp = true)
    {
        $result = false;

        if (!empty($privateKey)) {
            $params = ['private_key' => $privateKey] + $data;

            list($h, $body) = $this->httpRequest(
                \Zend_Http_Client::POST,
                $this->getServiceUrl($onlyHttp) . $urlPart,
                $params,
                [],
                [],
                $this->configuration->getRequestTimeout()
            );

            if ($body) {
                $result = $this->parseResponse($body, false);
            }

            $this->configuration->setLastRequest($this->getTime());
        }

        return $result;
    }

    /**
     * Send http request
     *
     * @param  string $method       Method name
     * @param  string $url          Host url
     * @param  array  $data         Url parameters
     * @param  array  $cookies      Cookies for send
     * @param  array  $basicAuth    Basic http authorization data
     * @param  int    $timeout      Timeout value
     * @param  int    $maxredirects Max redirects value
     *
     * @return array
     */
    public function httpRequest(
        $method = \Zend_Http_Client::POST,
        $url = '',
        $data = [],
        $cookies = [],
        $basicAuth = [],
        $timeout = 1,
        $maxredirects = 5
    ) {
        $this->loggerHelper->log('===== Http Request =====', [
            'method'        => $method,
            'url'           => $url,
            'data'          => $data,
            'cookies'       => $cookies,
            'basicAuth'     => $basicAuth,
            'timeout'       => $timeout,
            'maxRedirects'  => $maxredirects,
        ], SeLogger::TYPE_DEBUG);

        $requestStartTime = microtime(true);
        $responseHeaders = [];
        $responseBody = '';
        $client = new \Zend_Http_Client();
        $client->setUri($url);

        $client->setConfig([
            'httpversion'   => \Zend_Http_Client::HTTP_0,
            'maxredirects'  => $maxredirects,
            'timeout'       => $timeout,
        ]);

        if ($method == \Zend_Http_Client::GET) {
            $client->setParameterGet($data);
        } elseif ($method == \Zend_Http_Client::POST) {
            $client->setParameterPost($data);
        }

        $response = false;
        try {
            $response = $client->request($method);
            $responseBody = $response->getBody();
            $responseHeaders = $response->getHeaders();
            $responseHeaders['status_code'] = $response->getStatus();
            $responseHeaders['status_message'] = $response->getMessage();
        } catch (\Exception $e) {
            $this->loggerHelper->log($e->getMessage());
            $this->loggerHelper->log(
                '===== Response Error =====',
                ['response' => $response],
                SeLogger::TYPE_DEBUG
            );
        }

        $requestEndTime = microtime(true);

        $this->loggerHelper->log(
            '===== Response Body =====',
            [
                'body' => $responseBody,
                'time' => sprintf('%0.2f', $requestEndTime - $requestStartTime),
            ],
            SeLogger::TYPE_DEBUG
        );

        return [$responseHeaders, $responseBody];
    }

    /**
     * Parse response from service
     *
     * @param  string $jsonData         Json service response
     * @param bool    $showNotification if true, error notification will be shown
     * @param string  $objectDecodeType Decoded type
     *
     * @return mixed false if errors returned, true if response is ok, object if data was passed in the response
     */
    public function parseResponse($jsonData, $showNotification = false, $objectDecodeType = \Zend_Json::TYPE_ARRAY)
    {
        $result = false;
        $data = false;

        try {
            if (trim($jsonData) === 'CLOSED;' || trim($jsonData) === 'CLOSED') {
                $data = false;
            } else {
                $data = $this->jsonHelper->jsonDecode($jsonData, $objectDecodeType);
            }
        } catch (\Exception $e) {
            if ($objectDecodeType == \Zend_Json::TYPE_ARRAY) {
                return $this->parseResponse($jsonData, $showNotification, \Zend_Json::TYPE_OBJECT);
            }

            $this->loggerHelper->log(
                '===== ParseResponse : jsonDecode =====',
                $jsonData,
                $e->getMessage()
            );
            $data = false;
        }

        if (empty($data)) {
            $result = false;
        } elseif (is_array($data) && !empty($data['errors'])) {
            foreach ($data['errors'] as $err) {
                $this->loggerHelper->log(__("Error: ApiSe::parseResponse(): %1", $err));
                if ($showNotification == true) {
                    $this->notificationHelper->setNotification(
                        SeNotification::TYPE_ERROR,
                        __('Searchanise'),
                        $err
                    );
                }
            }

            $result = false;
        } elseif ($data === 'ok') {
            $result = true;
        } else {
            $result = $data;
        }

        return $result;
    }

    /**
     * Returns private key for store
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getPrivateKey($storeId = null)
    {
        return $this->configuration->getValue(
            Configuration::XML_PATH_PRIVATE_KEY,
            $storeId
        );
    }

    /**
     * Returns all registered private keys
     *
     * @return array
     */
    public function getPrivateKeys()
    {
        $key_ids = [];
        $stores = $this->getStores();

        if (!empty($stores)) {
            foreach ($stores as $store) {
                $key_ids[$store->getId()] = $this->getPrivateKey($store->getId());
            }
        }

        return $key_ids;
    }

    /**
     * Sets private key for store
     *
     * @param string  $privateKey
     * @param int|null $storeId
     */
    public function setPrivateKey($privateKey = null, $storeId = null)
    {
        $store = $this->storeManager->getStore($storeId);

        if (!empty($store)) {
            $this->privateKeySe[$store->getId()] = $privateKey;
            $this->configuration->setValue(Configuration::XML_PATH_PRIVATE_KEY, $privateKey, $store->getId());
        }
    }

    /**
     * Returns parent private key
     *
     * @return string
     */
    public function getParentPrivateKey()
    {
        if (!isset($this->parentPrivateKeySe)) {
            $this->parentPrivateKeySe = $this->configuration->getValue(Configuration::XML_PATH_PARENT_PRIVATE_KEY);
        }

        return $this->parentPrivateKeySe;
    }

    /**
     * Checks if parent private key exits
     *
     * @return bool
     */
    public function checkParentPrivateKey()
    {
        $parentPrivateKey = $this->getParentPrivateKey();

        return !empty($parentPrivateKey);
    }

    /**
     * Set parent private key
     *
     * @param string $parentPrivateKey
     */
    public function setParentPrivateKey($parentPrivateKey = '')
    {
        $this->parentPrivateKeySe = $parentPrivateKey;
        $this->configuration->setValue(Configuration::XML_PATH_PARENT_PRIVATE_KEY, $parentPrivateKey);
    }

    /**
     * Returns current date in format
     *
     * @param string $format Date format
     *
     * @return string
     */
    public function getDate($format = 'Y-m-d H:i:s')
    {
        return date($format);
    }

    /**
     * Returns current timestamp in ms
     *
     * @return int
     */
    public function getTime()
    {
        return time();
    }

    /**
     * Get export statuses
     *
     * @param  StoreModel $store
     *
     * @return array
     */
    public function getExportStatuses(StoreModel $store = null)
    {
        $statuses = [];
        if (isset($store)) {
            $stores = $this->getStores((array)$store->getId());
        } else {
            $stores = $this->getStores();
        }

        if (!empty($stores)) {
            foreach ($stores as $store) {
                $statuses[$store->getId()] = $this->getExportStatus($store->getId());
            }
        }

        return $statuses;
    }

    /**
     * Set export status for store
     *
     * @param string   $exportStatus
     * @param int|null $storeId
     *
     * @return bool
     */
    public function setExportStatus($exportStatus, $storeId = null)
    {
        $this->configuration->setValue(Configuration::XML_PATH_EXPORT_STATUS, $exportStatus, $storeId);
    }

    /**
     * Returns export status for store
     *
     * @param int|null $storeId
     *
     * @return string
     */
    public function getExportStatus($storeId = null)
    {
        return $this->configuration->getValue(Configuration::XML_PATH_EXPORT_STATUS, $storeId);
    }

    /**
     * Check if export status is done for store
     *
     * @param int|null $storeId
     *
     * @return bool
     */
    public function checkExportStatus($storeId = null)
    {
        return $this->getExportStatus($storeId) == self::EXPORT_STATUS_DONE;
    }

    /**
     * Returns current sync mode
     *
     * @return string
     */
    public function getSyncMode()
    {
        return $this->configuration->getValue(Configuration::XML_PATH_SYNC_MODE);
    }

    /**
     * Start import
     *
     * @param int|null $curStoreId   Store identifier
     * @param bool $showNotification If true, status notification will be shown
     *
     * @return bool
     */
    public function queueImport($curStoreId = null, $showNotification = true)
    {
        if (!$this->checkParentPrivateKey()) {
            return false;
        }

        $this->configuration->setNotificationAsyncCompleted(false);

        try {
            // Delete all exist queue, need if exists error
            $this->queueFactory->create()->clearActions($curStoreId);

            $this->queueFactory->create()->addAction(
                Queue::ACT_PREPARE_FULL_IMPORT,
                null,
                $curStoreId
            );
            $this->sendAddonVersion();
        } catch (\Exception $e) {
            $this->loggerHelper->log(__("Error: queueImport(): [%1] %2", $e->getCode(), $e->getMessage()));

            if ($showNotification == true) {
                $this->notificationHelper->setNotification(
                    SeNotification::TYPE_ERROR,
                    __('Searchanise'),
                    __('Unable to start catalog import. Please contact Searchanise <a href="mailto:%1">%2</a> technical support', self::SUPPORT_EMAIL, self::SUPPORT_EMAIL)
                );
            }
            return false;
        }

        if (!empty($curStoreId)) {
            $stores = $this->getStores((array)$curStoreId);
        } else {
            $stores = $this->getStores();
        }

        foreach ($stores as $store) {
            $this->setExportStatus(self::EXPORT_STATUS_QUEUED, $store->getId());
        }

        if ($showNotification == true) {
            $this->notificationHelper->setNotification(
                SeNotification::TYPE_NOTICE,
                __('Searchanise'),
                __('The product catalog is queued for syncing with Searchanise')
            );
        }

        return true;
    }

    /**
     * Show notification message
     *
     * @return boolean
     */
    public function showNotificationAsyncCompleted()
    {
        if (!$this->configuration->checkNotificationAsyncCompleted()) {
            $all_stores_done = true;
            $stores = $this->getStores();

            foreach ($stores as $store) {
                if (!$this->checkExportStatus($store->getId())) {
                    $all_stores_done = false;
                    break;
                }
            }

            if ($all_stores_done) {
                $textNotification = __(
                    'Catalog indexation is complete. Configure Searchanise via the <a href="%1">Admin Panel</a>.',
                    $this->getModuleUrl()
                );
                $this->notificationHelper->setNotification(
                    SeNotification::TYPE_NOTICE,
                    __('Searchanise'),
                    $textNotification
                );
                $this->configuration->setNotificationAsyncCompleted(true);
            }
        }

        return true;
    }

    /**
     * Check if the area is backend
     *
     * @return boolean
     */
    public function getIsAdmin()
    {
        return $this->appState->getAreaCode() == FrontNameResolver::AREA_CODE;
    }

    /**
     * Returns url manager for store
     *
     * @param int $storeId Store identifier
     *
     * @return \Magento\Framework\Url
     */
    public function getStoreUrl($storeId)
    {
        static $storeUrls = [];

        if (!isset($storeUrls[$storeId])) {
            $url = ObjectManager::getInstance()
                ->create(\Magento\Framework\Url::class);
            $url->setData('store', $storeId);
            $storeUrls[$storeId] = $url;
        }

        return $storeUrls[$storeId];
    }

    /**
     * Returns stores
     *
     * @param array $storeIds Stores identifierss
     *
     * @return array
     */
    public function getStores(array $storeIds = [])
    {
        if (empty($storeIds)) {
            $stores = $this->storeManager->getStores();
        } else {
            foreach ($storeIds as $storeId) {
                $store = $this->storeManager->getStore($storeId);

                if (!empty($store)) {
                    $stores[$store->getId()] = $store;
                }
            }
        }

        if (!empty($this->seStoreIds)) {
            foreach ($stores as $storeId => $store) {
                if (!in_array($storeId, $this->seStoreIds)) {
                    unset($stores[$storeId]);
                }
            }
        }

        return $stores;
    }

    /**
     * Return stores by website ids
     *
     * @param array $websiteIds
     *
     * @return array
     */
    public function getStoreByWebsiteIds(array $websiteIds = [])
    {
        $ret = [];

        if (!empty($websiteIds)) {
            if (!is_array($websiteIds)) {
                $websiteIds = [
                    0 => $websiteIds
                ];
            }

            $stores = $this->getStores();

            if (!empty($stores)) {
                foreach ($stores as $k => $store) {
                    $websiteId = $store->getWebsite()->getId();

                    if (in_array($websiteId, $websiteIds)) {
                        $ret[] = $store->getId();
                    }
                }
            }
        }

        return $ret;
    }

    /**
     * Returns stores by website codes
     *
     * @param array $websiteCodes
     *
     * @return array
     */
    public function getStoreByWebsiteCodes(array $websiteCodes = [])
    {
        // ToCheck: deprecated
        $ret = [];

        if (!empty($websiteCodes)) {
            if (!is_array($websiteCodes)) {
                $websiteCodes = [
                    0 => $websiteCodes
                ];
            }

            $stores = $this->getStores();

            if (!empty($stores)) {
                foreach ($stores as $k => $store) {
                    $websiteCode = $store->getWebsite()->getCode();

                    if (in_array($websiteCode, $websiteCodes)) {
                        $ret[] = $store->getId();
                    }
                }
            }
        }

        return $ret;
    }

    /**
     * Check if Searchanise is enabled
     *
     * @param StoreModel $store
     *
     * @return bool
     */
    public function getIsSearchaniseSearchEnabled(StoreModel $store = null)
    {
        if (empty($store)) {
            $store = $this->storeManager->getStore();
        }

        // Check if store is not in excluded store ids
        if (!empty($this->seStoreIds) && !in_array($store->getId(), $this->seStoreIds)) {
            return false;
        }

        // Check if page is allowed for search
        if (
            !in_array($this->httpRequest->getFullActionName(), [
                'catalogsearch_result_index',    // Search result page
                'catalogsearch_advanced_result', // Advanced search result page
            ])
        ) {
            return false;
        }

        return $this->configuration->getIsSearchaniseSearchEnabled();
    }

    /**
     * Check if Searchanise request is valid
     *
     * @param $searchaniseRequest
     * @param StoreModel Store
     *
     * @return bool
     */
    public function checkSearchaniseResult(
        $searchaniseRequest = null,
        StoreModel $store = null
    ) {
        if (empty($store)) {
            $store = $this->storeManager->getStore();
        }

        if (!empty($this->seStoreIds) && !in_array($store->getId(), $this->seStoreIds)) {
            return false;
        }

        $exportStatus = $this->getExportStatus($store->getId());

        if (
            $this->checkStatusModule($store->getId()) == 'Y'
            && in_array($exportStatus, [self::EXPORT_STATUS_DONE, self::EXPORT_STATUS_QUEUED])
            && !empty($searchaniseRequest)
        ) {
            if ($searchaniseRequest === true) {
                return true;
            }

            // TODO: Add check here
            return true;
        }

        return false;
    }

    /**
     * Process Searchanise queue
     *
     * @param bool $flIgnoreProcessing If true, processing elements will be not skipped
     *
     * @return string Processing result
     */
    public function async($flIgnoreProcessing = false)
    {
        $this->loggerHelper->log("===== Async was started =====", SeLogger::TYPE_INFO);
        $this->loggerHelper->log("===== Async: Started =====", SeLogger::TYPE_DEBUG);

        ignore_user_abort(true);
        set_time_limit(0);

        $asyncMemoryLimit = $this->configuration->getAsyncMemoryLimit();

        if (substr(ini_get('memory_limit'), 0, -1) < $asyncMemoryLimit) {
            ini_set('memory_limit', $asyncMemoryLimit . 'M');
        }

        $isProfilerEnabled = MagentoProfiler::isEnabled();

        // Disable profile during processing to prevent memory leak
        if ($isProfilerEnabled) {
            MagentoProfiler::disable();
        }

        $this->echoConnectProgress('.', $this->httpResponse);

        $q = $this->queueFactory->create()->getNextQueue();

        while (!empty($q)) {
            $queryStartTime = microtime(true);
            $dataForSend = [];
            $status = true;
            $statusError = '';

            $this->loggerHelper->log('===== Async: Processing query =====', $q, SeLogger::TYPE_DEBUG);

            try {
                $store = $this->storeManager->getStore($q['store_id']);
            } catch (NoSuchEntityException $e) {
                // Store not found, skip queue
                $this->queueFactory->create()->load($q['queue_id'])->delete();
                $q = [];

                $this->loggerHelper->log(
                    "===== Async: Store id {$q['store_id']} not exists. Query processing skipped =====",
                    SeLogger::TYPE_WARNING
                );

                continue;
            }

            $header = $this->apiProductsHelper->getHeader($store);
            $data = $q['data'];

            if (!empty($data) && $data !== Queue::NOT_DATA) {
                $data = $this->unserialize($data);
            }

            $privateKey = $this->getPrivateKey($store->getId());

            if (empty($privateKey)) {
                $this->queueFactory->create()->load($q['queue_id'])->delete();
                $q = [];

                $this->loggerHelper->log(
                    "===== Async: Private key not exits for store {$store->getId()}. Processing skipped. ====",
                    SeLogger::TYPE_DEBUG
                );

                continue;
            }

            try {
                //Note: $q['started'] can be in future.
                if ($q['status'] == Queue::STATUS_PROCESSING
                    && ($q['started'] + $this->configuration->getMaxProcessingTime() > $this->getTime())
                ) {
                    if (!$flIgnoreProcessing) {
                        // Restore profiler original status
                        if ($isProfilerEnabled) {
                            MagentoProfiler::enable();
                        }

                        return Queue::STATUS_PROCESSING;
                    }
                }

                if (!$flIgnoreProcessing && $q['error_count'] >= $this->configuration->getMaxErrorCount()) {
                    $this->setExportStatus(self::EXPORT_STATUS_SYNC_ERROR, $store->getId());

                    // Restore profiler original status
                    if ($isProfilerEnabled) {
                        MagentoProfiler::enable();
                    }

                    return Queue::STATUS_DISABLED;
                }

                $this->dataHelper->startEmulation($store);
                $this->storeManager->setCurrentStore($store->getId());

                // Set queue to processing state
                $this->queueFactory
                    ->create()
                    ->load($q['queue_id'])
                    ->setData('status', Queue::STATUS_PROCESSING)
                    ->setData('started', $this->getTime())
                    ->save();

                if ($q['action'] == Queue::ACT_PREPARE_FULL_IMPORT) {
                    $this->queueFactory
                        ->create()
                        ->getCollection()
                        ->addFieldToFilter('action', [
                            'neq' => Queue::ACT_PREPARE_FULL_IMPORT
                        ])
                        ->addFilter('store_id', $store->getId())
                        ->load()
                        ->delete();

                        $queueData = [
                            'data'     => Queue::NOT_DATA,
                            'action'   => Queue::ACT_START_FULL_IMPORT,
                            'store_id' => $store->getId(),
                        ];

                        $this->queueFactory
                            ->create()
                            ->setData($queueData)
                            ->save();

                        $queueData = [
                            'data'     => Queue::NOT_DATA,
                            'action'   => Queue::ACT_GET_INFO,
                            'store_id' => $store->getId(),
                        ];

                        $this->queueFactory
                            ->create()
                            ->setData($queueData)
                            ->save();

                        $queueData = [
                            'data'     => Queue::NOT_DATA,
                            'action'   => Queue::ACT_DELETE_FACETS_ALL,
                            'store_id' => $store->getId(),
                        ];

                        $this->queueFactory
                            ->create()
                            ->setData($queueData)
                            ->save();

                        $this->_addTaskByChunk(
                            $store,
                            Queue::ACT_UPDATE_PRODUCTS,
                            true
                        )->_addTaskByChunk(
                            $store,
                            Queue::ACT_UPDATE_CATEGORIES,
                            true
                        )->_addTaskByChunk(
                            $store,
                            Queue::ACT_UPDATE_PAGES,
                            true
                        );

                        $this->echoConnectProgress('.', $this->httpResponse);

                        $queueData = [
                            'data'     => Queue::NOT_DATA,
                            'action'   => Queue::ACT_END_FULL_IMPORT,
                            'store_id' => $store->getId(),
                        ];

                        $this->queueFactory
                            ->create()
                            ->setData($queueData)
                            ->save();

                        $status = true;
                } elseif ($q['action'] == Queue::ACT_START_FULL_IMPORT) {
                    $status = $this->sendRequest(
                        '/api/state/update/json',
                        $privateKey,
                        ['full_import' => self::EXPORT_STATUS_START],
                        true
                    );

                    if ($status == true) {
                        $this->setExportStatus(self::EXPORT_STATUS_PROCESSING, $store->getId());
                    }
                } elseif ($q['action'] == Queue::ACT_GET_INFO) {
                    $params = [];
                    $info = $this->sendRequest('/api/state/info/json', $privateKey, $params, true);

                    if (!empty($info['result_widget_enabled'])) {
                        $this->configuration->setResultsWidgetEnabled(
                            $info['result_widget_enabled'] == 'Y',
                            $store->getId()
                        );
                    }
                } elseif ($q['action'] == Queue::ACT_END_FULL_IMPORT) {
                    $status = $this->sendRequest(
                        '/api/state/update/json',
                        $privateKey,
                        ['full_import' => self::EXPORT_STATUS_DONE],
                        true
                    );

                    if ($status == true) {
                        $this->setExportStatus(self::EXPORT_STATUS_DONE, $store->getId());
                        $this->configuration->setLastResync($this->getTime());
                    }

                } elseif (Queue::isDeleteAllAction($q['action'])) {
                    $type = Queue::getAPITypeByAction($q['action']);

                    if ($type) {
                        $status = $this->sendRequest("/api/{$type}/delete/json", $privateKey, ['all' => true], true);
                    }
                } elseif (Queue::isUpdateAction($q['action'])) {
                    $dataForSend = [];

                    if ($q['action'] == Queue::ACT_UPDATE_PRODUCTS) {
                        $items = $this->apiProductsHelper->generateProductsFeed($data, $store);

                        if (!empty($items)) {
                            $dataForSend = [
                                'header' => $header,
                                'schema' => $this->apiProductsHelper->getSchema($store),
                                'items'  => $items,
                            ];
                        }
                    } elseif ($q['action'] == Queue::ACT_UPDATE_CATEGORIES) {
                        $categories = $this->apiCategoriesHelper->generateCategoriesFeed($data, $store);

                        if (!empty($categories)) {
                            $dataForSend = [
                                'header'     => $header,
                                'categories' => $categories,
                            ];
                        }
                    } elseif ($q['action'] == Queue::ACT_UPDATE_PAGES) {
                        $pages = $this->apiPagesHelper->generatePagesFeed($data, $store);

                        if (!empty($pages)) {
                            $dataForSend = [
                                'header' => $header,
                                'pages'  => $pages,
                            ];
                        }
                    } elseif ($q['action'] == Queue::ACT_UPDATE_ATTRIBUTES) {
                        $dataForSend = [
                            'header' => $header,
                            'schema' => $this->apiProductsHelper->getSchema($store),
                        ];
                    }

                    if (!empty($dataForSend)) {
                        $dataForSend = $this->jsonHelper->jsonEncode($dataForSend);

                        if (function_exists('gzcompress')) {
                            $dataForSend = gzcompress($dataForSend, self::COMPRESS_RATE);
                        }

                        $status = $this->sendRequest('/api/items/update/json', $privateKey, ['data' => $dataForSend], true);
                    }
                } elseif (Queue::isDeleteAction($q['action'])) {
                    $type = Queue::getAPITypeByAction($q['action']);

                    if (!empty($type)) {
                        if ($q['action'] == Queue::ACT_DELETE_PRODUCTS) {
                            // Bulk products delete
                            $dataForSend = ['id' => (array)$data];

                            $status = $this->sendRequest("/api/{$type}/delete/json", $privateKey, $dataForSend, true);

                            $this->echoConnectProgress('.', $this->httpResponse);
                        } else {
                            // Single delete
                            foreach ($data as $itemId) {
                                $dataForSend = [];

                                if ($q['action'] == Queue::ACT_DELETE_FACETS) {
                                    $dataForSend['attribute'] = $itemId;
                                } else {
                                    $dataForSend['id'] = $itemId;
                                }

                                $status = $this->sendRequest("/api/{$type}/delete/json", $privateKey, $dataForSend, true);

                                $this->echoConnectProgress('.', $this->httpResponse);

                                if ($status == false) {
                                    break;
                                }
                            }
                        }
                    }
                } elseif ($q['action'] == Queue::ACT_PHRASE) {
                    if (!empty($data) && is_array($data)) {
                        foreach ($data as $phrase) {
                            $status = $this->sendRequest(
                                '/api/phrases/update/json',
                                $privateKey,
                                ['phrase' => $phrase],
                                true
                            );

                            $this->echoConnectProgress('.', $this->httpResponse);

                            if ($status == false) {
                                break;
                            }
                        }
                    }
                }
            } catch (\Exception $e) {
                $this->loggerHelper->log($e->getMessage(), SeLogger::TYPE_ERROR);
                $this->configuration->setLastResyncError($e->getMessage());
                $this->loggerHelper->log(
                    '===== Query processing error =====',
                    $e->getMessage(),
                    SeLogger::TYPE_ERROR
                );

                $statusError = $e->getMessage();
                $status = false;

            } catch (\Error $e) {
                $errorMessage = $e->getMessage() . ' in ' . $e->getFile() . ' file on ' . $e->getLine() . ' line';

                $this->loggerHelper->log($errorMessage, SeLogger::TYPE_ERROR);
                $this->configuration->setLastResyncError($errorMessage);
                $this->loggerHelper->log(
                    '===== Query processing synax error =====',
                    $errorMessage,
                    SeLogger::TYPE_ERROR
                );

                $statusError = $errorMessage;
                $status = false;
            }

            $this->dataHelper->stopEmulation();

            // Change queue item status
            if ($status == true) {
                $this->queueFactory->create()->load($q['queue_id'])->delete();
                $q = $this->queueFactory->create()->getNextQueue();
            } else {
                $nextStartedTime = ($this->getTime() - $this->configuration->getMaxProcessingTime())
                    + $q['error_count'] * 60;

                $modelQueue = $this->queueFactory->create()->load($q['queue_id']);
                $modelQueue
                    ->setData('status', Queue::STATUS_PROCESSING)
                    ->setData('error_count', $modelQueue->getData('error_count') + 1)
                    ->setData('started', $nextStartedTime)
                    ->save();

                // try later
                throw new \Exception(__('Async error: %1', $statusError));
            }

            $queryEndTime = microtime(true);

            $this->loggerHelper->log(
                '===== Query was process with status =====',
                [
                    'status' => $status,
                    'time'   => sprintf('%0.2f', $queryEndTime - $queryStartTime),
                ],
                SeLogger::TYPE_DEBUG
            );
            $this->echoConnectProgress('.', $this->httpResponse);
        }

        // Restore profiler original status
        if ($isProfilerEnabled) {
            MagentoProfiler::enable();
        }

        $this->configuration->setLastResyncError('');
        $this->loggerHelper->log("==== Async: Ended ====", SeLogger::TYPE_DEBUG);
        $this->loggerHelper->log('Async was processed', SeLogger::TYPE_INFO);

        return self::ASYNC_STATUS_OK;
    }

    /**
     * Sends addon version to Searchanise
     *
     * @return bool
     */
    public function sendAddonVersion()
    {
        $result = false;
        $parentPrivateKey = $this->getParentPrivateKey();

        if (!empty($parentPrivateKey)) {
            $options = $this->getAddonOptions();
            $result = $this->sendRequest('/api/state/update/json', $parentPrivateKey, [
                'addon_version'    => $options['addon_version'],
                'platform_edition' => $options['core_edition'],
                'platform_version' => $options['core_version'],
            ], true);
        }

        return $result;
    }

    /**
     * Check if there is a record in the queue
     *
     * @return bool
     */
    public function checkStartAsync()
    {
        $ret = false;
        $q = $this->queueFactory->create()->getNextQueue();

        if (!empty($q)) {
            //Note: $q['started'] can be in future.
            if ($q['status'] == Queue::STATUS_PROCESSING
                && ($q['started'] + $this->configuration->getMaxProcessingTime() > $this->getTime())
            ) {
                $ret = false;
            } elseif ($q['error_count'] >= $this->configuration->getMaxErrorCount()) {
                if ($q['store_id']) {
                    $store = $this->storeManager->getStore($q['store_id']);
                } else {
                    $store = null;
                }

                $statuses = $this->getExportStatuses($store);

                if (!empty($statuses)) {
                    foreach ($statuses as $statusKey => $status) {
                        if ($status != self::EXPORT_STATUS_SYNC_ERROR) {
                            if ($store) {
                                $this->setExportStatus(self::EXPORT_STATUS_SYNC_ERROR, $store->getId());
                            } else {
                                $stores = $this->getStores();
                                foreach ($stores as $stKey => $_st) {
                                    $this->setExportStatus(self::EXPORT_STATUS_SYNC_ERROR, $_st->getId());
                                }
                                break;
                            }
                        }
                    }
                }

                $ret = false;
            } else {
                $ret = true;
            }
        }

        return $ret;
    }

    /**
     * Build query from the array
     *
     * @param  array  $array  data to build query from
     * @param  string $query  part of query to attach new data
     * @param  string $prefix prefix
     *
     * @return string well-formed query
     */
    public function buildQuery(array $array, $query = '', $prefix = '')
    {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                $query = $this->buildQuery($v, $query, rawurlencode(empty($prefix) ? "$k" : $prefix . "[$k]"));
            } else {
                $query .= (!empty($query) ? '&' : '')
                    . (empty($prefix) ? $k : $prefix . rawurlencode("[$k]")). '=' . rawurlencode($v);
            }
        }

        return $query;
    }

    /**
     * Adds chunk for further processing
     *
     * @param  StoreModel $store
     * @param  string                     $action
     * @param  string                     $isOnlyActive
     */
    private function _addTaskByChunk(
        StoreModel $store,
        $action = Queue::ACT_UPDATE_PRODUCTS,
        $isOnlyActive = false
    ) {
        $i = 0;
        $step = 50;
        $start = 0;
        $max = 0;

        if ($action == Queue::ACT_UPDATE_PRODUCTS) {
            $step = $this->configuration->getProductsPerPass() * 50;
            list($start, $max) = $this->apiProductsHelper->getMinMaxProductId($store);
        } elseif ($action == Queue::ACT_UPDATE_CATEGORIES) {
            $step = $this->configuration->getCategoriesPerPass() * 50;
            list($start, $max) = $this->apiCategoriesHelper->getMinMaxCategoryId($store);
        } elseif ($action == Queue::ACT_UPDATE_PAGES) {
            $step = $this->configuration->getPagesPerPass() * 50;
            list($start, $max) = $this->apiPagesHelper->getMinMaxPageId($store);
        }

        do {
            $end = $start + $step;
            $chunkItemIds = null;

            if ($action == Queue::ACT_UPDATE_PRODUCTS) {
                $chunkItemIds = $this->apiProductsHelper->getProductIdsFromRange(
                    $start,
                    $end,
                    $step,
                    $store,
                    $isOnlyActive
                );
            } elseif ($action == Queue::ACT_UPDATE_CATEGORIES) {
                $chunkItemIds = $this->apiCategoriesHelper->getCategoryIdsFromRange(
                    $start,
                    $end,
                    $step,
                    $store
                );
            } elseif ($action == Queue::ACT_UPDATE_PAGES) {
                $chunkItemIds = $this->apiPagesHelper->getPageIdsFromRange(
                    $start,
                    $end,
                    $step,
                    $store
                );
            }

            $start = $end + 1;

            if (empty($chunkItemIds)) {
                continue;
            }

            $chunkItemIds = array_chunk($chunkItemIds, $this->configuration->getProductsPerPass());

            foreach ($chunkItemIds as $itemIds) {
                $queueData = [
                    'data'     => $this->serialize($itemIds),
                    'action'   => $action,
                    'store_id' => $store->getId(),
                ];

                $this->queueFactory->create()->setData($queueData)->save();

                // It is necessary for save memory.
                unset($_result);
                unset($_data);
                unset($queueData);
            }
        } while ($end <= $max);

        $this->loggerHelper->log("===== Async: _addTaskByChunk =====", [
            'action'      => $action,
            'start'       => $start,
            'max'         => $max,
            'step'        => $step,
            'store_id'    => $store->getId(),
            'chunkItemIds' => count($chunkItemIds),
        ], SeLogger::TYPE_DEBUG);

        return $this;
    }

    /**
     * Serialize wrapper
     *
     * @param mixed $data
     *
     * @return string
     */
    public function serialize($data)
    {
        // Use default serializer
        try {
            return ObjectManager::getInstance()
                ->create('Magento\Framework\Serialize\SerializerInterface')
                ->serialize($data);
        } catch (\Exception $e) {
            // Error occurs, perhaps class doesn't exist
        }

        if (class_exists('Zend_Serializer', false)) {
            // Old version of Zend framwork
            return \Zend_Serializer::serialize($data);
        }

        // Use new zend framework
        return \Zend\Serializer\Serializer::serialize($data);
    }

    /**
     * Unserialize wrapper
     *
     * @param string $data
     *
     * @return mixed
     */
    public function unserialize($data)
    {
        // Use default serializer
        try {
            return ObjectManager::getInstance()
                ->create('Magento\Framework\Serialize\SerializerInterface')
                ->unserialize($data);
        } catch (\Exception $e) {
            // Error occurs, perhaps class doesn't exist
        }

        if (class_exists('Zend_Serializer', false)) {
            // Old version of Zend framwork
            return \Zend_Serializer::unserialize($data);
        }

        // Use new zend framework
        return \Zend\Serializer\Serializer::unserialize($data);
    }

    /**
     * Test if Searchanise server is available
     *
     * @param bool $showNotification Flag to show failed notice in admin
     * @param int  $timeout          Connection test timeout
     *
     * @return bool
     */
    public function checkConnections($showNotification = true, $timeout = self::TEST_CONNECTION_TIMEOUT)
    {
        list($h, $body) = $this->httpRequest(
            \Zend_Http_Client::GET,
            $this->getServiceUrl(true) . '/api/test',
            [],
            [],
            [],
            $timeout
        );

        $result = !empty($body) && $body == 'OK';

        if (!$result && $showNotification) {
            $this->notificationHelper->setNotification(
                SeNotification::TYPE_WARNING,
                __('Searchanise'),
                __(
                    'There is no connection to Searchanise server! For Searchanise to work properly, the store server must be able to access %1. Please contact Searchanise <a href="mailto: %2">%3</a> technical support or your system administrator.',
                    $this->getServiceUrl(true),
                    self::SUPPORT_EMAIL,
                    self::SUPPORT_EMAIL
                )
            );
        }

        return $result;
    }

    /**
     * Check Searchanise queue
     *
     * @param bool $showNotification Flag to show admin notice if test failed
     *
     * @return bool
     */
    public function checkQueue($showNotification = true)
    {
        $status = true;
        $q = $this->queueFactory->create()->getNextQueue();

        if (empty($q)) {
            return true;
        }

        if ($q['error_count'] >= $this->configuration->getMaxErrorCount()) {
            // Maximum attemps reached
            $status = false;
        } elseif ($q['status'] == Queue::STATUS_PROCESSING && ($q['started'] + 3600 < time())) {
            // Queue item processed more than one hour
            $status = false;
        }

        if ($showNotification && !$status) {
            $this->notificationHelper->setNotification(
                SeNotification::TYPE_WARNING,
                __('Searchanise'),
                __(
                    'We found an issue with the export of changes in your store catalog. To resolve the issue please contact the Searchanise <a href="mailto: %1">%2</a> technical support.',
                    self::SUPPORT_EMAIL,
                    self::SUPPORT_EMAIL
                )
            );
        }

        return $status;
    }

    /**
     * Checks if current search engine is supports full text search
     *
     * @param StoreModel $store            Store for check
     * @param bool       $showNotification If true, notification will be shown
     *
     * @return bool
     */
    public function checkCurrentEngine(StoreModel $store = null, $showNotification = true)
    {
        if (empty($store)) {
            $store = $this->storeManager->getStore();
        }

        if ($this->configuration->getIsResultsWidgetEnabled($store->getId())) {
            return true;
        }

        $check = in_array($this->configuration->getCurrentEngine($store), [
            'mysql',
            'elasticsearch7',
        ]);

        $neededEngine = version_compare($this->getMagentoVersion(), '2.4.0', '<') ? 'mysql' : 'elasticsearch7';

        if (!$check && $showNotification) {
            $this->notificationHelper->setNotification(
                SeNotification::TYPE_WARNING,
                __('Searchanise'),
                __(
                    'It looks like the current search engine is different from <b>%1</b>. Searchanise can display own search results on the catalog search and advanced search pages only if the <b>%2</b> search engine is selected. If the other search engine is selected, the search results displaying on these page may be different from Searchanise one. So, it recommends to switch current catalog search engine to <b>%3</b> on the <b>Stores → Configuration → Catalog</b> page or use <b>Search results widget</b> instead of fulltext search. For more details please contact the Searchanise team at <a href="mailto: %4">%5</a>.',
                    $neededEngine,
                    $neededEngine,
                    $neededEngine,
                    self::SUPPORT_EMAIL,
                    self::SUPPORT_EMAIL
                )
            );
        }

        return $check;
    }

    /**
     * Checks incompatibility extensions
     *
     * @param bool $showNotification If true, notification will be shown
     *
     * @return bool
     */
    public function checkIncompatibilities($showNotification = true)
    {
        if (version_compare($this->getMagentoVersion(), '2.4.0', '>=')) {
            return true;
        }

        $objectManager = ObjectManager::getInstance();

        // Check collections
        $fullTextCollection        = $objectManager->get(\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection::class);
        $searchCollection          = $objectManager->get(\Magento\CatalogSearch\Model\ResourceModel\Fulltext\SearchCollection::class);
        $collectionFactory         = $objectManager->get(\Magento\CatalogSearch\Model\ResourceModel\Fulltext\CollectionFactory::class);
        $advancedCollectionFactory = $objectManager->get(\Magento\CatalogSearch\Model\ResourceModel\Advanced\CollectionFactory::class);

        $fullTextCollectionOverrided = !in_array(get_class($fullTextCollection), [
            'Searchanise\SearchAutocomplete\Model\ResourceModel\Product\Fulltext\Collection\Interceptor',
            'Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\Interceptor',
        ]);

        $searchCollectionOverrided = !in_array(get_class($searchCollection), [
            'Searchanise\SearchAutocomplete\Model\ResourceModel\Product\Fulltext\Collection\Interceptor',
            'Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\Interceptor',
        ]);

        $collectionFactoryOverrided = !in_array(get_class($collectionFactory->create()), [
            'Searchanise\SearchAutocomplete\Model\ResourceModel\Product\Fulltext\Collection\Interceptor',
            'Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection\Interceptor',
        ]);

        $advancedCollectionFactoryOverrided = !in_array(get_class($advancedCollectionFactory->create()), [
            'Searchanise\SearchAutocomplete\Model\ResourceModel\Product\Fulltext\Collection\Interceptor',
            'Searchanise\SearchAutocomplete\Model\ResourceModel\Product\Advanced\Collection\Interceptor',
            'Magento\CatalogSearch\Model\ResourceModel\Advanced\Collection\Interceptor',
        ]);

        if ($fullTextCollectionOverrided || $searchCollectionOverrided || $collectionFactoryOverrided || $advancedCollectionFactoryOverrided) {
            if ($showNotification) {
                if ($fullTextCollectionOverrided) {
                    $parts = explode("\\", get_class($fullTextCollection));
                } elseif ($searchCollectionOverrided) {
                    $parts = explode("\\", get_class($searchCollection));
                } elseif ($collectionFactoryOverrided) {
                    $parts = explode("\\", get_class($collectionFactory->create()));
                } elseif ($advancedCollectionFactoryOverrided) {
                    $parts = explode("\\", get_class($advancedCollectionFactory->create()));
                }

                $moduleId = implode('_', array_slice($parts, 0, 2));
                $searchaniseDirectory = dirname(__DIR__);

                $this->notificationHelper->setNotification(
                    SeNotification::TYPE_WARNING,
                    __('Searchanise'),
                    __(
                        'It looks like default search engine code have been overridden by <b>%1</b>. Searchanise uses the same code to display search results and these overrides may be incompatible with each other. To avoid possible issues, we recommend to disable overriding Searchanise code and use our <b>Search results widget</b>. Click <a href="#" onclick="javascript: jQuery(\'#se_override_instruction\').toggle();">here</a> to see the instruction how to do that. For more details please contact the Searchanise team at <a href="mailto: %2">%3</a>.
                        <ul id="se_override_instruction" style="display:none">
                            <li>Go to the <b>%4/etc</b> directory on your server.</li>
                            <li>Replace the <b>di.xml</b> file with the <b>di_without_search.xml</b> one.</li>
                            <li>Clear cache on the <b>System → Cache management</b> page.</li>
                            <li>Enable <b>Search results widget</b> on this page.</li>
                        </ul>',
                        $moduleId,
                        self::SUPPORT_EMAIL,
                        self::SUPPORT_EMAIL,
                        $searchaniseDirectory
                    )
                );
            }

            return false;
        }

        return true;
    }
}
