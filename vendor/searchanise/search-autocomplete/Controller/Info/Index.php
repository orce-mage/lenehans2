<?php

namespace Searchanise\SearchAutocomplete\Controller\Info;

use Searchanise\SearchAutocomplete\Helper\ApiSe as ApiSeHelper;
use Searchanise\SearchAutocomplete\Helper\Data as SeHelper;
use Searchanise\SearchAutocomplete\Helper\Logger as SeLogger;
use Searchanise\SearchAutocomplete\Helper\ApiProducts;
use Searchanise\SearchAutocomplete\Helper\ApiCategories;
use Searchanise\SearchAutocomplete\Helper\ApiPages;
use Searchanise\SearchAutocomplete\Model\Configuration;
use Searchanise\SearchAutocomplete\Model\QueueFactory;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Escaper;

class Index extends Action
{
    const RESYNC             = 'resync';
    const OUTPUT             = 'visual';
    const PROFILER           = 'profiler';
    const STORE_ID           = 'store_id';
    const CHECK_DATA         = 'check_data';
    const DISPLAY_ERRORS     = 'display_errors';
    const PRODUCT_ID         = 'product_id';
    const PRODUCT_IDS        = 'product_ids';
    const CATEGORY_ID        = 'category_id';
    const CATEGORY_IDS       = 'category_ids';
    const PAGE_ID            = 'page_id';
    const PAGE_IDS           = 'page_ids';
    const BY_ITEMS           = 'by_items';
    const PARENT_PRIVATE_KEY = 'parent_private_key';

    /**
     * @var ApiSeHelper
     */
    private $apiSeHelper;

    /**
     * @var SeHelper
     */
    private $dataHelper;

    /**
     * @var SeLogger
     */
    private $loggerHelper;

    /**
     * @var ApiProducts
     */
    private $apiProducts;

    /**
     * @var ApiCategories
     */
    private $apiCategories;

    /**
     * @var ApiPages
     */
    private $apiPages;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var RawFactory
     */
    private $resultRawFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var QueueFactory
     */
    private $queueFactory;

    /**
     * @ Escaper
     */
    private $escaper;

