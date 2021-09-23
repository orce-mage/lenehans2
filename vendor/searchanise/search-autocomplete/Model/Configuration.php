<?php

namespace Searchanise\SearchAutocomplete\Model;

use Magento\Framework\App\Cache\Type\Config as CacheConfig;
use Magento\CatalogInventory\Model\Configuration as CatalogInventoryConfiguration;
use Magento\Store\Model\Store;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\Storage\WriterInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\ObjectManager;

/**
 * Configuration class
 */
class Configuration
{
    const SCOPE_DEFAULT = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;
    const SCOPE_STORE_READ = ScopeInterface::SCOPE_STORE;
    const SCOPE_STORE_WRITE = ScopeInterface::SCOPE_STORES;

    const SYNC_MODE_REALTIME = 'realtime';
    const SYNC_MODE_PERIODIC = 'periodic';
    const SYNC_MODE_MANUAL   = 'manual';

    const ATTR_SHORT_DESCRIPTION = 'short_description';
    const ATTR_DESCRIPTION = 'description';

    const XML_PATH_API_KEY = 'searchanise/searchanise_general/api_key';
    const XML_PATH_SERVICE_URL = 'searchanise/searchanise_general/service_url';
    const XML_PATH_SEARCH_INPUT_SELECTOR = 'searchanise/searchanise_general/search_input_selector';
    const XML_PATH_AUTO_INSTALL_INSTALLED = 'searchanise/searchanise_general/auto_install_initiated';
    const XML_PATH_PRIVATE_KEY = 'searchanise/searchanise_general/private_key';
    const XML_PATH_PARENT_PRIVATE_KEY = 'searchanise/searchanise_general/parent_private_key';
    const XML_PATH_REQUEST_TIMEOUT = 'searchanise/searchanise_general/request_timeout';
    const XML_PATH_SERVER_VERSION = 'searchanise/searchanise_general/server_version';
    const XML_PATH_LAST_REQUEST = 'searchanise/searchanise_general/last_request';
    const XML_PATH_LAST_RESYNC = 'searchanise/searchanise_general/last_resync';
    const XML_PATH_LAST_RESYNC_ERR = 'searchanise/searchanise_general/last_resync_err';
    const XML_PATH_EXPORT_STATUS = 'searchanise/searchanise_general/export_status';
    const XML_PATH_CRON_ASYNC_ENABLED = 'searchanise/searchanise_general/cron_async_enabled';
    const XML_PATH_AJAX_ASYNC_ENABLED = 'searchanise/searchanise_general/ajax_async_enabled';
    const XML_PATH_OBJECT_ASYNC_ENABLED = 'searchanise/searchanise_general/object_async_enabled';
    const XML_PATH_SYNC_MODE = 'searchanise/searchanise_general/sync_mode';
    const XML_PATH_ASYNC_MEMORY_LIMIT = 'searchanise/searchanise_general/async_memory_limit';
    const XML_PATH_MAX_PROCESSING_TIME = 'searchanise/searchanise_general/max_processing_time';
    const XML_PATH_MAX_ERROR_COUNT = 'searchanise/searchanise_general/max_error_count';
    const XML_PATH_MAX_SEARCH_REQUEST_LENGTH = 'searchanise/searchanise_general/max_search_request_length';
    const XML_PATH_SEARCH_TIMEOUT = 'searchanise/searchanise_general/search_timeout';
    const XML_PATH_PRODUCTS_PER_PASS = 'searchanise/searchanise_general/products_per_pass';
    const XML_PATH_CATEGORIES_PER_PASS = 'searchanise/searchanise_general/categories_per_pass';
    const XML_PATH_PAGES_PER_PASS = 'searchanise/searchanise_general/pages_per_pass';
    const XML_PATH_NOTIFICATION_ASYNC_COMPLETED = 'searchanise/searchanise_general/notification_async_completed';
    const XML_PATH_RESULTS_WIDGET_ENABLED = 'searchanise/searchanise_general/results_widget_enabled';
    const XML_PATH_INSTALLED_MODULE_VERSION = 'searchanise/searchanise_general/installed_module_version';
    const XML_PATH_USE_DIRECT_IMAGES_LINKS = 'searchanise/searchanise_general/use_direct_image_links';
    const XML_PATH_DESCRIPTION_ATTR = 'searchanise/searchanise_general/summary_attr';
    const XML_PATH_RENDER_PAGE_TEMPLATE = 'searchanise/searchanise_general/render_page_template';
    const XML_PATH_ENABLE_SEARCHANISE_SEARCH = 'searchanise/searchanise_general/enabled_searchanise_search';
    const XML_PATH_ENABLE_DEBUG = 'searchanise/searchanise_general/enable_debug';
    const XML_PATH_INDEX_ENABLED = 'searchanise/searchanise_general/index_enabled';
    const XML_PATH_CUSTOMER_USERGROUPS_ENABLED = 'searchanise/searchanise_general/enable_customer_usergroups';
    const XML_PATH_MAX_PAGE_SIZE = 'searchanise/searchanise_general/max_page_size';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var WriterInterface
     */
    private $writeInterface;

