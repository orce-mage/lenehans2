<?php

namespace Searchanise\SearchAutocomplete\Model;

use Magento\Framework\Registry;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event;
use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\App\RequestInterface as HttpRequestInterface;
use Magento\Catalog\Model\ResourceModel\Eav\AttributeFactory;
use Magento\CatalogImportExport\Model\Import\Product as ImportProduct;
use Magento\ConfigurableProduct\Model\Product\Type\ConfigurableFactory as ConfigurableProductTypeFactory;
use Magento\GroupedProduct\Model\Product\Type\GroupedFactory as GroupedProductTypeFactory;
use Magento\Bundle\Model\Product\TypeFactory as BundleProductTypeFactory;
use Magento\Catalog\Model\ProductFactory;
use Magento\Eav\Model\EntityFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Store\Model\Store as StoreModel;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\Product\Type as ProductType;

use Searchanise\SearchAutocomplete\Helper\ApiSe as ApiSeHelper;
use Searchanise\SearchAutocomplete\Helper\ApiProducts;
use Searchanise\SearchAutocomplete\Helper\Data as DataSeHelper;
use Searchanise\SearchAutocomplete\Helper\Logger as SeLogger;
use Searchanise\SearchAutocomplete\Helper\Notification as SeNotification;
use Searchanise\SearchAutocomplete\Model\QueueFactory;
use Searchanise\SearchAutocomplete\Model\Queue;

class Observer implements ObserverInterface
{
    /**
     * @var ApiSeHelper
     */
    private $apiSeHelper;

    /**
     * @var ApiProducts
     */
    private $apiProducts;

    /**
     * @var DataSeHelper
     */
    private $dataHelper;

    /**
     * @var SeLogger
     */
    private $loggerHelper;

    /**
     * @var SeNotification
     */
    private $notificationHelper;

    /**
     * @var QueueFactory
     */
    private $queueFactory;

    /**
     * @var ConfigurableProductTypeFactory
     */
    private $configurableProductTypeFactory;

    /**
     * @var GroupedProductTypeFactory
     */
    private $groupedProductProductTypeGroupedFactory;

    /**
     * @var BundleProductTypeFactory
     */
    private $bundleProductTypeFactory;

    /**
     * @var ProductFactory
     */
    private $catalogProductFactory;

    /**
     * @var EntityFactory
     */
    private $eavEntityFactory;

    /**
     * @var HttpRequest
     */
    private $request;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var OrderFactory $orderFactory
     */
    private $orderFactory;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var boolean
     */
    private $isExistsCategory = false;

    /**
     * @var array
     */
    private $productIdsInCategory = [];

    public function __construct(
        ApiSeHelper $apiSeHelper,
        ApiProducts $apiProducts,
        DataSeHelper $dataHelper,
        SeLogger $loggerHelper,
        SeNotification $notificationHelper,
        QueueFactory $queueFactory,
        ConfigurableProductTypeFactory $configurableProductTypeFactory,
        GroupedProductTypeFactory $groupedProductProductTypeGroupedFactory,
        BundleProductTypeFactory $bundleProductTypeFactory,
        HttpRequestInterface $request,
        StoreManagerInterface $storeManager,
        AttributeFactory $eavEntityFactory,
        ProductFactory $catalogProductFactory,
        OrderFactory $orderFactory,
        Registry $registry
    ) {
        $this->apiSeHelper = $apiSeHelper;
        $this->apiProducts = $apiProducts;
        $this->dataHelper = $dataHelper;
        $this->loggerHelper = $loggerHelper;
        $this->notificationHelper = $notificationHelper;
        $this->queueFactory = $queueFactory;
        $this->configurableProductTypeFactory = $configurableProductTypeFactory;
        $this->groupedProductProductTypeGroupedFactory = $groupedProductProductTypeGroupedFactory;
        $this->bundleProductTypeFactory = $bundleProductTypeFactory;
        $this->request = $request;
        $this->storeManager = $storeManager;
        $this->eavEntityFactory = $eavEntityFactory;
        $this->catalogProductFactory = $catalogProductFactory;
        $this->orderFactory = $orderFactory;
        $this->registry = $registry;
    }

    /**
     * Returns a valid method name
     *
     * @param Event $event
     */
    private function getMethodName(Event $event)
    {
        return lcfirst(implode('', array_map('ucfirst', explode('_', $event->getName()))));
    }

