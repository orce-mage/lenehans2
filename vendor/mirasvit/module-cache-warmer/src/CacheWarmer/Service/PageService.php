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



namespace Mirasvit\CacheWarmer\Service;

use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\App\PageCache\Cache;
use Magento\Framework\App\PageCache\Identifier as CacheIdentifier;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Module\ModuleListInterface;
use Magento\Framework\Registry;
use Magento\PageCache\Model\Config as PageCacheConfig;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Api\Data\PageTypeInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageTypeRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Service\FilterServiceInterface;
use Mirasvit\CacheWarmer\Api\Service\PageServiceInterface;
use Mirasvit\CacheWarmer\Api\Service\WarmerServiceInterface;
use Mirasvit\CacheWarmer\Logger\PageStatusVarnishLogger;
use Mirasvit\CacheWarmer\Model\Config;
use Mirasvit\CacheWarmer\Service\Config\ExtendedConfig;
use \Magento\Store\Model\StoreManagerInterface;
use Mirasvit\CacheWarmer\Api\Service\SessionServiceInterface;

class PageService implements PageServiceInterface
{
    /**
     * @var CurlService
     */
    protected $curlService;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var CacheIdentifier
     */
    private $cacheIdentifier;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var HttpContext
     */
    private $httpContext;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var ExtendedConfig
     */
    private $extendedConfig;

    /**
     * @var PageTypeRepositoryInterface
     */
    private $pageTypeRepository;

    /**
     * @var FilterServiceInterface
     */
    private $filterService;

    /**
     * @var ModuleListInterface
     */
    private $moduleList;


    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SessionServiceInterface
     */
    private $sessionService;

    /**
     * @var PageStatusVarnishLogger
     */
    private $logger;

    public function __construct(
        PageRepositoryInterface $pageRepository,
        CacheIdentifier $cacheIdentifier,
        CurlService $curlService,
        Registry $registry,
        HttpContext $httpContext,
        Cache $cache,
        Config $config,
        ExtendedConfig $extendedConfig,
        PageTypeRepositoryInterface $pageTypeRepository,
        FilterServiceInterface $filterService,
        SessionServiceInterface $sessionService,
        ModuleListInterface $moduleList,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateFactory,
        PageStatusVarnishLogger $logger
    ) {
        $this->pageRepository     = $pageRepository;
        $this->cacheIdentifier    = $cacheIdentifier;
        $this->curlService        = $curlService;
        $this->registry           = $registry;
        $this->httpContext        = $httpContext;
        $this->cache              = $cache;
        $this->config             = $config;
        $this->extendedConfig     = $extendedConfig;
        $this->pageTypeRepository = $pageTypeRepository;
        $this->filterService      = $filterService;
        $this->sessionService     = $sessionService;
        $this->moduleList         = $moduleList;
        $this->storeManager       = $storeManager;
        $this->dateFactory        = $dateFactory;
        $this->logger             = $logger;
    }

    /**
     * @param PageInterface $page
     */
    public function setUncacheableStatus(PageInterface $page)
    {
        $page->setStatus(PageInterface::STATUS_UNCACHEABLE);
        $this->pageRepository->save($page);
    }

    /**
     * @param PageInterface $page
     */
    public function setCachedStatus(PageInterface $page)
    {
        if ($page->getStatus() != PageInterface::STATUS_CACHED) {
            $page->setStatus(PageInterface::STATUS_CACHED);
            //using this, because default magento function is not working correctly in some configurations
            $page->setCachedAt(gmdate("Y-m-d H:i:s"));
            $page->setFlushedAt(null);
        }
        $this->pageRepository->save($page);
    }

    /**
     * @param PageInterface $page
     */
    public function setPendingStatus(PageInterface $page)
    {
        if ($page->getStatus() != PageInterface::STATUS_PENDING) {
            $page->setStatus(PageInterface::STATUS_PENDING);
            $page->setCachedAt(null);
            //using this, because default magento function is not working correctly in some configurations
            $page->setFlushedAt(gmdate("Y-m-d H:i:s"));
        }
        $this->pageRepository->save($page);
    }

