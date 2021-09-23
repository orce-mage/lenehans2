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

use Magento\CacheInvalidate\Model\PurgeCache;
use Magento\Cms\Model\ResourceModel\Page\Collection as CmsPageCollection;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as CmsPageCollectionFactory;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\PageCache\Cache;
use Magento\PageCache\Model\Config as PageCacheConfig;
use Magento\Store\Model\StoreManagerInterface;
use Mirasvit\CacheWarmer\Api\Data\PageInterface;
use Mirasvit\CacheWarmer\Api\Repository\PageRepositoryInterface;
use Mirasvit\CacheWarmer\Api\Service\PageServiceInterface;
use Mirasvit\CacheWarmer\Api\Service\WarmerServiceInterface;
use Mirasvit\CacheWarmer\Api\Service\SessionServiceInterface;
use Mirasvit\CacheWarmer\Model\Config;
use Mirasvit\CacheWarmer\Model\ResourceModel\Page\Collection;
use Mirasvit\CacheWarmer\Service\Config\ExtendedConfig;
use Mirasvit\CacheWarmer\Service\Warmer\PageWarmStatus;
use Mirasvit\CacheWarmer\Service\SessionService;
use Mirasvit\CacheWarmer\Api\Data\WarmRuleInterface;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class WarmerService implements WarmerServiceInterface
{
    /**
     * @var null|string
     */
    private static $cacheType;


    /**
     * @var CurlService
     */
    private $curlService;

    /**
     * @var ExtendedConfig
     */
    private $extendedConfig;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var PageRepositoryInterface
     */
    private $pageRepository;

    /**
     * @var WarmRuleService
     */
    private $warmRuleService;

    /**
     * @var PageServiceInterface
     */
    private $pageService;

    /**
     * @var Cache
     */
    private $cache;

    /**
     * @var Context
     */
    private $context;

    /**
     * @var PurgeCache
     */
    private $purgeCache;

    /**
     * @var SessionServiceInterface
     */
    private $sessionService;

    /**
     * @var PageCacheConfig
     */
    private $pageCacheConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CmsPageCollectionFactory
     */
    private $cmsPageCollectiomFactory;

    /**
     * WarmerService constructor.
     *
     * @param CurlService             $curlService
     * @param ExtendedConfig          $extendedConfig
     * @param Config                  $config
     * @param PageRepositoryInterface $pageRepository
     * @param PageServiceInterface    $pageService
     * @param WarmRuleService         $warmRuleService
     * @param Cache                   $cache
     * @param Context                 $context
     * @param StoreManagerInterface   $storeManager
     * @param PageCacheConfig         $pageCacheConfig
     * @param PurgeCache              $purgeCache
     * @param SessionServiceInterface $sessionService
     */
    public function __construct(
        CurlService $curlService,
        ExtendedConfig $extendedConfig,
        Config $config,
        PageRepositoryInterface $pageRepository,
        PageServiceInterface $pageService,
        WarmRuleService $warmRuleService,
        Cache $cache,
        Context $context,
        StoreManagerInterface $storeManager,
        PageCacheConfig $pageCacheConfig,
        PurgeCache $purgeCache,
        SessionServiceInterface $sessionService,
        CmsPageCollectionFactory $collectionFactory
    ) {
        $this->curlService              = $curlService;
        $this->extendedConfig           = $extendedConfig;
        $this->config                   = $config;
        $this->pageRepository           = $pageRepository;
        $this->pageService              = $pageService;
        $this->warmRuleService          = $warmRuleService;
        $this->cache                    = $cache;
        $this->context                  = $context;
        $this->storeManager             = $storeManager;
        $this->pageCacheConfig          = $pageCacheConfig;
        $this->purgeCache               = $purgeCache;
        $this->sessionService           = $sessionService;
        $this->cmsPageCollectiomFactory = $collectionFactory;
    }

    /**
     * @param Collection        $collection
     * @param WarmRuleInterface $rule
     *
     * @return \Generator|PageWarmStatus[]
     */
    public function warmCollection(Collection $collection, WarmRuleInterface $rule = null)
    {
        $queue = [];

        while ($p = $collection->fetchItem()) {
            /** @var PageInterface $page */
            $page = $p; //casting interface. fix somehow
            $page = $this->warmRuleService->modifyPage($page, $rule);
            if ($page->getStatus() != PageInterface::STATUS_UNCACHEABLE && $this->pageService->isCached($page)) {
                continue;
            }

            $queue[] = $page;

            if (count($queue) >= $this->config->getWarmThreads()) {
                foreach ($this->warmPages($queue) as $warmStatus) {
                    yield $warmStatus;
                }

                $queue = [];
            }
        }

        //if collection more than threads
        if ($queue) {
            foreach ($this->warmPages($queue) as $warmStatus) {
                yield $warmStatus;
            }
        }
    }

    /**
     * @param PageInterface[] $pages
     * @param bool            $terminate
     *
     * @return PageWarmStatus[]
     */
    private function warmPages($pages, $terminate = false)
    {
        $channels = $this->curlService->initMultiChannel(count($pages));

        foreach ($channels as $idx => $channel) {
            $page = $pages[$idx];
            $channel->setUrl($page->getUri());
            $channel->setUserAgent($page->getUserAgent());
            $channel->setHeaders($page->getHeaders());
            $channel->addCookies($this->sessionService->getCookies($page));
        }

        $result = [];
        foreach ($this->curlService->multiRequest($channels) as $idx => $response) {
            $result[] = new PageWarmStatus($pages[$idx], $response);
        }

        foreach ($pages as $page) {
            if ($page->getStatus() == PageInterface::STATUS_UNCACHEABLE) {
                $this->pageService->setPendingStatus($page);
            }
            if ($this->pageService->isCached($page)) {
                $this->pageService->setCachedStatus($page);
            } else {
                if(!$terminate) { // try to warm page once more if it wasn't cached for the first time
                    $this->warmPages([$page], true);
                }

                $this->pageService->setUncacheableStatus($page);
            }
        }

        return $result;
    }


    /**
     * {@inheritdoc}
     */
    public function cleanPage(PageInterface $page)
    {
        if ($this->getCacheType() == PageCacheConfig::BUILT_IN) {
            if ($page->getCacheId()) {
                $this->cache->remove($page->getCacheId());
            }
        } else {
            $tags    = [];
            $pattern = "((^|,)%s(,|$))";
            if ($page->getProductId()) {
                $tags[] = 'cat_p_' . $page->getProductId();
            }
            if ($page->getCategoryId()) {
                $tags[] = 'cat_c_' . $page->getCategoryId();
                $tags[] = 'cat_c_p_' . $page->getCategoryId();
            }
            if (strpos($page->getPageType(), 'cms') === 0) {
                $tags = $this->addCmsPageCacheTag($page, $tags);
            }

            foreach ($tags as $key => $tag) {
                $tags[$key] = sprintf($pattern, $tag);
            }

            if (!empty($tags)) {
                $this->purgeCache->sendPurgeRequest(implode('|', array_unique($tags)));
            }
        }

        return true;
    }

    /**
     * @return string
     */
    private function getCacheType()
    {
        if (self::$cacheType === null) {
            self::$cacheType = $this->config->getCacheType();
        }

        return self::$cacheType;
    }

    /**
     * @param PageInterface $page
     * @param array         $tags
     *
     * @return array
     */
    private function addCmsPageCacheTag(PageInterface $page, $tags)
    {
        /** @var CmsPageCollection $cmsPageCollection */
        $cmsPageCollection = $this->cmsPageCollectiomFactory->create();
        $storeId           = $page->getStoreId();

        if ($page->getPageType() == 'cms_index_index') {
            $pageIdentifier = 'home';
        } else {
            $baseUrl        = $this->storeManager->getStore($storeId)->getBaseUrl();
            $pageIdentifier = str_replace($baseUrl, '', $page->getUri());
        }

        $cmsPageCollection
            ->addFieldToFilter('identifier', ['eq' => $pageIdentifier])
            ->addFieldToFilter('store_id', ['in' => [0, $storeId]])
            ->setOrder('store_id');

        $tags[] = 'cms_p_' . $cmsPageCollection->getFirstItem()->getPageId();

        return $tags;
    }
}
