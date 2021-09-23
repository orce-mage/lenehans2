<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-cache-warmer
 * @version   1.6.1
 * @copyright Copyright (C) 2021 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\CacheWarmer\Model;

use Magento\Config\Model\ResourceModel\Config as ConfigWriter;
use Magento\Framework\App\Cache\StateInterface as CacheStateInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Api\Service\WarmerServiceInterface;
use Mirasvit\CacheWarmer\Helper\Serializer;
use Magento\Framework\App\Cache\TypeListInterface;

class Config
{
    const DATA_VERSION = 1;

    const PERFORMANCE_CUSTOM = 0;
    const PERFORMANCE_LOW    = 1;
    const PERFORMANCE_MEDIUM = 2;
    const PERFORMANCE_HIGH   = 3;

    private $scopeConfig;

    private $filesystem;

    private $cacheState;

    private $timezone;

    private $configWriter;

    private $serializer;

    private $cacheTypeList;

    private $request;

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Filesystem $filesystem,
        CacheStateInterface $cacheState,
        TimezoneInterface $timezone,
        ConfigWriter $configWriter,
        TypeListInterface $cacheTypeList,
        Serializer $serializer,
        RequestInterface $request
    ) {
        $this->scopeConfig   = $scopeConfig;
        $this->filesystem    = $filesystem;
        $this->cacheState    = $cacheState;
        $this->timezone      = $timezone;
        $this->configWriter  = $configWriter;
        $this->cacheTypeList = $cacheTypeList;
        $this->serializer    = $serializer;
        $this->request       = $request;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->getValue('cache_warmer/general/enabled', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return bool
     */
    public function isPageCacheEnabled()
    {
        return $this->cacheState->isEnabled(\Magento\PageCache\Model\Cache\Type::TYPE_IDENTIFIER);
    }

    /**
     * @return \DateTime
     */
    public function getDateTime()
    {
        return $this->timezone->date();
    }

    public function isWarmVariations()
    {
        return $this->scopeConfig->getValue(
            'cache_warmer/performance/is_warm_variations',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getWarmThreads()
    {
        switch ($this->getPerformanceLevel()) {
            case self::PERFORMANCE_HIGH:
                return 3;
            case self::PERFORMANCE_MEDIUM:
                return 2;
            case self::PERFORMANCE_LOW:
                return 1;
            default:
                $value = $this->scopeConfig->getValue(
                    'cache_warmer/performance/threads',
                    ScopeInterface::SCOPE_STORE
                );

                $value = (int)$value;

                return $value ? $value : 1;
        }
    }

    /**
     * @return int
     */
    public function getJobRunThreshold()
    {
        switch ($this->getPerformanceLevel()) { // we are using every 2 minutes cron for warming
            case self::PERFORMANCE_HIGH:
                return 115;
            case self::PERFORMANCE_MEDIUM:
                return 90;
            case self::PERFORMANCE_LOW:
                return 60;
            default:
                $warmTime = $this->scopeConfig->getValue(
                    'cache_warmer/performance/job_time',
                    ScopeInterface::SCOPE_STORE
                );

                return $warmTime ?: 100;
        }
    }

    /**
     * 100 - no limits
     * @return int
     */
    public function getServerLoadThreshold()
    {
        switch ($this->getPerformanceLevel()) {
            case self::PERFORMANCE_HIGH:
                return 100;
            case self::PERFORMANCE_MEDIUM:
                return 80;
            case self::PERFORMANCE_LOW:
                return 60;
            default:
                $value = (int)$this->scopeConfig->getValue(
                    'cache_warmer/performance/system_load_limit',
                    ScopeInterface::SCOPE_STORE
                );

                if (!$value) { // backward compatibility
                    $value = (int)$this->scopeConfig->getValue(
                        'cache_warmer/extended/system_load_limit',
                        ScopeInterface::SCOPE_STORE
                    );
                }

                return $value > 25 && $value < 100 ? $value : 100;
        }
    }

    /**
     * @return int
     */
    private function getPerformanceLevel()
    {
        return $this->scopeConfig->getValue('cache_warmer/performance/level');
    }

    /**
     * @param PageInterface $page
     *
     * @return bool
     */
    public function isIgnoredPage(PageInterface $page)
    {
        if (
            $this->isIgnoredUri($page->getUri())
            || $this->isIgnoredPageType($page->getPageType())
            || $this->isIgnoredUserAgent($page->getUserAgent())
        ) {
            return true;
        }

        if (in_array($page->getPageType(), ['cms_noroute_index', 'cms_noroute_index_*'])) {
            return true;
        }

        return false;
    }

    /**
     * @param string $uri
     *
     * @return bool
     */
    public function isIgnoredUri($uri)
    {
        foreach ($this->getIgnoredUriExpressions() as $expression) {
            if (preg_match($expression, $uri)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array
     */
    public function getIgnoredUriExpressions()
    {
        $expressions = [];
        $config      = $this->scopeConfig->getValue(
            'cache_warmer/general/ignored_uri_expressions',
            ScopeInterface::SCOPE_STORE
        );

        $config = $this->serializer->unserialize($config);

        foreach ($config as $item) {
            if ($this->isValidExpression($item['expression'])) {
                $expressions[] = $item['expression'];
            }
        }

        return $expressions;
    }

    /**
     * @param string $userAgent
     *
     * @return bool
     */
    public function isIgnoredUserAgent($userAgent)
    {
        foreach ($this->getIgnoredUserAgents() as $expression) {
            if (preg_match($expression, $userAgent)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param string $pageType
     *
     * @return bool
     */
    public function isIgnoredPageType($pageType)
    {
        return in_array($pageType, $this->getIgnoredPageTypes());
    }

    /**
     * @return array
     */
    public function getIgnoredUserAgents()
    {
        $expressions = [];
        $config      = $this->scopeConfig->getValue(
            'cache_warmer/general/ignored_user_agents',
            ScopeInterface::SCOPE_STORE
        );

        $config = $this->serializer->unserialize($config);

        foreach ($config as $item) {
            $item          = (array)$item;
            if ($this->isValidExpression($item['expression'])) {
                $expressions[] = $item['expression'];
            }
        }

        return $expressions;
    }

    /**
     * @return array
     */
    public function getIgnoredPageTypes()
    {
        $ignoredPageTypes = [];
        $config = $this->scopeConfig->getValue(
            'cache_warmer/general/ignored_page_types',
            ScopeInterface::SCOPE_STORE
        );

        $ignoredPageTypes = explode(',', $config);

        return $ignoredPageTypes;
    }

    /**
     * @return string
     */
    public function getCacheType()
    {
        return $this->scopeConfig->getValue(\Magento\PageCache\Model\Config::XML_PAGECACHE_TYPE);
    }

    /**
     * @return int
     */
    public function getCacheTtl()
    {
        return $this->scopeConfig->getValue(\Magento\PageCache\Model\Config::XML_PAGECACHE_TTL);
    }

    /**
     * @return string
     */
    public function getTmpPath()
    {
        $path = $this->filesystem->getDirectoryRead(DirectoryList::VAR_DIR)->getAbsolutePath();

        return $path;
    }

    /**
     * @return string
     */
    public function getWarmerUniquePart()
    {
        return $this->scopeConfig->getValue(
            WarmerServiceInterface::WARMER_UNIQUE_VALUE,
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param null $store
     * @return bool
     */
    public function isStoreCodeToUrlEnabled($store = null)
    {
        return $this->scopeConfig->getValue(
            \Magento\Store\Model\Store::XML_PATH_STORE_IN_URL,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $store
        );
    }

    /**
     * @return int
     */
    public function getCacheFillThreshold()
    {
        $value = $this->scopeConfig->getValue('cache_warmer/extended/crawler_limit', ScopeInterface::SCOPE_STORE);

        $value = (int)$value;

        return $value > 0 && $value < 100 ? $value : 100;
    }

    /**
     * @return bool
     */
    public function isDebugToolbarEnabled()
    {
        return $this->scopeConfig->getValue('cache_warmer/debug/info', ScopeInterface::SCOPE_STORE)
            || $this->request->getParam('debug') === 'warmer'
            || strpos($this->request->getParam('uri'), 'debug=warmer') !== false;
    }

    /**
     * @return string
     */
    public function getHttpAuthUsername()
    {
        return $this->scopeConfig->getValue(
            'cache_warmer/extended/http_auth/username',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return string
     */
    public function getHttpAuthPassword()
    {
        return $this->scopeConfig->getValue(
            'cache_warmer/extended/http_auth/password',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function isRequestLogEnabled()
    {
        return $this->scopeConfig->getValue(
            'cache_warmer/debug/request_log',
            ScopeInterface::SCOPE_STORE
        );
    }


    /**
     * @return int
     */
    public function isTagLogEnabled()
    {
        return $this->scopeConfig->getValue(
            'cache_warmer/debug/tag_log',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function isBacktraceLogEnabled()
    {
        return $this->scopeConfig->getValue(
            'cache_warmer/debug/backtrace_log',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return int
     */
    public function getDataVersion()
    {
        return (int)$this->scopeConfig->getValue(
            'cache_warmer/data_version'
        );
    }

    /**
     * @param int $version
     */
    public function setDataVersion($version)
    {
        $this->configWriter->saveConfig(
            'cache_warmer/data_version',
            $version,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
        $this->cacheTypeList->cleanType("config");
    }

    /**
     * @param  string $expression
     * @return bool
     */
    private function isValidExpression($expression)
    {
        set_error_handler(function () {
        }, E_WARNING);
        $isValidExpression = preg_match($expression, "") !== false;
        restore_error_handler();

        return $isValidExpression;
    }

    /**
     * @return int
     */
    public function getCleanupPagesState()
    {
        return (int)$this->scopeConfig->getValue('cache_warmer/cleanup_pages/state');
    }

    /**
     * @param int $state
     */
    public function setCleanupPagesState($state)
    {
        $this->configWriter->saveConfig(
            'cache_warmer/cleanup_pages/state',
            $state,
            ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
            0
        );
        $this->cacheTypeList->cleanType("config");
    }
}