    /**
     * {@inheritdoc}
     */
    public function isCached(PageInterface $page)
    {
        if ($page->getStatus() == PageInterface::STATUS_UNCACHEABLE) {
            return false;
        }
        if ($this->moduleList->has('FishPig_Bolt')) {
            if ($this->cache->load($page->getCacheId())) {
                $this->setCachedStatus($page);

                return true;
            }

            $channel = $this->curlService->initChannel();

            $channel->setUrl($page->getUri());
            $channel->addCookies($this->sessionService->getCookies($page));

            $response = $this->curlService->request($channel);
            $headers  = $response->getHeaders();

            if (isset($headers['X-Cached-By']) && $headers['X-Cached-By'] == 'Bolt') {
                $this->setCachedStatus($page);

                return true;
            }
            $this->setPendingStatus($page);

            return false;
        } elseif ($this->config->getCacheType() == PageCacheConfig::BUILT_IN) {
            if ($this->cache->load($page->getCacheId())) {
                $this->setCachedStatus($page);

                return true;
            }
            $this->setPendingStatus($page);

            return false;
        } else {
            $channel = $this->curlService->initChannel();

            $channel->setUrl($page->getUri());
            $channel->setUserAgent($page->getUserAgent());
            $channel->addCookie(WarmerServiceInterface::STATUS_COOKIE, 1);
            $channel->addCookies($this->sessionService->getCookies($page));

            $response = $this->curlService->request($channel);

            if ($response->getBody() === '*') {
                $this->setPendingStatus($page);

                return false;
            }

            $code = $response->getCode();
            if ($code !== 200 ) {
                $message = "Page " . $page->getUri()
                    . " (id: " . $page->getId() . ") respond with status code "
                    . $code . ". ";

                if (in_array($code, [301, 302, 404])) {
                    $message .= "Removing page...";
                    $this->pageRepository->delete($page);
                }

                if($this->config->isRequestLogEnabled()) {
                    $isBacktraceLogEnabled = $this->config->isBacktraceLogEnabled();

                    $body = strlen($response->getBody()) > 500
                        ? $body = substr($response->getBody(), 0, 500)."..."
                        : $response->getBody();

                    $this->logger->debug($message, [
                        'cli'       => php_sapi_name() == "cli" ? "Yes" : "No",
                        'code'      => $code,
                        'body'      => $body,
                        'backtrace' => $isBacktraceLogEnabled
                                       ? \Magento\Framework\Debug::backtrace(true, false, false)
                                       : null,
                    ]);
                }

                return false;
            }

            $this->setCachedStatus($page);

            return true;
        }
    }

    /**
     * Remove google gclid from URLs. Same as in default magento config for Varnish.
     *
     * @param string $uri
     *
     * @return string
     */
    public function prepareUri($uri)
    {
        $uri = preg_replace('/(.*)\\?gclid=[^&]+$/', '$1', $uri, -1);
        $uri = preg_replace('/(.*)\\?gclid=[^&]+&/', '$1?', $uri, -1);
        $uri = preg_replace('/(.*)&gclid=[^&]+/', '$1', $uri, -1);

        return $uri;
    }

    /**
     * @param RequestInterface $request
     *
     * @return string
     */
    public function getVaryDataHash(RequestInterface $request)
    {
        $hash = $request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING) ? : $this->httpContext->getVaryString();