    /**
     * Execute observer action
     *
     * @param EventObserver $observer
     */
    public function execute(EventObserver $observer)
    {
        $method_name = $this->getMethodName($observer->getEvent());

        if (method_exists($this, $method_name)) {
            try {
                $this->{$method_name}($observer->getEvent());
            } catch (\Exception $e) {
                $this->loggerHelper->log(
                    __("Error: Observer::Execute() for method '%1': [%2] %3", $method_name, $e->getCode(), $e->getMessage()),
                    SeLogger::TYPE_ERROR
                );
                $this->notificationHelper->setNotification(
                    SeNotification::TYPE_ERROR,
                    __('Error'),
                    __(
                        'Searchanise: Unable to add object data to queue. Error occurs: %1. Please contact Searchanise <a href="mailto:%2">%3</a> technical support',
                        $e,
                        ApiSeHelper::SUPPORT_EMAIL,
                        ApiSeHelper::SUPPORT_EMAIL
                    )
                );
                // Uncomment for debug
                //debug_print_backtrace(debug_print_backtrace);
            }
        }
    }

    /********************************
     * Layout events
     ********************************/

     /**
      * Before loading page
      *
      * @param Event $event

      * @return Observer
      */
    private function layoutLoadBefore(Event $event)
    {
        $layout = $event->getData('layout');
        $api_key = $this->apiSeHelper->getApiKey();

        if (
            $layout
            && $this->apiSeHelper->checkStatusModule()
            && ($this->apiSeHelper->getIsAdmin() || !empty($api_key))
        ) {
            $layout->getUpdate()->addHandle('searchanise_handler');
        }

        return $this;
    }

    /********************************
     * Product events
     ********************************/

    /**
     * Before save product
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function catalogProductSaveBefore(Event $event)
    {
        $this->queueFactory->create()->addActionDeleteProductFromOldStore($event->getProduct());

        return $this;
    }

    /**
     * After save product
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function catalogProductSaveAfter(Event $event)
    {
        $product = $event->getProduct();

        if (!empty($product)) {
            $this->addProductToQueue($product);
        }

        return $this;
    }

    /**
     * Before delete product
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function catalogProductDeleteBefore(Event $event)
    {
        $this->queueFactory->create()->addActionDeleteProduct($event->getProduct());

        return $this;
    }

    /**
     * Product attribute update
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function catalogProductAttributeUpdateBefore(Event $event)
    {
        $productIds = $event->getData('product_ids');

        if (!empty($productIds) && is_array($productIds)) {
            $this->queueFactory->create()->addActionProductIds($productIds);
        }

        return $this;
    }

    /**
     * Product website update
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function catalogProductToWebsiteChange(Event $event)
    {
        $productIds = $event->getData('products');
        $request = $this->request->getPost();

        $storeAddIds = $this->apiSeHelper->getStoreByWebsiteIds($request->get('add_website_ids'));
        $storeRemoveIds = $this->apiSeHelper->getStoreByWebsiteIds($request->get('remove_website_ids'));

        if (!empty($storeAddIds) && !empty($productIds)) {
            foreach ($productIds as $k => $productId) {
                foreach ($storeAddIds as $k => $storeId) {
                    $this->queueFactory->create()->addAction(
                        Queue::ACT_UPDATE_PRODUCTS,
                        $productId,
                        $storeId
                    );
                }
            }
        }

        if (!empty($storeRemoveIds) && !empty($productIds)) {
            foreach ($productIds as $k => $productId) {
                // TODO: Deprecated
                $productOld = $this->catalogProductFactory->create()->load($productId);

                if (!empty($productOld)) {
                    $storeIdsOld = $productOld->getStoreIds();

                    if (!empty($storeIdsOld)) {
                        foreach ($storeRemoveIds as $k => $storeId) {
                            if (in_array($storeId, $storeIdsOld)) {
                                $this->queueFactory->create()->addAction(
                                    Queue::ACT_DELETE_PRODUCTS,
                                    $productId,
                                    null,
                                    $storeId
                                );
                            }
                        }
                    }
                }
            }
        }

        return $this;
    }

    /********************************
     * Product reviews
     ********************************/
    private function reviewUpdate(Event $event)
    {
        $review = $event->getDataObject();

        if (!empty($review)) {
            $productId = $review->getEntityPkValue();
            $storeAddIds = (array)$review->getStores();

            foreach ($storeAddIds as $storeId) {
                if (!empty($storeId)) {
                    $this->queueFactory->create()->addAction(
                        Queue::ACT_UPDATE_PRODUCTS,
                        $productId,
                        null,
                        $storeId
                    );
                }
            }
        }

        return $this;
    }

