<?php

namespace Searchanise\SearchAutocomplete\Console\Command;

use Symfony\Component\Console\Command\Command;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Registry;
use Magento\Framework\App\State as AppState;

use Searchanise\SearchAutocomplete\Helper\ApiSe as ApiSeHelper;
use Searchanise\SearchAutocomplete\Helper\Data as SearchaniseHelper;
use Searchanise\SearchAutocomplete\Model\Configuration;
use Searchanise\SearchAutocomplete\Model\QueueFactory;

abstract class AbstractCommand extends Command
{
    /**
     * Configuration
     */
    protected $configuration;

    /**
     * QueueFactory
     */
    protected $queueFactory;

    /**
     * StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Registry
     */
    protected $registry;

    /**
     * AppState
     */
    protected $appState;

    public function __construct(
        Configuration $configuration,
        QueueFactory $queueFactory,
        StoreManagerInterface $storeManager,
        Registry $registry,
        AppState $appState
    ) {
        $this->configuration = $configuration;
        $this->queueFactory = $queueFactory;
        $this->storeManager = $storeManager;
        $this->registry = $registry;
        $this->appState = $appState;

        parent::__construct();
    }

    protected function cleanup($output)
    {
        if ($this->getApiSeHelper()->deleteKeys([], true)) {
            $this->configuration->setAutoInstall(false);

            $output->writeln('<info>Searchanise keys were cleanup succesfully.</info>');
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } else {
            $output->writeln('<error>Searchanise cleanup failed.</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }

    protected function signup($output)
    {
        ApiSeHelper::$consoleEmail = 'admin@example.com';

        if ($this->getApiSeHelper()->signup(null, false, true)) {
            $output->writeln('<info>Searchanise was sucessfully registered.</info>');
            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } else {
            $output->writeln('<error>Searchanise signup failed.</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }

    protected function getApiSeHelper()
    {
        static $apiSeHelper;

        if (!$apiSeHelper) {
            $apiSeHelper = ObjectManager::getInstance()->get(\Searchanise\SearchAutocomplete\Helper\ApiSe::class);
        }

        return $apiSeHelper;
    }

    protected function setAdminArea()
    {
        $this->appState->setAreaCode('adminhtml');

        return $this;
    }

    protected function setFrontendArea()
    {
        $this->appState->setAreaCode('frontend');

        return $this;
    }

    protected function createTestCategory($title, $storeId)
    {
        $category = null;
        $objectManager = ObjectManager::getInstance();
        $store = $this->storeManager->getStore($storeId);
        $rootNodeId = $store->getRootCategoryId();
        $rootCat = $objectManager->get(\Magento\Catalog\Model\Category::class);
        $rootCat->load($rootNodeId);

        $categoryFactory = $objectManager->get(\Magento\Catalog\Model\CategoryFactory::class);
        $category = $categoryFactory->create();
        $category->setName($title);
        $category->setIsActive(true);
        $category->setUrlKey('test-category-' . rand(1000, 1000000));
        $category->setData('description', 'this is test category');
        $category->setParentId($rootNodeId);
        $category->setStoreId($storeId);
        $category->setPath($rootCat->getPath());
        $category->save();

        return $category;
    }

    protected function createTestPage($title, $storeId = null)
    {
        $objectManager = ObjectManager::getInstance();
        $store = $this->storeManager->getStore($storeId);

        $pageFactory = $objectManager->get(\Magento\Cms\Model\PageFactory::class);
        $page = $pageFactory->create();
        $page->setData([
            'title' => $title,
            'page_layout' => '1column',
            'meta_keywords' => 'Page keywords',
            'meta_description' => 'Page description',
            'identifier' => 'custom-test-page-' . rand(1000, 1000000),
            'content_heading' => 'Custom cms page',
            'content' => "<h1>This is custom test page</h1>",
            'is_active' => 1,
            'stores' => [$storeId],
            'sort_order' => 0
        ])->save();

        return $page;
    }

    protected function createTestProduct($title, $type = 'simple', $category = null, $storeId = null)
    {
        $product = null;
        $objectManager = ObjectManager::getInstance();
        $store = $this->storeManager->getStore($storeId);
        $websiteId = $store->getWebsiteId();

        switch ($type) {
            case 'simple':
                $product = $objectManager->create(Magento\Catalog\Model\Product::class);
                $product->setSku('test-sku-simple-' . rand(1000, 1000000));
                $product->setName($title . ' ' . rand(1000, 1000000));
                $product->setDescription('This is test simple product');
                if ($category) {
                    $product->setCategoryIds((array)$category->getId());
                }
                $product->setWebsiteIds((array)$websiteId);
                $product->setStatus(1); // Status on product enabled/ disabled 1/0
                $product->setWeight(10);
                $product->setVisibility(4); // visibilty of product (catalog / search / catalog, search / Not visible individually)
                $product->setTaxClassId(0);
                $product->setTypeId('simple'); // type of product (simple/virtual/downloadable/configurable)
                $product->setPrice(100.00);
                $product->setSpecialPrice(77.00);
                $product->setStockData([
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 1,
                    'is_in_stock' => 1,
                    'qty' => 999999999,
                ]);
                $product->save();
                break;

            case 'configurable':
                $product = $objectManager->create(\Magento\Catalog\Model\Product::class);
                $product->setSku('test-sku-configurable-' . rand(1000, 1000000));
                $product->setName($title . ' ' . rand(1000, 1000000));
                $product->setDescription('This is test configurable product');
                if ($category) {
                    $product->setCategoryIds((array)$category->getId());
                }
                $product->setWebsiteIds((array)$websiteId);
                $product->setStatus(1); // Status on product enabled/ disabled 1/0
                $product->setWeight(10);
                $product->setVisibility(4); // visibilty of product (catalog / search / catalog, search / Not visible individually)
                $product->setTaxClassId(0);
                $product->setTypeId('configurable'); // type of product (simple/virtual/downloadable/configurable)
                $product->setPrice(0);
                $product->setStockData([
                    'use_config_manage_stock' => 0,
                    'manage_stock' => 1,
                    'is_in_stock' => 1,
                    'qty' => 999999999,
                ]);

                // super attribute
                $size_attr_id = $product->getResource()->getAttribute('size')->getId();
                $color_attr_id = $product->getResource()->getAttribute('color')->getId();
                $product->getTypeInstance()->setUsedProductAttributeIds([$color_attr_id, $size_attr_id], $product);
                $configurableAttributesData = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
                $product->setCanSaveConfigurableAttributes(true);
                $product->setConfigurableAttributesData($configurableAttributesData);
                $configurableProductsData = [];
                $product->setConfigurableProductsData($configurableProductsData);

                $product->save();
                $productId = $product->getId();

                $productOne =  $this->createTestProduct('assoc-config-product-1', 'simple', $category, $storeId);
                $productTwo =  $this->createTestProduct('assoc-config-product-2', 'simple', $category, $storeId);
                $productThree = $this->createTestProduct('assoc-config-product-3', 'simple', $category, $storeId);

                $associatedProductIds = [$productOne->getId(), $productTwo->getId(), $productThree->getId()];
                $product = $objectManager->create(\Magento\Catalog\Model\Product::class)->load($productId);
                $product->setAssociatedProductIds($associatedProductIds);
                $product->setCanSaveConfigurableAttributes(true);
                $product->save();
                break;
        }

        return $product;
    }

    protected function processQueue($output, $storeId = null)
    {
        $store = $this->storeManager->getStore($storeId);

        $totalItems = $this->queueFactory->create()->getTotalItems();
        $output->writeln('<info>Processing ' . $totalItems . ' items in queue...</info>');
        $result = $this->getApiSeHelper()->async(true);

        if ($result == ApiSeHelper::ASYNC_STATUS_OK) {
            $output->writeln('<info>' . $totalItems . ' items was processed.</info>');
            $output->writeln('<info>Waiting for indexation...</info>');

            $privateKey = $this->getApiSeHelper()->getPrivateKey($store->getId());

            do {
                sleep(3);
                $info = $this->getApiSeHelper()->sendRequest('/api/state/get/json', $privateKey, ['status' => '', 'full_import' => ''], true);

                if (empty($info)) {
                    break;
                }
            } while ($info['variable']['full_import'] != 'done');

            $output->writeln('<info>Indexation was completed.</info>');

            return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
        } else {
            $output->writeln('<error>Error in processing: ' . $result . '.</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }
    }

    protected function search($q, $storeId = null)
    {
        $store = $this->storeManager->getStore($storeId);
        $searchaniseHelper = ObjectManager::getInstance()->get(\Searchanise\SearchAutocomplete\Helper\Data::class);

        $searchRequest = $searchaniseHelper->search([
            'type'    => 'test_container',
            'request' => [
                'query'        => $q,
                'filters'      => [],
                'queryFilters' => [],
                'orders'       => ['name' => 'asc'],
                'pageSize'     => 10,
                'curPage'      => 1,
            ],
        ]);

        $results = $searchRequest->getSearchResult();

        if (empty($results) && (empty($results['items']) || empty($results['categories']) || empty($results['pages']))) {
            return [];
        }

        return [$results['items'], $results['categories'], $results['pages']];
    }

    protected function createOrder($product_id, $storeId = null)
    {
        $orderId = null;
        $orderData = [
            'currency_id'  => 'USD',
            'email'        => 'customer@example.com',
            'shipping_address' =>[
                'firstname'    => 'John', //address Details
                'lastname'     => 'Doe',
                'street'       => '123 Demo',
                'city'         => 'Mageplaza',
                'country_id'   => 'US',
                'region'       => 'California',
                'region_id'    => '12', // State region id
                'postcode'     => '10019',
                'telephone'    => '0123456789',
                'fax'          => '32423',
                'save_in_address_book' => 1,
            ],
            'items'=> [
                [
                    'product_id' => $product_id,
                    'price'      => 50.0,
                    'qty'        => 1,
                ],
            ],
        ];

        $objectManager = ObjectManager::getInstance();
        $store = $this->storeManager->getStore($storeId);
        $websiteId = $store->getWebsiteId();

        $customerFactory = $objectManager->get(\Magento\Customer\Model\CustomerFactory::class);
        $quote = $objectManager->get(\Magento\Quote\Model\QuoteFactory::class);
        $quoteManagement = $objectManager->get(\Magento\Quote\Model\QuoteManagement::class);
        $customerRepository = $objectManager->get(\Magento\Customer\Api\CustomerRepositoryInterface::class);
        $orderService = $objectManager->get(\Magento\Sales\Model\Service\OrderService::class);
        $productModel = $objectManager->get(\Magento\Catalog\Model\Product::class);
        
        $customer = $customerFactory->create();
        $customer->setWebsiteId($websiteId);
        $customer->loadByEmail($orderData['email']);

        if (!$customer->getEntityId()) {
            // If not avilable then create this customer
            $customer->setWebsiteId($websiteId)
                ->setStore($store)
                ->setFirstname($orderData['shipping_address']['firstname'])
                ->setLastname($orderData['shipping_address']['lastname'])
                ->setEmail($orderData['email'])
                ->setPassword($orderData['email'])
                ->save();
        }

        $quote = $quote->create(); //Create object of quote
        $quote->setStore($store);

        // if you have allready buyer id then you can load customer directly
        $customer = $customerRepository->getById($customer->getEntityId());
        $quote->setCurrency();
        $quote->assignCustomer($customer);

        // Add items in quote
        foreach ($orderData['items'] as $item) {
            $product = $productModel->load($item['product_id']);
            $product->setPrice($item['price']);
            $quote->addProduct($product, (int)$item['qty']);
        }

        // Set Address to quote
        $quote->getBillingAddress()->addData($orderData['shipping_address']);
        $quote->getShippingAddress()->addData($orderData['shipping_address']);

        // Collect Rates and Set Shipping & Payment Method
        $shippingAddress = $quote->getShippingAddress();
        $shippingAddress->setCollectShippingRates(true)
            ->collectShippingRates()
            ->setShippingMethod('flatrate_flatrate'); //shipping method
        $quote->setPaymentMethod('checkmo'); //payment method
        $quote->setInventoryProcessed(false); //not effetc inventory
        $quote->save();

        // Set Sales Order Payment
        $quote->getPayment()->importData(['method' => 'checkmo']);

        // Collect Totals & Save Quote
        $quote->collectTotals()->save();

        // Create Order From Quote
        $order = $quoteManagement->submit($quote);
        $order->setEmailSent(0);
        $increment_id = $order->getRealOrderId();

        if ($order->getEntityId()) {
            $orderId = $order->getRealOrderId();
        }

        return $orderId;
    }

    public function createStoreView()
    {
        $objectManager = ObjectManager::getInstance();
        $storeView = $objectManager->create(\Magento\Store\Model\Store::class);
        $storeView->setName('EN');
        $storeView->setCode('store2_en');
        $storeView->setWebsiteId(1);
        $storeView->setGroupId(1); // GroupId is a Store ID (in adminhtml terms)
        $storeView->setSortOrder(10);
        $storeView->setIsActive(true);
        $storeView->save();

        return $storeView;
    }

    public function fullTest($output)
    {
        $this->setAdminArea();
        $this->registry->register('isSecureArea', true);
        $store = $this->storeManager->getStore();
        $objectManager = ObjectManager::getInstance();
        ApiSeHelper::$consoleEmail = 'admin@example.com';

        // Registration test
        if ($this->cleanup($output) != \Magento\Framework\Console\Cli::RETURN_SUCCESS) {
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        if ($this->signup($output) != \Magento\Framework\Console\Cli::RETURN_SUCCESS) {
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        /*$output->writeln('<info>Creating new storeview.</info>');
        $_store = $this->createStoreView();
        $output->writeln('<info>Storeview ' . $_store->getId() . ' was created</info>');*/

        /**
         * Test create/update items
         */
        $category = $this->createTestCategory('Test category', $store->getId());
        $output->writeln('<info>Test category (' . $category->getId() . ') was created.</info>');

        $product = $this->createTestProduct('Test simple product', 'simple', $category, $store->getId());
        $output->writeln('<info>Test simple product (' . $product->getId() . ') was created.</info>');

        $configurableProduct = $this->createTestProduct('Test configurable product', 'configurable', $category, $store->getId());
        $output->writeln('<info>Test configurable product (' . $configurableProduct->getId() . ') was created.</info>');

        $page = $this->createTestPage('Test page');
        $output->writeln('<info>Test page (' . $page->getId() . ') was created.</info>');

        if (!$category || !$product || !$configurableProduct || !$page) {
            $output->writeln('<error>Unable to create objects.</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        /**
         * Test order creating functionality
         */
        $output->writeln('<info>Creating order...</info>');
        try {
            $orderId = $this->createOrder($product->getId());
        } catch (\Exception $e) {
            $product->delete();
            $configurableProduct->delete();
            $page->delete();
            $category->delete();

            throw $e;
        }

        if ($orderId) {
            $output->writeln('<info>Order ' . $orderId . ' was created</info>');
        } else {
            $output->writeln('<error>Unable to create order</error>');
        }

        /**
         * Test queue functionality
         */
        if ($this->queueFactory->create()->getTotalItems() < 4) {
            $output->writeln('<error>Objects were NOT added to queue!</error>');
            $product->delete();
            $configurableProduct->delete();
            $page->delete();
            $category->delete();

            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        if ($this->processQueue($output, $store->getId()) != \Magento\Framework\Console\Cli::RETURN_SUCCESS) {
            $output->writeln('<error>Unable to process update queue.</error>');
            $product->delete();
            $configurableProduct->delete();
            $page->delete();
            $category->delete();

            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $output->writeln('<info>Waiting 30 sec.</info>');
        sleep(30);

        /**
         * Test pages access
         */
        $output->writeln('<info>Check page access...</info>');
        $pagesToCheck = [
            $this->getApiSeHelper()->getUrl('/', false, $store->getId()),
            $this->getApiSeHelper()->getUrl('catalogsearch/result', false, $store->getId()) . '?q=test',
            $this->getApiSeHelper()->getAsyncUrl(false, $store->getId()),
            $product->getProductUrl(),
            $configurableProduct->getProductUrl(),
            $objectManager->create(\Magento\Cms\Helper\Page::class)->getPageUrl($page->getId()),
            $category->getUrl(),
        ];

        foreach ($pagesToCheck as $url) {
            list($h, $response) = $this->getApiSeHelper()->httpRequest(
                \Zend_Http_Client::GET,
                $url,
                [],
                [],
                [],
                30
            );
            
            if (empty($h) || empty($response) || $h['status_code'] != 200) {
                $output->writeln('<error>Unable to get content for url: ' . $url . '</error>');
            }
        }

        /**
         * Test search functionality
         */
        $searchResults = $this->search('test');

        if (empty($searchResults)) {
            $output->writeln('<error>Search error!.</error>');
            $product->delete();
            $configurableProduct->delete();
            $page->delete();
            $category->delete();

            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $productFound = $pageFound = $categoryFound = false;

        foreach ($searchResults[0] as $item) {
            if ($item['product_id'] == $product->getId()) {
                $productFound = true;
            }
        }

        foreach ($searchResults[1] as $catItem) {
            if ($catItem['category_id'] == $category->getId()) {
                $categoryFound = true;
            }
        }
        
        foreach ($searchResults[2] as $pageItem) {
            if ($pageItem['page_id'] == $page->getId()) {
                $pageFound = true;
            }
        }

        if (!$productFound) {
            $output->writeln('<error>Product NOT found!.</error>');
        }

        if (!$categoryFound) {
            $output->writeln('<error>Category NOT found!.</error>');
        }

        if (!$pageFound) {
            $output->writeln('<error>Page NOT found!.</error>');
        }

        if (!$pageFound || !$categoryFound || !$productFound) {
            $product->delete();
            $configurableProduct->delete();
            $page->delete();
            $category->delete();

            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $output->writeln('<info>Search was processed.</info>');

        /**
         * Test delete items functionality
         */
        $output->writeln('<info>Removing created objects...</info>');
        $product->delete();
        $children = $configurableProduct
            ->getTypeInstance()
            ->getUsedProducts($configurableProduct);
        foreach ($children as $child) {
            $child->delete();
        }
        $configurableProduct->delete();
        $page->delete();
        $category->delete();
        //$_store->delete();

        if ($this->queueFactory->create()->getTotalItems() < 3) {
            $output->writeln('<error>Objects were NOT added to queue!</error>');

            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        if ($this->processQueue($output) != \Magento\Framework\Console\Cli::RETURN_SUCCESS) {
            $output->writeln('<error>Unable to process delete queue.</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $output->writeln('<info>Waiting 30 sec.</info>');
        sleep(30);

        $searchResults = $this->search('test');

        if (empty($searchResults)) {
            $output->writeln('<error>Search error!.</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $productFound = $pageFound = $categoryFound = false;

        foreach ($searchResults[0] as $item) {
            if ($item['product_id'] == $product->getId()) {
                $productFound = true;
            }
        }

        foreach ($searchResults[1] as $catItem) {
            if ($catItem['category_id'] == $category->getId()) {
                $categoryFound = true;
            }
        }
        
        foreach ($searchResults[2] as $pageItem) {
            if ($pageItem['page_id'] == $page->getId()) {
                $pageFound = true;
            }
        }

        if ($productFound) {
            $output->writeln('<error>Product was NOT deleted!.</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        if ($categoryFound) {
            $output->writeln('<error>Category was NOT deleted!.</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        if ($pageFound) {
            $output->writeln('<error>Page was NOT deleted!.</error>');
            return \Magento\Framework\Console\Cli::RETURN_FAILURE;
        }

        $output->writeln('<info>Search was processed.</info>');

        $output->writeln('<info>All tests completed successfully!</info>');

        return \Magento\Framework\Console\Cli::RETURN_SUCCESS;
    }
}