        return (string)$hash;
    }

    /**
     * @param RequestInterface $request
     *
     * @return string
     */
    public function getVaryDataString(RequestInterface $request)
    {
        //some 3rd party plugins modify vary data by using this method.
        //we need to make sure that vary data is modifed.
        $this->httpContext->getVaryString();

        $varyData = $this->prepareVaryData($this->httpContext->getData());
        if ($this->config->getCacheType() == PageCacheConfig::BUILT_IN) {
            return $varyData;
        }
        //on non-default stores, vary data is not empty
        //if we use varnish, first request goes without cookie and vary data
        //but we need to crawl it. so we clear vary data
        /** @var \Magento\Framework\App\Request\Http $request */
        if (!$request->get(\Magento\Framework\App\Response\Http::COOKIE_VARY_STRING)) {
            $varyData = $this->prepareVaryData([]);
        }

        return $varyData;
    }

    /**
     * @param string $cookie
     *
     * @return string
     */
    private function prepareCookie($cookie)
    {
        $ignoredCookies = [
            '_ga',
            '_gcl_au',
            '__utmz',
            '__zlcmid',
            '__utmc',
            '_gid',
            '_fbp',
            'PHPSESSID',
            'mst-cache-warmer-track',
            'mst-cache-warmer-toolbar',
        ];

        $cookies = [];
        $ccc     = explode(";", $cookie);

        foreach ($ccc as $c) {
            $cc = explode("=", $c);
            if (count($cc) != 2) {
                continue;
            }

            $k = trim($cc[0]);

            if (in_array($k, $ignoredCookies)) {
                continue;
            }

            $v         = $cc[1];
            $cookies[] = "$k=$v";
        }

        return implode(";", $cookies);
    }

    /**
     * {@inheritdoc}
     */
    public function collect(RequestInterface $request, ResponseInterface $response)
    {
        if ($response->getStatusCode() && $response->getStatusCode() != 200) {
            return false;
        }

        if (!$this->isValidUrl($request->getUriString())) {
            return false;
        }

        /** @var \Magento\Framework\App\Response\Http $response */
        /** @var \Magento\Framework\App\Request\Http $request */
        $cacheId      = $this->cacheIdentifier->getValue();
        $varyDataHash = $this->getVaryDataHash($request);
        $varyData     = $this->getVaryDataString($request);
        $userAgent    = $request->getHeader('User-Agent');

        //remove cache warmer agent
        if (strpos($userAgent, WarmerServiceInterface::USER_AGENT) !== false) {
            $userAgent = substr($userAgent, 0, strpos($userAgent, WarmerServiceInterface::USER_AGENT));
            $userAgent .= ";W";//mark for debug purposes
        }

        $page     = $this->pageRepository->getByCacheId($cacheId, $varyDataHash);
        $pageType = $this->getPageType($request);
        $uri      = $this->prepareUri($request->getUriString());

        $storeId = $this->storeManager->getStore()->getId();
        $cookie  = $this->prepareCookie($request->getHeader('Cookie'));

        if (!$page && $request->getFullActionName() !== '__'
            && strpos($uri, '_=') === false) {

            /** @var PageInterface $page */
            $page = $this->ensurePage(
                $request,
                $this->pageRepository->getByURI($uri, $varyDataHash)
            );

            $pageTypeCollection = $this->pageTypeRepository->getCollection()
                ->addFieldToFilter(PageTypeInterface::PAGE_TYPE, $pageType);

            if ($pageTypeCollection->getSize() == 0) {
                $this->pageTypeRepository->save(
                    $this->pageTypeRepository->create()->setPageType($pageType)
                );
            }

            if (!$this->config->isIgnoredPage($page)) {
                $this->pageRepository->save($page);
            }
        } elseif (is_object($page) && $page->getId() && $pageType != $page->getPageType()) {
            $page->setUri($uri)
                ->setCacheId($cacheId)
                ->setPageType($pageType)
                ->setStoreId($storeId)
                ->setVaryData($varyData)
                ->setVaryDataHash($varyDataHash)
                ->setUserAgent($userAgent)
                ->setCookie($cookie)
                ->setStatus(PageInterface::STATUS_PENDING);

            $this->pageRepository->save($page);
        }

        return true;
    }

    /**
     * @param RequestInterface $request
     * @return string
     */
    public function getPageType(RequestInterface $request)
    {
        $pageType = $request->getFullActionName();
        $uri      = $this->prepareUri($request->getUriString());

        if (strpos($uri, '?') !== false) {
            $pageType .= '_*';
        } elseif ($this->filterService->isSeoFilterPage($pageType, $request->getParams())) {
            $pageType .= '_SeoFilter';
        }

        return $pageType;
    }

    /**
     * {@inheritdoc}
     */
    public function isValidUrl($url)
    {
        if (strpos($url, 'https://') === false
            && strpos($url, 'http://') === false) {
            return false;
        }
        $parsedUrl = parse_url($url);
        //Assume that URL like https://123.123.123.123 is not valid. no cetificate
        if (isset($parsedUrl['host'])
            && $parsedUrl['host']
            && strpos($url, 'http://') === false
            && filter_var($parsedUrl['host'], FILTER_VALIDATE_IP)
        ) {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareVaryData($varyData)
    {
        if (is_array($varyData)) {
            ksort($varyData);
        }

        return \Zend_Json::encode($varyData);
    }

    /**
     * @param RequestInterface $request
     * @param bool|PageInterface $page
     * @return bool|PageInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function ensurePage($request, $page = false)
    {
        if (!$page) {
            $page = $this->pageRepository->create();
        }

        $uri          = $this->prepareUri($request->getUriString());
        $cookie       = $this->prepareCookie($request->getHeader('Cookie'));
        $product      = $this->registry->registry('current_product');
        $category     = $this->registry->registry('current_category');
        $productId    = $product ? $product->getId() : 0;
        $categoryId   = $category ? $category->getId() : 0;

        if (!$productId && $this->registry->registry(PageServiceInterface::PRODUCT_REG)) {
            $productId = $this->registry->registry(PageServiceInterface::PRODUCT_REG);
        }

        if (!$categoryId && $this->registry->registry(PageServiceInterface::CATEGORY_REG)) {
            $categoryId = $this->registry->registry(PageServiceInterface::CATEGORY_REG);
        }

        $page->setUri($uri)
            ->setCacheId($this->cacheIdentifier->getValue())
            ->setPageType($this->getPageType($request))
            ->setStoreId($this->storeManager->getStore()->getId())
            ->setVaryData($this->getVaryDataString($request))
            ->setVaryDataHash($this->getVaryDataHash($request))
            ->setUserAgent($request->getHeader('User-Agent'))
            ->setProductId($productId)
            ->setCategoryId($categoryId)
            ->setCookie($cookie)
            ->setStatus(PageInterface::STATUS_PENDING);

        return $page;
    }
}