    private function reviewSaveAfter(Event $event)
    {
        return $this->reviewUpdate($event);
    }

    private function reviewDeleteAfter(Event $event)
    {
        return $this->reviewUpdate($event);
    }

    /********************************
     * Product import events
     ********************************/

    /**
     * Delete product after import
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function catalogProductImportBunchDeleteAfter(Event $event)
    {
        if ($products = $event->getBunch()) {
            $idToDelete = [];
            $oldSku = $event->getAdapter()->getOldSku();

            foreach ($products as $product) {
                $sku = strtolower($product[ImportProduct::COL_SKU]);

                if (isset($oldSku[$sku])) {
                    $idToDelete[] = $oldSku[$sku]['entity_id'];
                }
            }

            if (!empty($idToDelete)) {
                $this->queueFactory
                    ->create()
                    ->addActionDeleteProductIds($idToDelete);
            }
        }

        return $this;
    }

    /**
     * Update products after import
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function catalogProductImportBunchSaveAfter(Event $event)
    {
        if ($products = $event->getBunch()) {
            $productIds = [];

            foreach ($products as $product) {
                $newSku = $event->getAdapter()->getNewSku($product[ImportProduct::COL_SKU]);

                if (empty($newSku) || !isset($newSku['entity_id'])) {
                    continue;
                }

                $productIds[] = $newSku['entity_id'];
            }

            if (!empty($productIds)) {
                $this->queueFactory
                    ->create()
                    ->addActionProductIds($productIds);
            }
        }

        return $this;
    }

    /********************************
     * Category events
     ********************************/

    /**
     * Save category before
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function catalogCategorySaveBefore(Event $event)
    {
        $category = $event->getCategory();

        if ($category && $category->getId()) {
            $this->isExistsCategory = true; // New category doesn't run the catalogCategorySaveBefore function.
            // For category
            $this->queueFactory->create()->addActionCategory($category);

            // For products from category
            // It save before because products could remove from $category.
            $products = $category->getProductCollection();
            $this->queueFactory->create()->addActionProducts($products);

            // save current products ids
            // need for find new products in catalogCategorySaveAfter
            if ($products) {
                $this->productIdsInCategory = array_filter($products->getAllIds());
            }
        }

        return $this;
    }

    /**
     * Save category after
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function catalogCategorySaveAfter(Event $event)
    {
        $category = $event->getCategory();

        if ($category && $category->getId()) {
            // For category
            if (!$this->isExistsCategory) { // if category was created now
                $this->queueFactory->create()->addActionCategory($category);
            }

            // For products from category
            $products = $category->getProductCollection();

            if (!empty($products)) {
                if (empty($this->productIdsInCategory)) {
                    // Adds all products in category
                    $this->queueFactory->create()->addActionProducts($products);
                } else {
                    // Add only new products in category
                    $productIds = array_filter($products->getAllIds());
                    $productIds = array_diff($productIds, $this->productIdsInCategory);

                    $this->queueFactory->create()->addActionProductIds($productIds);
                }
            }
        }

        $this->isExistsCategory = false;
        $this->productIdsInCategory = [];

        return $this;
    }

    /**
     * Move category after
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function catalogCategoryMoveAfter(Event $event)
    {
        $category = $event->getCategory();

        if ($category && $category->getId()) {
            $products = $category->getProductCollection();

            if ($products) {
                $this->queueFactory->create()->addActionProducts($products);
            }
        }

        return $this;
    }

    /**
     * Delete category before
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function catalogCategoryDeleteBefore(Event $event)
    {
        $category = $event->getCategory();

        if ($category && $category->getId()) {
            // For category
            $this->queueFactory->create()->addActionCategory(
                $category,
                Queue::ACT_DELETE_CATEGORIES
            );

            // For products from category
            $products = $category->getProductCollection();
            // ToCheck:
            // $this->queueFactory->create()->addActionProducts($products);
        }

        return $this;
    }

    /********************************
     * Store events
     ********************************/

    private function modelSaveBefore(Event $event)
    {
        $object = $event->getData('object');

        if ($object instanceof StoreModel) {
            $this->storeBeforeEdit($object);
        }

        return $this;
    }

