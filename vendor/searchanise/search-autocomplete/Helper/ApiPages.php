<?php

namespace Searchanise\SearchAutocomplete\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Cms\Model\ResourceModel\Page\CollectionFactory as PageCollectionFactory;
use Magento\Cms\Model\ResourceModel\Page\Collection as PageCollection;
use Magento\Cms\Model\Page as PageModel;
use Magento\Store\Model\Store as StoreModel;

use Searchanise\SearchAutocomplete\Model\Configuration;
use Searchanise\SearchAutocomplete\Model\Queue;

class ApiPages extends AbstractHelper
{
    const USE_GENERATED_URLS = true;

    /**
     * @var array
     */
    private static $excludedPages = [
        'no-route', // 404 page
        'enable-cookies', // Enable Cookies
        'privacy-policy-cookie-restriction-mode', // Privacy Policy
        'service-unavailable', // 503 Service Unavailable
        'private-sales', // Welcome to our Exclusive Online Store
        'home', // Home
    ];

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var FilterProvider
     */
    private $filterProvider;

    /**
     * @var PageCollectionFactory
     */
    private $cmsResourceModelPageCollectionFactory;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        FilterProvider $filterProvider,
        Configuration $configuration,
        PageCollectionFactory $cmsResourceModelPageCollectionFactory
    ) {
        $this->storeManager = $storeManager;
        $this->filterProvider = $filterProvider;
        $this->configuration = $configuration;
        $this->cmsResourceModelPageCollectionFactory = $cmsResourceModelPageCollectionFactory;

        parent::__construct($context);
    }

    /**
     * Generate feed for the page
     *
     * @param  PageModel    $page
     * @param  StoreModel   $store
     * @param  string       $checkData
     *
     * @return array
     */
    public function generatePageFeed(
        PageModel $page,
        StoreModel $store = null,
        $checkData = true
    ) {
        $item = [];

        if ($checkData
            && (empty($page)
            || !$page->getId()
            || !$page->getTitle()
            || !$page->getIsActive()
            || in_array($page->getIdentifier(), self::$excludedPages))
        ) {
            return $item;
        }

        $item['id'] = $page->getId();
        $item['title'] = $page->getTitle();
        $item['link'] = $this->getPageLink($page, $store);

        if ($this->configuration->getIsRenderPageTemplateEnabled()) {
            $item['summary'] = $this->filterProvider->getPageFilter()->filter($page->getContent());
        } else {
            $item['summary'] = $page->getContent();
        }

        return $item;
    }

    /**
     * Returns page url on frontend
     *
     * @param PageModel  $page
     * @param StoreModel $store
     *
     * @return string
     */
    public function getPageLink(
        PageModel $page,
        StoreModel $store = null
    ) {
        if (!$store) {
            $store = $this->storeManager->getCurrentStore();
        }

        $apiSeHelper = ObjectManager::getInstance()
            ->get(\Searchanise\SearchAutocomplete\Helper\ApiSe::class);

        $requestParams = [
            '_nosid'  => true,
            '_secure' => $apiSeHelper->getIsUseSecureUrlsInFrontend($store->getId()),
            '_scope'  => $store->getId(),
        ];

        if (self::USE_GENERATED_URLS && $page->getIdentifier() != '') {
            $url = $apiSeHelper->getStoreUrl($store->getId())->getBaseUrl($requestParams) . $page->getIdentifier();
        } else {
            $requestParams['id'] = $page->getId();
            $url = $apiSeHelper
                ->getStoreUrl($store->getId())
                ->getUrl('cms/page/view', $requestParams);
        }

        return $url;
    }

    /**
     * Retruns pages by pages ids
     *
     * @param  mixed      $pageIds Pages ids
     * @param  StoreModel $store   Stores
     *
     * @return array|PageCollection
     */
    public function getPages(
        $pageIds = Queue::NOT_DATA,
        StoreModel $store = null
    ) {
        static $arrPages = [];

        $keyPages = '';

        if (!empty($pageIds)) {
            if (is_array($pageIds)) {
                $keyPages .= implode('_', $pageIds);
            } else {
                $keyPages .= $pageIds;
            }
        }

        $storeId = !empty($store) ? $store->getId() : 0;
        $keyPages .= ':' .  $storeId;

        if (!isset($arrPages[$keyPages])) {
            $collection = $this->cmsResourceModelPageCollectionFactory
                ->create()
                ->addFieldToFilter('is_active', 1);

            if ($store) {
                $collection->addStoreFilter($storeId);
            }

            if ($pageIds !== Queue::NOT_DATA) {
                // Already exist automatic definition 'one value' or 'array'.
                $this->addIdFilter($collection, $pageIds);
            }

            $arrPages[$keyPages] = $collection->load();
        }

        return $arrPages[$keyPages];
    }

    /**
     * Generate feed for the pages
     *
     * @param  mixed      $pageIds   Page ids
     * @param  StoreModel $store     Store
     * @param  boolean    $checkData
     *
     * @return array
     */
    public function generatePagesFeed(
        $pageIds = Queue::NOT_DATA,
        StoreModel $store = null,
        $checkData = true
    ) {
        $items = [];

        $pages = $this->getPages($pageIds, $store);

        if (!empty($pages)) {

            foreach ($pages as $page) {
                $item = $this->generatePageFeed($page, $store, $checkData);

                if (!empty($item)) {
                    $items[] = $item;
                }
            }
        }

        return $items;
    }

    /**
     * Returns mix/max page ids values
     *
     * @param StoreModel $store
     *
     * @return array(mix, max)
     */
    public function getMinMaxPageId(StoreModel $store = null)
    {
        $startId = $endId = 0;

        $pageStartCollection = $this->cmsResourceModelPageCollectionFactory
            ->create()
            ->setOrder('page_id', DataCollection::SORT_ORDER_ASC)
            ->setPageSize(1);

        if (!empty($store)) {
            $pageStartCollection->addStoreFilter($store);
        }

        $pageEndCollection = $this->cmsResourceModelPageCollectionFactory
            ->create()
            ->setOrder('page_id', DataCollection::SORT_ORDER_DESC)
            ->setPageSize(1);

        if (!empty($store)) {
            $pageEndCollection->addStoreFilter($store);
        }

        if ($pageStartCollection->getSize() > 0) {
            $firstItem = $pageStartCollection->getFirstItem();
            $startId = $firstItem->getId();
        }

        if ($pageEndCollection->getSize() > 0) {
            $firstItem = $pageEndCollection->getFirstItem();
            $endId = $firstItem->getId();
        }

        return [$startId, $endId];
    }

    /**
     * Returns page ids from range
     *
     * @param  int        $start Start page id
     * @param  int        $end   End page id
     * @param  int        $step  Step value
     * @param  StoreModel $store
     *
     * @return array
     */
    public function getPageIdsFromRange($start, $end, $step, StoreModel $store = null)
    {
        $arrPages = [];

        $pages = $this->cmsResourceModelPageCollectionFactory
            ->create()
            ->addFieldToFilter('page_id', ['from' => $start, 'to' => $end])
            ->addFieldToFilter('is_active', 1)
            ->setPageSize($step);

        if (!empty($store)) {
            $pages = $pages->addStoreFilter($store->getId());
        }

        $arrPages = $pages->getAllIds();
        // It is necessary for save memory.
        unset($pages);

        return $arrPages;
    }

    /**
     * Add Id filter
     *
     * @param  PageCollection   $collection
     * @param  array|string|int $pageIds
     *
     * @return PageCollection
     */
    private function addIdFilter(PageCollection &$collection, $pageIds)
    {
        if (is_array($pageIds)) {
            if (empty($pageIds)) {
                $condition = '';
            } else {
                $condition = ['in' => $pageIds];
            }
        } elseif (is_numeric($pageIds)) {
            $condition = $pageIds;
        } elseif (is_string($pageIds)) {
            $ids = explode(',', $pageIds);

            if (empty($ids)) {
                $condition = $pageIds;
            } else {
                $condition = ['in' => $ids];
            }
        }

        return $collection->addFieldToFilter('page_id', $condition);
    }
}