    private $visual = false;
    private $store = null;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        RawFactory $resultRawFactory,
        ApiSeHelper $apiSeHelper,
        SeHelper $dataHelper,
        SeLogger $loggerHelper,
        ApiProducts $apiProducts,
        ApiPages $apiPages,
        ApiCategories $apiCategories,
        Configuration $configuration,
        QueueFactory $queueFactory,
        StoreManagerInterface $storeManager,
        Escaper $escaper
    ) {
        $this->apiSeHelper = $apiSeHelper;
        $this->dataHelper = $dataHelper;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->loggerHelper = $loggerHelper;
        $this->apiProducts = $apiProducts;
        $this->apiPages = $apiPages;
        $this->apiCategories = $apiCategories;
        $this->configuration = $configuration;
        $this->queueFactory = $queueFactory;
        $this->storeManager = $storeManager;
        $this->escaper = $escaper;

        parent::__construct($context);
    }

    public function execute()
    {
        $request = $this->getRequest();

        $this->visual = $request->getParam(self::OUTPUT, 'N') == 'Y';

        if (!$this->dataHelper->checkPrivateKey()) {
            $_options = $this->apiSeHelper->getAddonOptions();

            $this->setOutput(
                [
                'status'  => $_options['addon_status'],
                'api_key' => $_options['api_key'],
                ]
            );
        } else {
            $resync        = $request->getParam(self::RESYNC, 'N') == 'Y';
            $profiler      = $request->getParam(self::PROFILER, 'N') == 'Y';
            $storeId       = $request->getParam(self::STORE_ID);
            $checkData     = $request->getParam(self::CHECK_DATA, 'N');
            $displayErrors = $request->getParam(self::DISPLAY_ERRORS, 'Y') == 'Y';
            $productId     = $request->getParam(self::PRODUCT_ID);
            $productIds    = $request->getParam(self::PRODUCT_IDS);
            $categoryId    = $request->getParam(self::CATEGORY_ID);
            $categoryIds   = $request->getParam(self::CATEGORY_IDS);
            $pageId        = $request->getParam(self::PAGE_ID);
            $pageIds       = $request->getParam(self::PAGE_IDS);
            $byItems       = $request->getParam(self::BY_ITEMS, 'N');

            if ($byItems == 'Y') {
                $this->apiProducts->setIsGetProductsByItems(true);
            }

            $checkData = $checkData != 'N';

            if ($displayErrors) {
                error_reporting(E_ALL | E_STRICT);
                ini_set('display_errors', 1);
                ini_set('display_startup_errors', 1);
            } else {
                error_reporting(0);
                ini_set('display_errors', 0);
                ini_set('display_startup_errors', 0);
            }

            $productIds = $productId ? [$productId] : ($productIds ? explode(',', $productIds) : []);
            $categoryIds = $categoryId ? [$categoryId] : ($categoryIds ? explode(',', $categoryIds) : []);
            $pageIds = $pageId ? [$pageId] : ($pageIds ? explode(',', $pageIds) : []);

            if (!empty($storeId)) {
                $this->store = $this->storeManager->getStore($storeId);
            } else {
                $this->store = $this->storeManager->getStore();
            }

            if ($profiler) {
                /**
                 * To enable profileing the following code should be added to the index.php:
                 *
                 * $_SERVER["MAGE_PROFILER"]="html";
                 */

                try {
                    \Magento\Framework\Profiler::start('info-profiler');
                    $numberIterations = 50;

                    if (empty($productIds)) {
                        $this->storeManager->setCurrentStore(0);

                        $productIds = [];
                        $allProductIds = $this->apiProducts->getProductCollection()->setPageSize($numberIterations)->load();

                        foreach ($allProductIds as $key => $value) {
                            $productIds[] = $value->getId();

                            if (count($productIds) > $numberIterations) {
                                break;
                            }
                        }

                        $numberIterations = 1;
                    }

                    $n = 0;
                    $productFeeds = '';
                    while ($n < $numberIterations) {
                        $productFeeds = $this->apiProducts->generateProductsFeed($productIds, $this->store, true);
                        $n++;
                    }

                    \Magento\Framework\Profiler::stop('info-profiler');
                } catch (\Exception $e) {
                    \Magento\Framework\Profiler::stop('magento');
                    $this->setOutput('Error occurs: ' . $e->getMessage());
                }
            } elseif ($resync) {
                if ($this->apiSeHelper->queueImport(!empty($storeId) ? $this->store->getId() : null, false)) {
                    $this->setOutput('The product catalog is queued for syncing with Searchanise');
                } else {
                    $this->setOutput('Unable to add the product catalog to searchanise queue');
                }
            } elseif (!empty($productIds) || !empty($categoryIds) || !empty($pageIds)) {
                $this->dataHelper->startEmulation($this->store);
                $feed = [
                    'header'     => $this->apiProducts->getHeader($this->store),
                    'items'      => $this->apiProducts->generateProductsFeed($productIds, $this->store, $checkData),
                    'schema'     => $this->apiProducts->getSchema($this->store),
                    'categories' => $this->apiCategories->generateCategoriesFeed($categoryIds, $this->store, $checkData),
                    'pages'      => $this->apiPages->generatePagesFeed($pageIds, $this->store, $checkData),
                ];
                $this->dataHelper->stopEmulation();

                $this->setOutput($feed);
            } else {
                $options = $this->apiSeHelper->getAddonOptions();

                $options['next_queue'] = $this->queueFactory->create()->getNextQueue();
                $options['total_items_in_queue'] = $this->queueFactory->create()->getTotalItems();
                $options['last_resync_error'] = $this->configuration->getLastResyncError();

                $options['search_input_selector'] = $this->apiSeHelper->getSearchInputSelector();
                $options['enabled_searchanise_search'] = $this->configuration->getIsSearchaniseSearchEnabled() ? 'Y' : 'N';

                $options['max_execution_time'] = ini_get('max_execution_time');
                set_time_limit(0);
                $options['max_execution_time_after'] = ini_get('max_execution_time');

                $options['ignore_user_abort'] = ini_get('ignore_user_abort');
                ignore_user_abort(1);
                $options['ignore_user_abort_after'] = ini_get('ignore_user_abort_after');

                $options['searchanise_connect'] = $this->apiSeHelper->checkConnections(false) ? 'OK' : 'Failed';

                $options['memory_limit'] = ini_get('memory_limit');
                $asyncMemoryLimit = $this->configuration->getAsyncMemoryLimit();
                if (substr(ini_get('memory_limit'), 0, -1) < $asyncMemoryLimit) {
                    ini_set('memory_limit', $asyncMemoryLimit . 'M');
                }
                $options['memory_limit_after'] = ini_get('memory_limit');

                $options['sync_mode'] = $this->apiSeHelper->getSyncMode();

                $stores = $this->apiSeHelper->getStores();
                if (!empty($stores)) {
                    foreach ($stores as $_store) {
                        list($minProductId, $maxProductId) = $this->apiProducts->getMinMaxProductId($_store);
                        $options['min_max_product_ids'][$_store->getId()] = [
                            'min_product_id' => $minProductId,
                            'max_product_id' => $maxProductId,
                        ];
                        $options['full_config'][$_store->getId()] = $this->configuration->getFullStoreConfig($_store->getId());
                    }
                }

                $objectManager = ObjectManager::getInstance();
                // Check collections
                $fullTextCollection        = $objectManager->get(\Magento\CatalogSearch\Model\ResourceModel\Fulltext\Collection::class);
                $searchCollection          = $objectManager->get(\Magento\CatalogSearch\Model\ResourceModel\Fulltext\SearchCollection::class);
                $collectionFactory         = $objectManager->get(\Magento\CatalogSearch\Model\ResourceModel\Fulltext\CollectionFactory::class);
                $advancedCollectionFactory = $objectManager->get(\Magento\CatalogSearch\Model\ResourceModel\Advanced\CollectionFactory::class);

                // Check direct collection name
                $options['collections']['direct_full_text_collection'] = [
                    'need'    => 'Searchanise\SearchAutocomplete\Model\ResourceModel\Product\Fulltext\Collection',
                    'current' => get_class($fullTextCollection),
                ];
                // Check direct search collection
                $options['collections']['direct_search_collection'] = [
                    'need'    => 'Searchanise\SearchAutocomplete\Model\ResourceModel\Product\Fulltext\Collection',
                    'current' => get_class($searchCollection),
                ];
                // Check collection created via factory
                $options['collections']['factory_full_text_collection'] = [
                    'need'    => 'Searchanise\SearchAutocomplete\Model\ResourceModel\Product\Fulltext\Collection',
                    'current' => get_class($collectionFactory->create())
                ];
                // Check advanced collection created via factory
                $options['collections']['factory_advanced_full_text_collection'] = [
                    'need'    => 'Searchanise\SearchAutocomplete\Model\ResourceModel\Product\Fulltext\Collection',
                    'current' => get_class($advancedCollectionFactory->create())
                ];
                // Check ElasticSearch classes
                try {
                    $itemCollectionProvider = $objectManager->get('Magento\CatalogSearch\Model\Search\ItemCollectionProviderInterface');
                    $options['ElasticSearch']['ItemCollectionProvider'] = [
                        'need'    => 'Magento\CatalogSearch\Model\Search\ItemCollectionProvider',
                        'current' => get_class($itemCollectionProvider),
                    ];
                } catch (\Exception $e) {
                    $options['ElasticSearch']['ItemCollectionProvider'] = [
                        'need'    => 'Magento\CatalogSearch\Model\Search\ItemCollectionProvider',
                        'current' => 'null'
                    ];
                }
                $options['current_engine'] = $this->configuration->getCurrentEngine();

                $this->setOutput($options);
            }
        }

        return $this->getResult();
    }

    private function getResult()
    {
        static $result;

        if (!$result) {
            if ($this->visual) {
                $result = $this->resultRawFactory->create();
            } else {
                $result = $this->resultJsonFactory->create();
            }
        }

        return $result;
    }

    private function printR()
    {
        static $count = 0;
        $args = func_get_args();
        $content = '';

        if (!empty($args)) {
            $content .= '<ol style="font-family: Courier; font-size: 12px; border: 1px solid #dedede; background-color: #efefef; float: left; padding-right: 20px;">';

            foreach ($args as $k => $v) {
                $v = $this->escaper->escapeHtml(print_r($v, true));
                if ($v == '') {
                    $v = '    ';
                }

                $content .= '<li><pre>' . $v . "\n" . '</pre></li>';
            }

            $content .= '</ol><div style="clear:left;"></div>';
        }

        $count++;

        $this->getResult()->setContents($content);
    }

    private function setOutput($data)
    {
        if ($this->visual) {
            $this->printR($data);
        } else {
            $this->getResult()->setData($data);
        }
    }
}