    /**
     * Before delete store
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function storeDeleteBefore(Event $event)
    {
        $store = $event->getData('store');

        if ($store && $store->getId()) {
            $this->apiSeHelper->deleteKeysForStore($store);
        }

        return $this;
    }

    /**
     * Before save store
     *
     * @param StoreModel $event
     *
     * @return Observer
     */
    private function storeBeforeEdit(StoreModel $store)
    {
        $this->registry->register(
            'store_save_before' . $store->getId(),
            $this->storeManager->getStore($store->getId())
        );

        return $this;
    }

    /**
     * After save store
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function storeEdit(Event $event)
    {
        $store = $event->getData('store');
        $response = $event->getData('response');

        if ($store && $store->getId()) {
            $isActive = $store->getIsActive();
            $isActiveOld = false;
            $this->apiSeHelper->setHttpResponse($response);

            $storeOld = $this->registry->registry('store_save_before' . $store->getId());

            if ($storeOld) {
                $isActiveOld = $storeOld->getIsActive();
                $this->registry->unregister('store_save_before' . $store->getId());
            }

            if ($isActiveOld != $isActive) {
                if ($this->apiSeHelper->signup($store, false, false) == true) {
                    if ($isActive) {
                        $this->apiSeHelper->sendAddonStatusRequest(ApiSeHelper::SE_ADDON_STATUS_ENABLED, $store);
                        $this->apiSeHelper->queueImport($store->getId(), false);
                        $this->notificationHelper->setNotification(
                            SeNotification::TYPE_NOTICE,
                            __('Notice'),
                            __('Searchanise: New search engine for %1 created. Catalog import started', $store->getName())
                        );
                    } else {
                        $this->apiSeHelper->sendAddonStatusRequest(ApiSeHelper::SE_ADDON_STATUS_DISABLED, $store);
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Add store
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function storeAdd(Event $event)
    {
        $store = $event->getData('store');
        $response = $event->getData('response');

        if ($store && $store->getId()) {
            // Create new store. Set empty value to 'PrivateKey' and 'ApiKey'
            $this->apiSeHelper->setApiKey('', $store->getId());
            $this->apiSeHelper->setPrivateKey('', $store->getId());

            // Reset store config
            $store->resetConfig();

            $checkPrivateKey = $this->apiSeHelper->checkPrivateKey($store->getId());
            $this->apiSeHelper->setHttpResponse($response);

            if ($this->apiSeHelper->signup($store, false, false) == true) {
                if (!$checkPrivateKey) {
                    if ($store->getIsActive()) {
                        $this->apiSeHelper->queueImport($store->getId(), false);
                        $this->notificationHelper->setNotification(
                            SeNotification::TYPE_NOTICE,
                            __('Notice'),
                            __('Searchanise: New search engine for %1 created. Catalog import started', $store->getName())
                        );
                    }
                }
            }
        }

        return $this;
    }

    /**
     * Save config 'Advanced' section
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function adminSystemConfigChangedSectionAdvanced(Event $event)
    {
        $groups = $this->request->getPost()->get('groups');
        $storesIds = $event->getData('store');
        $websiteIds = $event->getData('website');
        $response = $event->getData('response');

        if (empty($storesIds) && !empty($websiteIds)) {
            $storesIds = $this->apiSeHelper->getStoreByWebsiteIds($websiteIds);
        }

        $stores = $this->apiSeHelper->getStores($storesIds);
        $this->apiSeHelper->setHttpResponse($response);

        if (!empty($stores) && !empty($groups)) {
            foreach ($groups as $group => $groupData) {
                if (isset($groupData['fields']['Searchanise_SearchAutocomplete']['value'])) {
                    $status = ($groupData['fields']['Searchanise_SearchAutocomplete']['value']) ? 'D' : 'Y';

                    foreach ($stores as $k => $store) {
                        if (!$store->getIsActive() || $this->apiSeHelper->getStatusModule($store) == $status) {
                            continue;
                        } elseif (!$this->apiSeHelper->signup($store, false, false)) {
                            continue;
                        } elseif ($status != 'Y') {
                            $this->apiSeHelper->sendAddonStatusRequest('disabled', $store);
                            continue;
                        }

                        $this->apiSeHelper->sendAddonStatusRequest('enabled', $store);
                        $this->apiSeHelper->queueImport($store, false);
                        $this->notificationHelper->setNotification(
                            SeNotification::TYPE_NOTICE,
                            __('Notice'),
                            str_replace(
                                '[language]',
                                $store->getName(),
                                __('Searchanise: New search engine for [language] created. Catalog import started')
                            )
                        );
                    }
                }
            }
        }

        return $this;
    }

    /********************************
     * EAV
     ********************************/