    /**
     * @var CacheInterface
     */
    private $cache;

    private static $configCache = [];

    private static $config_information = [
        self::XML_PATH_SERVICE_URL,
        self::XML_PATH_SEARCH_INPUT_SELECTOR,
        self::XML_PATH_REQUEST_TIMEOUT,
        self::XML_PATH_SERVER_VERSION,
        self::XML_PATH_CRON_ASYNC_ENABLED,
        self::XML_PATH_AJAX_ASYNC_ENABLED,
        self::XML_PATH_OBJECT_ASYNC_ENABLED,
        self::XML_PATH_SYNC_MODE,
        self::XML_PATH_ASYNC_MEMORY_LIMIT,
        self::XML_PATH_MAX_PROCESSING_TIME,
        self::XML_PATH_MAX_ERROR_COUNT,
        self::XML_PATH_MAX_SEARCH_REQUEST_LENGTH,
        self::XML_PATH_SEARCH_TIMEOUT,
        self::XML_PATH_PRODUCTS_PER_PASS,
        self::XML_PATH_CATEGORIES_PER_PASS,
        self::XML_PATH_PAGES_PER_PASS,
        self::XML_PATH_RESULTS_WIDGET_ENABLED,
        self::XML_PATH_USE_DIRECT_IMAGES_LINKS,
        self::XML_PATH_DESCRIPTION_ATTR,
        self::XML_PATH_RENDER_PAGE_TEMPLATE,
        self::XML_PATH_ENABLE_SEARCHANISE_SEARCH,
        self::XML_PATH_ENABLE_DEBUG,
        self::XML_PATH_INDEX_ENABLED,
        self::XML_PATH_CUSTOMER_USERGROUPS_ENABLED,
        self::XML_PATH_MAX_PAGE_SIZE,
        'catalog/seo/category_url_suffix',
        'catalog/seo/product_url_suffix',
    ];

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        WriterInterface $writeInteface,
        CacheInterface $cache
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->writeInterface = $writeInteface;
        $this->cache = $cache;
    }

    /**
     * Returns settings value
     *
     * @param string   $xml_path xml path to config value
     * @param null|int $store_id Value or default value
     * @param mixed    $default  Default value
     *
     * @return mixed
     */
    public function getValue($xml_path, int $store_id = null, $defaul = '')
    {
        $value = $defaul;

        if (!empty($xml_path)) {
            if (empty($store_id)) {
                $value = isset(self::$configCache[0][$xml_path])
                    ? self::$configCache[0][$xml_path]
                    : $this->scopeConfig->getValue($xml_path, self::SCOPE_DEFAULT);
            } else {
                $value = isset(self::$configCache[$store_id][$xml_path])
                    ? self::$configCache[$store_id][$xml_path]
                    : $this->scopeConfig->getValue($xml_path, self::SCOPE_STORE_READ, $store_id);
            }
        }

        return $value != null ? $value : $defaul;
    }

    /**
     * Set a new value for config
     *
     * @param string     $xml_path XML path to config value
     * @param mixed      $value    The new value
     * @param null|int   $store_id Store identifier
     */
    public function setValue($xml_path, $value, int $store_id = null)
    {
        if (empty($store_id)) {
            $this->writeInterface->save($xml_path, $value);
            self::$configCache[0][$xml_path] = $value;
        } else {
            $this->writeInterface->save($xml_path, $value, self::SCOPE_STORE_WRITE, $store_id);
            self::$configCache[$store_id][$xml_path] = $value;
        }

        $this->cache->clean([CacheConfig::CACHE_TAG]);
    }

    /**
     * Set if Searchanise search result is enabled
     *
     * @param bool $value
     * @param int  $storeId
     *
     * @return bool
     */
    public function setResultsWidgetEnabled($value, int $storeId = null)
    {
        $this->setValue(self::XML_PATH_RESULTS_WIDGET_ENABLED, $value ? 1 : 0, $storeId);

        return true;
    }

    /**
     * Returns if Searchanise search result is enabled
     *
     * @param int $storeId
     *
     * @return bool
     */
    public function getIsResultsWidgetEnabled(int $storeId = null)
    {
        return $this->getValue(self::XML_PATH_RESULTS_WIDGET_ENABLED, $storeId) == 1;
    }

    public function getMaxSearchRequestLength()
    {
        return (int)$this->getValue(self::XML_PATH_MAX_SEARCH_REQUEST_LENGTH);
    }

    public function getSearchTimeout()
    {
        return (int)$this->getValue(self::XML_PATH_SEARCH_TIMEOUT);
    }

    public function getProductsPerPass()
    {
        return (int)$this->getValue(self::XML_PATH_PRODUCTS_PER_PASS);
    }

    public function getCategoriesPerPass()
    {
        return (int)$this->getValue(self::XML_PATH_CATEGORIES_PER_PASS);
    }

    public function getPagesPerPass()
    {
        return (int)$this->getValue(self::XML_PATH_PAGES_PER_PASS);
    }

    /**
     * Check if notification async comlpeted is enabled
     *
     * @return boolean
     */
    public function checkNotificationAsyncCompleted()
    {
        return $this->getValue(self::XML_PATH_NOTIFICATION_ASYNC_COMPLETED) == 1;
    }

    /**
     * Set notification async comlpeted
     *
     * @param boolean $value
     *
     * @return boolean
     */
    public function setNotificationAsyncCompleted(bool $value)
    {
        $this->setValue(self::XML_PATH_NOTIFICATION_ASYNC_COMPLETED, $value ? 1 : 0);

        return true;
    }

    /**
     * Set last resync date
     *
     * @param string $value
     *
     * @return bool
     */
    public function setLastResync($value = null)
    {
        $this->setValue(self::XML_PATH_LAST_RESYNC, $value);

        return true;
    }

    /**
     * Get last resync date
     *
     * @return timestamp
     */
    public function getLastResync()
    {
        return $this->getValue(self::XML_PATH_LAST_RESYNC, null, '');
    }

    /**
     * Sets resync error
     *
     * @param string $value Last error
     *
     * @return bool
     */
    public function setLastResyncError($value = '')
    {
        $this->setValue(self::XML_PATH_LAST_RESYNC_ERR, $value);

        return true;
    }

    /**
     * Returns last resync error
     *
     * @return string
     */
    public function getLastResyncError()
    {
        return $this->getValue(self::XML_PATH_LAST_RESYNC_ERR, null, '');
    }

    /**
     * Set last request date
     *
     * @param  timestamp $value
     *
     * @return bool
     */
    public function setLastRequest($value = null)
    {
        $this->setValue(self::XML_PATH_LAST_REQUEST, $value);

        return true;
    }

    /**
     * Get last request date
     *
     * @return timestamp
     */
    public function getLastRequest()
    {
        return $this->getValue(self::XML_PATH_LAST_REQUEST, null, '');
    }

    /**
     * Get current module version
     *
     * @return string
     */
    public function getInsalledModuleVersion()
    {
        return $this->getValue(self::XML_PATH_INSTALLED_MODULE_VERSION);
    }

    /**
     * Set current module version
     *
     * @param  string $value
     *
     * @return boolean
     */
    public function setInsalledModuleVersion($value = null)
    {
        $this->setValue(self::XML_PATH_INSTALLED_MODULE_VERSION, $value);

        return true;
    }

    public function checkAutoInstall()
    {
        return $this->getValue(self::XML_PATH_AUTO_INSTALL_INSTALLED) != 1;
    }

    public function setAutoInstall($value = true)
    {
        $this->setValue(self::XML_PATH_AUTO_INSTALL_INSTALLED, $value ? 1 : 0);
    }

    public function getServerVersion()
    {
        return $this->getValue(self::XML_PATH_SERVER_VERSION);
    }

    public function getIsSearchaniseSearchEnabled()
    {
        return $this->getValue(self::XML_PATH_ENABLE_SEARCHANISE_SEARCH);
    }

    public function getMaxProcessingTime()
    {
        return (int)$this->getValue(self::XML_PATH_MAX_PROCESSING_TIME);
    }

    public function getMaxErrorCount()
    {
        return (int)$this->getValue(self::XML_PATH_MAX_ERROR_COUNT);
    }

    public function getIsRealtimeSyncMode()
    {
        return $this->getValue(self::XML_PATH_SYNC_MODE) == self::SYNC_MODE_REALTIME;
    }

    public function getIsPeriodicSyncMode()
    {
        return $this->getValue(self::XML_PATH_SYNC_MODE) == self::SYNC_MODE_PERIODIC;
    }

    public function getIsManualSyncMode()
    {
        return $this->getValue(self::XML_PATH_SYNC_MODE) == self::SYNC_MODE_MANUAL;
    }

    public function getIsUseDirectImagesLinks()
    {
        return $this->getValue(self::XML_PATH_USE_DIRECT_IMAGES_LINKS) == 1;
    }

    public function getRequestTimeout()
    {
        return (int)$this->getValue(self::XML_PATH_REQUEST_TIMEOUT);
    }

    public function getAsyncMemoryLimit()
    {
        return (int)$this->getValue(self::XML_PATH_ASYNC_MEMORY_LIMIT);
    }

    public function getSummaryAttr()
    {
        $attr = $this->getValue(self::XML_PATH_DESCRIPTION_ATTR);
        return !empty($attr) ? $attr : self::ATTR_SHORT_DESCRIPTION;
    }

    public function getIsRenderPageTemplateEnabled()
    {
        return $this->getValue(self::XML_PATH_RENDER_PAGE_TEMPLATE) == 1;
    }

    public function getIsDebugEnabled()
    {
        return $this->getValue(self::XML_PATH_ENABLE_DEBUG) == 1;
    }

    public function getIsIndexEnabled()
    {
        return $this->getValue(self::XML_PATH_INDEX_ENABLED) == 1;
    }

    public function getIsCustomerUsergroupsEnabled()
    {
        return $this->getValue(self::XML_PATH_CUSTOMER_USERGROUPS_ENABLED) == 1;
    }

    public function getMaxPageSize()
    {
        return $this->getValue(self::XML_PATH_MAX_PAGE_SIZE);
    }

    public function getIsShowOutOfStockProducts()
    {
        return $this->getValue(CatalogInventoryConfiguration::XML_PATH_SHOW_OUT_OF_STOCK);
    }

    public function getIsUseSecureUrlsInFrontend()
    {
        return $this->getValue(Store::XML_PATH_SECURE_IN_FRONTEND) == 1;
    }

    /**
     * Returns current search engine
     *
     * @return string
     */
    public function getCurrentEngine()
    {
        $engineResolver = $this->getEngineResolver();

        if ($engineResolver) {
            return $engineResolver ? $engineResolver->getCurrentSearchEngine() : '';
        } else {
            // Magento 2.0.x
            return $this->getValue(\Magento\CatalogSearch\Model\ResourceModel\EngineProvider::CONFIG_ENGINE_PATH, null, '');
        }
    }

    public function getFullStoreConfig($store_id = null)
    {
        $result = [];

        foreach (self::$config_information as $path) {
            $result[$path] = $this->getValue($path, $store_id);
        }

        return $result;
    }

    private function getEngineResolver()
    {
        $engineResolver = null;

        try {
            // Magento > 2.3.x
            $engineResolver = ObjectManager::getInstance()->get('Magento\Framework\Search\EngineResolverInterface');
        } catch (\Exception $e) {
            // Magento 2.1.x, 2.2.
            try {
                $engineResolver = ObjectManager::getInstance()->get('Magento\Search\Model\EngineResolver');
            } catch (\Exception $e) {
                return null;
            }
        }

        return $engineResolver;
    }
}