    /**
     * Before save attribute
     *
     * @param  Event $event
     *
     * @return Observer
     */
    private function catalogEntityAttributeSaveBefore(Event $event)
    {
        $attribute = $event->getAttribute();

        if ($attribute && $attribute->getId()) {
            $isFacet = $this->apiProducts->isFacet($attribute);

            $isFacetPrev = null;

            $prevAttribute = $this->eavEntityFactory->create()
                ->load($attribute->getId());

            if ($prevAttribute) {
                $isFacetPrev = $this->apiProducts->isFacet($prevAttribute);
            }

            if ($isFacet != $isFacetPrev) {
                if (!$isFacet) {
                    $this->queueFactory->create()->addAction(
                        Queue::ACT_DELETE_FACETS,
                        $attribute->getId()
                    );
                }
            }
        }

        return $this;
    }

    /**
     * Save attribute
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function catalogEntityAttributeSaveAfter(Event $event)
    {
        $attribute = $event->getAttribute();

        if ($attribute && $attribute->getId()) {
            $this->queueFactory->create()->addAction(
                Queue::ACT_UPDATE_ATTRIBUTES,
                $attribute->getId()
            );
        }

        return $this;
    }

    /**
     * Delete attribute
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function catalogEntityAttributeDeleteAfter(Event $event)
    {
        $attribute = $event->getAttribute();

        if ($attribute && $attribute->getId()) {
            if ($this->apiProducts->isFacet($attribute)) {
                $this->queueFactory->create()->addAction(
                    Queue::ACT_DELETE_FACETS,
                    $attribute->getId()
                );
            }
        }

        return $this;
    }

    /********************************
     * Pages events
     ********************************/

    /**
     * Delete page before
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function cmsPageDeleteBefore(Event $event)
    {
        $page = $event->getObject();

        if ($page && $page->getId()) {
            $this->queueFactory->create()->addActionPage(
                $page,
                Queue::ACT_DELETE_PAGES
            );
        }

        return $this;
    }

    /**
     * Save page after
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function cmsPageSaveAfter(Event $event)
    {
        $page = $event->getObject();

        if ($page && $page->getId()) {
            $this->queueFactory->create()->addActionPage($page);
        }

        return $this;
    }

    /********************************
     * Orders event
     ********************************/

    /**
     * Place order action
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function checkoutOnepageControllerSuccessAction(Event $event)
    {
        $orderIds = $event->getOrderIds();

        if (!empty($orderIds)) {
            $order = $this->orderFactory->create()->load($orderIds[0]);
            $orderItems = $order->getAllItems();

            foreach ($orderItems as $orderItem) {
                $product = $orderItem->getProduct();

                if ($product) {
                    $this->addProductToQueue($product);
                }
            }
        }

        return $this;
    }

    /**
     * Updage order action
     *
     * @param Event $event
     *
     * @return Observer
     */
    private function salesOrderSaveAfter(Event $event)
    {
        $order = $event->getOrder();

        if ($order) {
            $orderItems = $order->getAllItems();

            foreach ($orderItems as $orderItem) {
                $product = $orderItem->getProduct();

                if ($product) {
                    $this->addProductToQueue($product);
                }
            }
        }

        return $this;
    }

    /**
     * Add product to queue and it's parents
     *
     * @param $product ProductModel
     *
     * @return Observer
     */
    private function addProductToQueue(ProductModel $product)
    {
        $this->queueFactory->create()->addActionUpdateProduct($product);

        if ($product->getTypeId() == ProductType::TYPE_SIMPLE) {
            $parent_ids_arr = array_merge(
                $this->configurableProductTypeFactory->create()->getParentIdsByChild($product->getId()),
                $this->groupedProductProductTypeGroupedFactory->create()->getParentIdsByChild($product->getId()),
                $this->bundleProductTypeFactory->create()->getParentIdsByChild($product->getId())
            );

            if (!empty($parent_ids_arr)) { // If there is one or more parent products.
                $parent_ids_arr = array_unique($parent_ids_arr);

                foreach ($parent_ids_arr as $product_id) { // Update all detected parent products.
                    // TODO: Deprecated
                    $product = $this->catalogProductFactory->create()->load($product_id);

                    if (!empty($product)) {
                        $this->queueFactory->create()->addActionUpdateProduct($product);
                    }
                }
            }
        }

        return $this;
    }
}
