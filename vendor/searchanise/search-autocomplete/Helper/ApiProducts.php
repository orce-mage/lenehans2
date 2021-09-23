<?php

namespace Searchanise\SearchAutocomplete\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Data\Collection as DataCollection;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Helper\Data as CatalogDataHelper;
use Magento\Catalog\Helper\ImageFactory;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ProductCollection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory as CatalogProductAttributeCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollectionFactory;
use Magento\Catalog\Model\ResourceModel\Eav\Attribute as ProductAttribute;
use Magento\Catalog\Model\Product\Attribute\Source\Status as ProductStatus;
use Magento\Catalog\Model\Product\Visibility as ProductVisibility;
use Magento\Catalog\Model\CategoryFactory;
use Magento\Catalog\Model\Layer\Filter\DataProvider\Price as DataProviderPrice;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Model\Configuration as CatalogInventoryConfiguration;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\Store as StoreModel;
use Magento\Catalog\Model\Product as ProductModel;
use Magento\Catalog\Model\Product\Image as ProductImage;
use Magento\Tax\Helper\Data as TaxDataHelper;
use Magento\Review\Model\ReviewFactory;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\GroupedProduct\Model\Product\Type\Grouped as ProductTypeGrouped;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ProductTypeConfigurable;
use Magento\Catalog\Pricing\Price\RegularPrice;
use Magento\Bundle\Model\Product\Price as BundleProductPrice;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerGroupCollectionFactory;
use Magento\Customer\Model\Group as CustomerGroupModel;
use Magento\Customer\Model\GroupManagement as CustomerGroupManagement;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;
use Magento\CatalogUrlRewrite\Model\ProductUrlRewriteGenerator;

use Searchanise\SearchAutocomplete\Model\Configuration;
use Searchanise\SearchAutocomplete\Helper\Logger as SeLogger;
use Searchanise\SearchAutocomplete\Helper\ApiSe as ApiSeHelper;

/**
 * Products helper for searchanise
 */
class ApiProducts extends AbstractHelper
{
    const WEIGHT_SHORT_TITLE         = 100;
    const WEIGHT_SHORT_DESCRIPTION   = 40;
    const WEIGHT_DESCRIPTION         = 40;
    const WEIGHT_DESCRIPTION_GROUPED = 30;

    const WEIGHT_TAGS              = 60;
    const WEIGHT_CATEGORIES        = 60;

    // <if_isSearchable>
    const WEIGHT_META_TITLE        =  80;
    const WEIGHT_META_KEYWORDS     = 100;
    const WEIGHT_META_DESCRIPTION  =  40;

    const WEIGHT_SELECT_ATTRIBUTES    = 60;
    const WEIGHT_TEXT_ATTRIBUTES      = 60;
    const WEIGHT_TEXT_AREA_ATTRIBUTES = 40;
    // </if_isSearchable>

    const IMAGE_SIZE = 300;
    const THUMBNAIL_SIZE = 70;

    // Product types which as children
    public $hasChildrenTypes = [
        ProductType::TYPE_BUNDLE,
        ProductTypeGrouped::TYPE_CODE,
        ProductTypeConfigurable::TYPE_CODE
    ];

    public $flWithoutTags = false;
    public $isGetProductsByItems = false;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ProductCollectionFactory
     */
    private $catalogResourceModelProductCollectionFactory;

    /**
     * @var CustomerGroupCollectionFactory
     */
    private $customerGroupCollectionFactory;

    /**
     * @var CatalogProductAttributeCollectionFactory
     */
    private $catalogProductAttributeCollectionFactory;

    /**
     * @var CatalogProductAttributeCollectionFactory
     */
    private $categoryCollectionFactory;

    /**
     * @var TaxDataHelper
     */
    private $taxHelper;

    /**
     * @var CatalogDataHelper
     */
    private $catalogHelper;

    /**
     * @var CategoryFactory
     */
    private $categoryFactory;

    /**
     * @var Configuration
     */
    private $configuration;

    /**
     * @var SeLogger
     */
    private $loggerHelper;

    /**
     * @var ImageFactory
     */
    private $catalogImageFactory;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var ProductStatus
     */
    private $productStatus;

    /**
     * @var ResourceConnection
     */
    private $resourceConnection;

    /**
     * @var ProductVisibility
     */
    private $productVisibility;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        ProductCollectionFactory $catalogResourceModelProductCollectionFactory,
        CustomerGroupCollectionFactory $customerGroupCollectionFactory,
        CatalogProductAttributeCollectionFactory $catalogProductAttributeCollectionFactory,
        CategoryCollectionFactory $categoryCollectionFactory,
        TaxDataHelper $taxHelper,
        CatalogDataHelper $catalogHelper,
        CategoryFactory $categoryFactory,
        Configuration $configuration,
        SeLogger $loggerHelper,
        ImageFactory $catalogImageFactory,
        StockRegistryInterface $stockRegistry,
        DateTime $dateTime,
        ReviewFactory $reviewFactory,
        ResourceConnection $resourceConnection,
        ProductStatus $productStatus,
        ProductVisibility $productVisibility
    ) {
        $this->storeManager = $storeManager;
        $this->configuration = $configuration;
        $this->catalogResourceModelProductCollectionFactory = $catalogResourceModelProductCollectionFactory;
        $this->customerGroupCollectionFactory = $customerGroupCollectionFactory;
        $this->catalogProductAttributeCollectionFactory = $catalogProductAttributeCollectionFactory;
        $this->categoryCollectionFactory = $categoryCollectionFactory;
        $this->loggerHelper = $loggerHelper;
        $this->taxHelper = $taxHelper;
        $this->catalogHelper = $catalogHelper;
        $this->categoryFactory = $categoryFactory;
        $this->catalogImageFactory = $catalogImageFactory;
        $this->stockRegistry = $stockRegistry;
        $this->dateTime = $dateTime;
        $this->reviewFactory = $reviewFactory;
        $this->resourceConnection = $resourceConnection;
        $this->productStatus = $productStatus;
        $this->productVisibility = $productVisibility;

        parent::__construct($context);
    }

    /**
     * Returns module Manager class
     *
     * @return \Magento\Framework\Module\Manager
     */
    public function getModuleManager()
    {
        if (property_exists($this, 'moduleManager')) {
            $moduleManager = $this->moduleManager;
        } else {
            $moduleManager = ObjectManager::getInstance()
                ->get(\Magento\Framework\Module\Manager::class);
        }

        return $moduleManager;
    }

    /**
     * Checks if magento version is more than
     *
     * @param string $version Version for check
     *
     * @return bool
     */
    public function isVersionMoreThan($version)
    {
        $magentoVersion = ObjectManager::getInstance()
            ->get(ApiSeHelper::class)
            ->getMagentoVersion();

        return version_compare($magentoVersion, $version, '>=');
    }

    /**
     * Returns products collection
     *
     * @return ProductCollection
     */
    public function getProductCollection()
    {
        $objectManager = ObjectManager::getInstance();
        $version = $objectManager
            ->get(ApiSeHelper::class)
            ->getMagentoVersion();

        if ($this->isVersionMoreThan('2.2')) {
            static $collectionFactory = null;

            if (!$collectionFactory) {
                $collectionFactory = $objectManager->create('Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
            }

            $collection = $collectionFactory
                ->create()
                ->clear();
        } else {
            static $catalogProductFactory = null;

            if (!$catalogProductFactory) {
                $catalogProductFactory = $objectManager->get('\Magento\Catalog\Model\ProductFactory');
            }

            $collection = $catalogProductFactory
                ->create()
                ->getCollection();
        }

        return $collection;
    }

    /**
     * Returns image base url for store
     *
     * @param StoreModel $store Store data
     *
     * @return string
     */
    public static function getImageBaseUrl(StoreModel $store)
    {
        return ObjectManager::getInstance()
            ->get(ApiSeHelper::class)
            ->getStoreUrl($store->getId())
            ->getUrl('pub/media/catalog', [
                '_scope' => $store->getId(),
                '_nosid' => true,
            ]) . 'product';
    }

    /**
     * Returns frontend url model
     *
     * @param StoreModel $store
     *
     * @return mixed
     */
    public static function getUrlInstance(StoreModel $store)
    {
        return ObjectManager::getInstance()
            ->get(ApiSeHelper::class)
            ->getStoreUrl($store->getId());
    }

    /**
     * Loads single product by id
     *
     * @param int $productId
     *
     * @return ProductModel|null
     */
    public function loadProductById($productId)
    {
        // TODO: Load() method is deprected here since 2.2.1. Should be replaced in future
        $product = ObjectManager::getInstance()
            ->get(\Magento\Catalog\Model\ProductFactory::class)
            ->create()
            ->load($productId);

        return $product;
    }

    /**
     * Sets isGetProductsByItems value
     *
     * @param bool $value
     */
    public function setIsGetProductsByItems($value = false)
    {
        $this->isGetProductsByItems = $value;
    }

    /**
     * Returns required attributes list
     *
     * @return array
     */
    private function getRequiredAttributes()
    {
        return [
            'name',
            'short_description',
            'sku',
            'status',
            'visibility',
            'price',
        ];
    }

    /**
     * Returns all product attributes
     */
    public function getAllAttributes()
    {
        $basicAttributes = [
            'name',
            'path',
            'categories',
            'categories_without_path',
            'description',
            'ordered_qty',
            'total_ordered',
            'stock_qty',
            'rating_summary',
            'media_gallery',
            'in_stock',
        ];
        $requiredAttributes = $this->getRequiredAttributes();

        $additionalAttributes = [];
        $productAttributes = $this->getProductAttributes();
        if (!empty($productAttributes)) {
            foreach ($productAttributes as $attribute) {
                $additionalAttributes[] = $attribute->getAttribute();
            }
        }

        return array_unique(array_merge($requiredAttributes, $basicAttributes, $additionalAttributes));
    }

    /**
     * Generate product feed for searchanise api
     *
     * @param array      $productIds Product ids
     * @param StoreModel $store      Store object
     * @param string     $checkData
     *
     * @return array
     */
    public function generateProductsFeed(
        array $productIds = [],
        StoreModel $store = null,
        $checkData = true
    ) {
        $items = [];

        $startTime = microtime(true);

        $products = $this->getProducts($productIds, $store);

        if (!empty($products)) {
            $this->generateChildrenProducts($products);

            foreach ($products as $product) {
                if ($item = $this->generateProductFeed($product, $store, $checkData)) {
                    $items[] = $item;
                }
            }
        }

        $endTime = microtime(true);

        $this->loggerHelper->log(
            sprintf("===== ApiProducts::generateProductsFeed() for %d products takes %0.2f ms =====", count($productIds), $endTime - $startTime),
            SeLogger::TYPE_DEBUG
        );

        return $items;
    }

    /**
     * Get product minimal price without "Tier Price" (quantity discount) and with tax (if it is need)
     *
     * @param ProductModel      $product           Product model
     * @param StoreModel        $store             Store model
     * @param ProductCollection $childrenProducts  United products
     * @param int               $customerGroupId   Customer group identifier
     * @param bool              $applyTax          if true, tax will be applied
     *
     * @return float
     */
    private function getProductMinimalPrice(
        ProductModel $product,
        StoreModel $store,
        $childrenProducts = null,
        $customerGroupId = null,
        $applyTax = true
    ) {
        $minimalPrice = false;
        $tierPrice = $this->getMinimalTierPrice($product, $customerGroupId);

        if ($product->getTypeId() == ProductType::TYPE_BUNDLE) {
            $product->setCustomerGroupId(0);
            $minimalPrice = $product->getPriceModel()->getTotalPrices($product, 'min', null, false);

            if ($tierPrice != null) {
                $minimalPrice = min($minimalPrice, $tierPrice);
            }
        } elseif (!empty($childrenProducts)
            && ($product->getTypeId() == ProductTypeGrouped::TYPE_CODE
            || $product->getTypeId() == ProductTypeConfigurable::TYPE_CODE)
        ) {
            $prices = [];
            foreach ($childrenProducts as $childrenProduct) {
                if ($childrenProduct->getStatus() != ProductStatus::STATUS_ENABLED) {
                    continue;
                }

                $prices[] = $this->getProductMinimalPrice(
                    $childrenProduct,
                    $store,
                    null,
                    $customerGroupId,
                    false
                );
            }

            if (!empty($prices)) {
                $minimalPrice = min($prices);
            }
        }

        if ($minimalPrice === false) {
            $minimalPrice = $product->getFinalPrice();

            if ($tierPrice !== null) {
                $minimalPrice = min($minimalPrice, $tierPrice);
            }
        }

        if ($minimalPrice && $applyTax) {
            $minimalPrice = $this->getProductShowPrice($product, $minimalPrice);
        }

        return (float)$minimalPrice;
    }

    /**
     * Get product price with tax if it is need
     *
     * @param ProductModel $product Product data
     * @param float        $price   Produt price
     *
     * @return float
     */
    public function getProductShowPrice(ProductModel $product, $price)
    {
        static $taxHelper;
        static $showPricesTax;

        if (!isset($taxHelper)) {
            $taxHelper = $this->taxHelper;
            $showPricesTax = ($taxHelper->displayPriceIncludingTax() || $taxHelper->displayBothPrices());
        }

        // TODO: Test taxes
        $finalPrice = $this->catalogHelper->getTaxPrice($product, $price, $showPricesTax);

        return (float)$finalPrice;
    }

    /**
     * Generate product attributes
     *
     * @param array        $item             Product data
     * @param ProductModel $product          Product model
     * @param array        $childrenProducts List of the children products
     * @param array        $unitedProducts   Unit products
     * @param StoreModel   $store            Store object
     *
     * @return array
     */
    private function generateProductAttributes(
        array &$item,
        ProductModel $product,
        $childrenProducts = null,
        $unitedProducts = null,
        StoreModel $store = null
    ) {
        $startTime = microtime(true);
        $attributes = $this->getProductAttributes();

        if (!empty($attributes)) {
            $requiredAttributes = $this->getRequiredAttributes();

            foreach ($attributes as $attribute) {
                $attributeCode = $attribute->getAttributeCode();
                $value = $product->getData($attributeCode);

                // unitedValues - main value + childrens values
                $unitedValues = $this->getIdAttributesValues($unitedProducts, $attributeCode);

                $inputType = $attribute->getData('frontend_input');
                $isSearchable = $attribute->getIsSearchable();
                $isVisibleInAdvancedSearch = $attribute->getIsVisibleInAdvancedSearch();
                $usedForSortBy = $attribute->getUsedForSortBy();
                $isFilterable = $attribute->getIsFilterable();

                $attributeName = 'attribute_' . $attribute->getId();

                $isNecessaryAttribute = $isSearchable
                    || $isVisibleInAdvancedSearch
                    || $usedForSortBy
                    || $isFilterable
                    || in_array($attributeCode, $requiredAttributes);

                if (!$isNecessaryAttribute) {
                    continue;
                }

                if (empty($unitedValues)) {
                    // nothing
                    // <system_attributes>
                } elseif ($attributeCode == 'price') {
                    // already defined in the '<cs:price>' field
                } elseif ($attributeCode == 'status' || $attributeCode == 'visibility') {
                    $item[$attributeCode] = $value;
                } elseif ($attributeCode == 'has_options') {
                } elseif ($attributeCode == 'required_options') {
                } elseif ($attributeCode == 'custom_layout_update') {
                } elseif ($attributeCode == 'tier_price') { // quantity discount
                } elseif ($attributeCode == 'image_label') {
                } elseif ($attributeCode == 'small_image_label') {
                } elseif ($attributeCode == 'thumbnail_label') {
                } elseif ($attributeCode == 'tax_class_id') {
                } elseif ($attributeCode == 'url_key') { // seo name
                } elseif ($attributeCode == 'category_ids') {
                } elseif ($attributeCode == 'categories') {
                    // <system_attributes>
                } elseif ($attributeCode == 'group_price') {
                    // nothing
                    // fixme in the future if need
                } elseif ($attributeCode == 'short_description'
                    || $attributeCode == 'name'
                    || $attributeCode == 'sku'
                ) {
                    if (count($unitedValues) > 1) {
                        $item['se_grouped_' . $attributeCode] = array_slice($unitedValues, 1);
                    }
                } elseif ($attributeCode == 'description') {
                    $item['full_description'] = $value;

                    if (count($unitedValues) > 1) {
                        $item['se_grouped_full_' . $attributeCode] = array_slice($unitedValues, 1);
                    }
                } elseif ($attributeCode == 'meta_title'
                    || $attributeCode == 'meta_description'
                    || $attributeCode == 'meta_keyword'
                ) {
                    $item[$attributeCode] = $unitedValues;
                } elseif ($inputType == 'price') {
                    // Other attributes with type 'price'.
                    $item[$attributeCode] = $unitedValues;
                } elseif ($inputType == 'select' || $inputType == 'multiselect') {
                    // <text_values>
                    $unitedTextValues = $this->getProductAttributeTextValues(
                        $unitedProducts,
                        $attributeCode,
                        $inputType,
                        $store
                    );
                    $item[$attributeCode] = $unitedTextValues;
                } elseif ($inputType == 'text' || $inputType == 'textarea') {
                    $item[$attributeCode] = $unitedValues;
                } elseif ($inputType == 'date') {
                    //Magento's timestamp function makes a usage of timezone and converts it to timestamp
                    $item[$attributeCode] = $this->dateTime->timestamp(strtotime($value));
                } elseif ($inputType == 'media_image') {
                    if ($this->configuration->getIsUseDirectImagesLinks()) {
                        if (empty($store)) {
                            $store = $this->storeManager->getStore();
                        }

                        $image = self::getImageBaseUrl($store) . $attribute->getImage($attributeCode);
                    } else {
                        $image = $this->generateImage($product, $attributeCode, true, 0, 0);
                    }

                    if (!empty($image)) {
                        $item[$attributeCode] = is_object($image) ? $image->getUrl() : $image;
                    }
                } elseif ($inputType == 'gallery') {
                    // Nothing.
                } else {
                    // Attribute not will use.
                }
            }
        }

        $endTime = microtime(true);

        $this->loggerHelper->log(
            sprintf("===== ApiProducts::generateProductAttributes() takes %0.2f ms =====", $endTime - $startTime),
            SeLogger::TYPE_DEBUG
        );

        return $item;
    }

    /**
     * Generate text values for product attributes for products
     *
     * @param array      $unitedProducts Unit products
     * @param string     $attributeCode  Attribute code
     * @param string     $inputType      Input type (seelct, textarea, multiselect and etc)
     * @param StoreModel $store
     *
     * @return array
     */
    private function getProductAttributeTextValues(
        array $products,
        $attributeCode,
        $inputType,
        StoreModel $store = null
    ) {
        $arrTextValues = [];

        foreach ($products as $p) {
            if ($values = $this->getTextAttributeValues($p, $attributeCode, $inputType, $store)) {
                foreach ($values as $key => $value) {
                    $trimValue = trim($value);
                    if ($trimValue != '' && !in_array($trimValue, $arrTextValues)) {
                        $arrTextValues[] = $value;
                    }
                }
            }
        }

        return $arrTextValues;
    }

    /**
     * Returns text attribute values for product
     *
     * @param ProductModel $product       Product model
     * @param string       $attributeCode Attribute code
     * @param string       $inputType     Input type (seelct, textarea, multiselect and etc)
     * @param StoreModel   $store         Store
     *
     * @return array
     */
    private function getTextAttributeValues(
        ProductModel $product,
        $attributeCode,
        $inputType,
        StoreModel $store = null
    ) {
        $arrTextValues = [];

        if ($product->getData($attributeCode) !== null) {
            $values = [];

            // Dependency of store already exists
            $textValues = $product
                ->getResource()
                ->getAttribute($attributeCode)
                ->setStoreId($store->getId())
                ->getFrontend();

            $use_text_values = $this->configuration->getIsResultsWidgetEnabled($store->getId());

            if ($inputType == 'multiselect') {
                $v = $product->getData($attributeCode);
                $values = $use_text_values ? (array)$this->clearTextAttributeValues($textValues->getOption($v)) : explode(',', $v);
            } else {
                $v = $textValues->getValue($product);

                if (!empty($v)) {
                    $values[] = $use_text_values ? $this->clearTextAttributeValues($v) : $v;
                }
            }

            $arrTextValues = $values;
        }

        return $arrTextValues;
    }

    /**
     * Clear text attribute values
     * 
     * @param mixed $values Attribute values
     *
     * @return mixed
     */
    private function clearTextAttributeValues($values)
    {
        if (empty($values)) {
            $values = '';
        } elseif (is_array($values)) {
            foreach ($values as $k => $v) {
                $values[$k] = $this->clearTextAttributeValues($v);
            }
        } else {
            $values = html_entity_decode(trim($values));
        }

        return $values;
    }

    /**
     * Returns attribute values
     *
     * @param array  $unitedProducts Unit products
     * @param string $attributeCode  Attribute code
     *
     * @return array
     */
    private function getIdAttributesValues($products, $attributeCode)
    {
        $values = [];

        foreach ($products as $productKey => $product) {
            $value = $product->getData($attributeCode);

            if ($value == '') {
                // Nothing.
            } elseif (is_array($value) && empty($value)) {
                // Nothing.
            } else {
                if (!in_array($value, $values)) {
                    $values[] = $value;
                }
            }
        }

        return $values;
    }

    /**
     * Returns thumbnail of product image
     *
     * @param ProductModel $product
     * @param bool         $flagKeepFrame
     * @param bool         $isTumbnail
     * @param StoreModel   $store
     *
     * @return string
     */
    public function getProductImageLink(
        ProductModel $product,
        $flagKeepFrame = true,
        $isThumbnail = true,
        StoreModel $store = null
    ) {
        $image = null;

        if (!empty($product)) {
            if ($this->configuration->getIsUseDirectImagesLinks()) {
                if (empty($store)) {
                    $store = $this->storeManager->getStore();
                }

                $image = ltrim($product->getImage(), '/');
                if (!empty($image)) {
                    $image = self::getImageBaseUrl($store) . '/' . $image;
                }
            } else {
                $this->storeManager->setCurrentStore($store);
                $imageType = $isThumbnail ? 'se_thumbnail' : 'se_image';
                $image = $this->generateImage($product, $imageType, false, false, false);

                if (empty($image)) {
                    // Outdated code, should be removed in future
                    if ($isThumbnail) {
                        $width = $height = self::THUMBNAIL_SIZE;
                    } else {
                        $width = $height = self::IMAGE_SIZE;
                    }

                    foreach (['small_image', 'image', 'thumbnail'] as $imageType) {
                        $image = $this->generateImage($product, $imageType, $flagKeepFrame, $width, $height);

                        if (!empty($image)) {
                            break;
                        }
                    }
                }
            }
        }

        return is_object($image) ? $image->getUrl() : ($image != null ? $image : '');
    }

    /**
     * Genereate image for product
     *
     * @param ProductModel $product
     * @param bool         $flagKeepFrame
     * @param int          $width
     * @param int          $height
     *
     * @return ProductImage $image
     */
    private function generateImage(
        ProductModel $product,
        $imageType = 'small_image',
        $flagKeepFrame = true,
        $width = 70,
        $height = 70
    ) {
        $image = null;
        $objectImage = $product->getData($imageType);

        if (in_array($imageType, ['se_image', 'se_thumbnail']) || !empty($objectImage) && $objectImage != 'no_selection') {
            try {
                $image = $this->catalogImageFactory
                    ->create()
                    ->init($product, $imageType)
                    ->setImageFile($product->getImage());

                if ($width || $height) {
                    $image->constrainOnly(true)  // Guarantee, that image picture will not be bigger, than it was.
                        ->keepAspectRatio(true)      // Guarantee, that image picture width/height will not be distorted.
                        ->keepFrame($flagKeepFrame)  // Guarantee, that image will have dimensions, set in $width/$height
                        ->resize($width, $height);
                }
            } catch (\Exception $e) {
                // image not exists
                $image = null;
            }
        }

        return $image;
    }

    /**
     * Genereate children products
     *
     * @param mixed      $products Products array or collection
     * @param StoreModel $store    Store model
     */
    public function generateChildrenProducts(
        &$products,
        StoreModel $store = null
    ) {
        $childrenIdsGrouped = $childrenIds = [];

        foreach ($products as $product) {
            $childrenIdsGrouped[$product->getId()] = $this->getChildrenProductIds($product, $store);
            $childrenIds = array_merge($childrenIds, $childrenIdsGrouped[$product->getId()]);
        }

        if (!empty($childrenIds)) {
            $childrenProducts = [];
            $childrenProductsCollection = $this->getProducts(array_unique($childrenIds), $store, null, false, false);

            // Convert collection object to array
            foreach ($childrenProductsCollection as $child) {
                $childrenProducts[$child->getId()] = $child;
            }

            if (!empty($childrenProducts)) {
                foreach ($childrenIdsGrouped as $parentId => $chidrenProducts) {
                    $currentChildrenProducts = array_intersect_key($childrenProducts, array_flip($chidrenProducts));

                    if (!empty($currentChildrenProducts)) {
                        if (is_array($products)) {
                            foreach ($products as &$rootProduct) {
                                $rootFound = false;

                                if ($rootProduct->getId() == $parentId) {
                                    $rootProduct->setData('seChildrenProducts', $currentChildrenProducts);
                                    $rootFound = true;
                                    break;
                                }

                                if (!$rootFound) {
                                    $this->loggerHelper->log(
                                        __('Warning: Root product id: %1 not found', $parentId),
                                        SeLogger::TYPE_WARNING
                                    );
                                }
                            }
                            unset($rootProduct);
                        } else {
                            $products->getItemById($parentId)->setData('seChildrenProducts', $currentChildrenProducts);
                        }
                    }
                }
            }
        }
    }

    /**
     * Return children product ids
     *
     * @param ProductModel $product
     * @param StoreModel   $store
     *
     * @return array
     */
    public function getChildrenProductIds(
        ProductModel $product,
        StoreModel $store = null
    ) {
        $childrenIds = [];

        if (empty($product)) {
            return $childrenIds;
        }

        // if CONFIGURABLE OR GROUPED OR BUNDLE
        if (in_array($product->getData('type_id'), $this->hasChildrenTypes)) {
            if ($typeInstance = $product->getTypeInstance()) {
                $requiredChildrenIds = $typeInstance->getChildrenIds($product->getId(), true);
                if ($requiredChildrenIds) {
                    foreach ($requiredChildrenIds as $groupedChildrenIds) {
                        $childrenIds = array_merge($childrenIds, $groupedChildrenIds);
                    }
                }
            }
        }

        return $childrenIds;
    }

    /**
     * Get product minimal tier price
     *
     * @param ProductModel $product         Product data
     * @param int          $customerGroupId Usergroup
     *
     * @return null|int
     */
    private function getMinimalTierPrice(ProductModel $product, $customerGroupId = null, $min = true)
    {
        $price = null;

        if ($customerGroupId) {
            $product->setCustomerGroupId($customerGroupId);
        }

        // Load tier prices
        $tierPrices = $product->getTierPrices();
        if (empty($tierPrices)) {
            if ($attribute = $product->getResource()->getAttribute('tier_price')) {
                $attribute->getBackend()->afterLoad($product);
                $tierPrices = $product->getTierPrices();
            }
        }

        // Detect discount type: fixed or percent (available for bundle products)
        $priceType = 'fixed';
        if ($product->getTypeId() == ProductType::TYPE_BUNDLE) {
            $priceType = $product->getPriceType();

            if ($priceType !== null && $priceType != BundleProductPrice::PRICE_TYPE_FIXED) {
                $priceType = 'percent';
            }

            $min = $priceType == 'percent' ? !$min : $min;
        }

        // Calculate minimum discount value
        if (!empty($tierPrices) && is_array($tierPrices)) {
            $prices = [];

            foreach ($tierPrices as $priceInfo) {
                if ($priceInfo->getCustomerGroupId() == $customerGroupId) {
                    if ($priceType == 'percent') {
                        if (!empty($priceInfo['extension_attributes'])) {
                            $priceValue = $priceInfo->getExtensionAttributes()->getPercentageValue();
                        } else {
                            $priceValue = $priceInfo->getValue();
                        }
                    } else {
                        $priceValue = $priceInfo->getValue();
                    }

                    $prices[] = $priceValue;
                }
            }

            if (!empty($prices)) {
                $price = $min ? min($prices) : max($prices);
            }
        }

        // Calculate discounted price
        if ($price && $priceType == 'percent') {
            $regularPrice = $this->getProductMinimalRegularPrice($product, null, false);
            $price = $regularPrice * (1 - $price / 100.0);
        }

        return $price;
    }

    /**
     * Calculate minimal list price
     *
     * @param ProductModel $product          Product model
     * @param array        $childrenProducts List of the children products
     * @param bool         $applyTax         If true tax will be applied
     *
     * @return float
     */
    private function getProductMinimalRegularPrice(
        ProductModel $product,
        $childrenProducts = null,
        $applyTax = true
    ) {
        $regularPrice = $product
            ->getPriceInfo()
            ->getPrice(RegularPrice::PRICE_CODE)
            ->getAmount()
            ->getBaseAmount();

        $msrpPrice = $product->getData('msrp');
        if (!empty($msrpPrice)) {
            $regularPrice = $msrpPrice;
        }

        if (!$regularPrice && !empty($childrenProducts)) {
            foreach ($childrenProducts as $childrenProduct) {
                if ($childrenProduct->getStatus() != ProductStatus::STATUS_ENABLED) {
                    continue;
                }

                $childRegularPrice = $childrenProduct
                    ->getPriceInfo()
                    ->getPrice(RegularPrice::PRICE_CODE)
                    ->getAmount()
                    ->getBaseAmount();

                $regularPrice = $regularPrice ? min($regularPrice, $childRegularPrice) : $childRegularPrice;
            }
        }

        if ($regularPrice && $applyTax) {
            $regularPrice = $this->getProductShowPrice($product, $regularPrice);
        }

        return (float)$regularPrice;
    }

    /**
     * Generate prices for product
     *
     * @param array        $item             Product data
     * @param ProductModel $product          Product model
     * @param array        $childrenProducts List of the children products
     * @param StoreModel   $store            Store object
     *
     * @return boolean
     */
    private function generateProductPrices(
        array &$item,
        ProductModel $product,
        $childrenProducts = null,
        StoreModel $store = null
    ) {
        $startTime = microtime(true);

        if ($customerGroups = $this->getCustomerGroups()) {
            foreach ($customerGroups as $customerGroupId => $customerGroup) {
                // It is needed because the 'setCustomerGroupId' function works only once.
                $productCurrentGroup = clone $product;

                if ($customerGroupId == CustomerGroupModel::NOT_LOGGED_IN_ID
                    || !isset($equalPriceForAllGroups)
                ) {
                    $price = $this->getProductMinimalPrice(
                        $productCurrentGroup,
                        $store,
                        $childrenProducts,
                        $customerGroupId
                    );

                    if ($price !== false) {
                        $price = round($price, ApiSeHelper::getFloatPrecision());
                    }

                    if ($customerGroupId == CustomerGroupModel::NOT_LOGGED_IN_ID) {
                        $item['price'] = $price;
                        $item['list_price'] = round(
                            $this->getProductMinimalRegularPrice($product, $childrenProducts),
                            ApiSeHelper::getFloatPrecision()
                        );
                    }
                } else {
                    $price = $equalPriceForAllGroups ?: 0;
                }

                $label_ = ApiSeHelper::getLabelForPricesUsergroup() . $customerGroupId;
                $item[$label_] = $price;
                unset($productCurrentGroup);
            }
        }

        $endTime = microtime(true);

        $this->loggerHelper->log(
            sprintf("===== ApiProducts::generateProductPrices() takes %0.2f ms =====", $endTime - $startTime),
            SeLogger::TYPE_DEBUG
        );

        return true;
    }

    /**
     * Get storefront url for product
     *
     * @param ProductModel $product    Product data
     * @param StoreModel   $store      Store object
     * @param int          $categoryId Category identifier
     *
     * @return string
     */
    public function getStorefrontUrl(
        ProductModel $product,
        StoreModel $store = null,
        $categoryId = null
    ) {
        $routeParams = [
            '_nosid'  => true,
            '_secure' => $this->configuration->getIsUseSecureUrlsInFrontend(),
            //'_query' => ['___store' => $store->getCode()],
        ];
        $urlDataObject = $product->getData('url_data_object');
        $storeId = $product->getStoreId();
        $urlFinder = ObjectManager::getInstance()->get(\Magento\UrlRewrite\Model\UrlFinderInterface::class);

        if ($urlDataObject !== null) {
            $requestPath = $urlDataObject->getUrlRewrite();
            $routeParams['_scope'] = $urlDataObject->getStoreId();
        } else {
            $requestPath = $product->getRequestPath();

            if (empty($requestPath)) {
                $filterData = [
                    UrlRewrite::ENTITY_ID   => $product->getId(),
                    UrlRewrite::ENTITY_TYPE => ProductUrlRewriteGenerator::ENTITY_TYPE,
                    UrlRewrite::STORE_ID    => $storeId,
                ];

                if ($categoryId) {
                    $filterData[UrlRewrite::METADATA]['category_id'] = $categoryId;
                }

                $rewrite = $urlFinder->findOneByData($filterData);

                if ($rewrite) {
                    $requestPath = $rewrite->getRequestPath();
                    $product->setRequestPath($requestPath);
                } else {
                    $product->setRequestPath(false);
                }
            }
        }

        if (isset($routeParams['_scope'])) {
            $storeId = $this->storeManager->getStore($routeParams['_scope'])->getId();
        } elseif ($store) {
            $routeParams['_scope'] = $storeId = $store->getId();
        }

        if ($storeId != $this->storeManager->getStore()->getId()) {
            $routeParams['_scope_to_url'] = true;
        }

        if ($requestPath) {
            $routeParams['_direct'] = $requestPath;
        } else {
            $routeParams['id'] = $product->getId();
            $routeParams['s'] = $product->getUrlKey();

            if ($categoryId) {
                $routeParams['category'] = $categoryId;
            }
        }

        if (!isset($routeParams['_query'])) {
            $routeParams['_query'] = [];
        }

        if (isset($routeParams['_direct'])) {
            // Build direct url
            $direct = $routeParams['_direct'];
            unset($routeParams['_direct']);
            $url = self::getUrlInstance($store)->getBaseUrl($routeParams) . $direct;
        } else {
            $url = self::getUrlInstance($store)->getUrl('catalog/product/view', $routeParams);
        }

        return $url;
    }

    /**
     * Generate feed for product
     *
     * @param ProductModel $product   Product object
     * @param StoreModel   $store     Store object
     * @param string       $checkData If true, the additional checks will be perform on the product
     *
     * @return array
     */
    public function generateProductFeed(
        ProductModel $product,
        StoreModel $store = null,
        $checkData = true
    ) {
        $item = [];

        if ($checkData
            && (!$product || !$product->getId() || !$product->getName())
        ) {
            return $item;
        }

        if (!empty($store)) {
            $product->setStoreId($store->getId());
            $this->storeManager->setCurrentStore($store);
        } else {
            $product->setStoreId(0);
        }

        $unitedProducts = [$product]; // current product + childrens products (if exists)
        $childrenProducts = $product->getData('seChildrenProducts');

        if ($childrenProducts) {
            foreach ($childrenProducts as $childrenProductsKey => $childrenProduct) {
                $unitedProducts[] = $childrenProduct;
            }
        }

        $item['id'] = $product->getId();
        $item['title'] = $product->getName();
        $item['link'] = $this->getStorefrontUrl($product, $store);
        $item['product_code'] = $product->getSku();
        $item['created'] = strtotime($product->getCreatedAt());

        $summaryAttr = $this->configuration->getSummaryAttr();
        $item['summary'] = $product->getData($summaryAttr);

        $this->generateProductPrices($item, $product, $childrenProducts, $store);

        $quantity = $this->getProductQty($product, $store, $unitedProducts);
        $item['quantity'] = ceil($quantity);
        $item['is_in_stock'] = $quantity > 0;

        // Show images without white field
        // Example: image 360 x 535 => 47 х 70
        if ($this->configuration->getIsResultsWidgetEnabled($store->getId())) {
            $item['image_link'] = $this->getProductImageLink($product, false, false, $store);
        } else {
            $item['image_link'] = $this->getProductImageLink($product, false, true, $store);
        }

        $this->generateProductAttributes($item, $product, $childrenProducts, $unitedProducts, $store);

        // Add product categories
        $item['category_ids'] = $item['categories'] = [];

        $categoryCollection = $product
            ->getCategoryCollection()
            ->addAttributeToFilter('path', ['like' => "1/{$store->getRootCategoryId()}/%"])
            ->addAttributeToSelect(['entity_id', 'name']);

        $categoryCollection->load();

        foreach ($categoryCollection as $category) {
            $item['category_ids'][] = $category->getId();
            $item['categories'][] = $category->getName();
        }

        // Add review data
        if ($product->getRatingSummary()) {
            $item['total_reviews'] = $product->getRatingSummary()->getReviewsCount();
            $item['reviews_average_score'] = $product->getRatingSummary()->getRatingSummary() / 20.0;
        }

        // Add sales data
        $item['sales_amount'] = (int)$product->getData('se_sales_amount');
        $item['sales_total'] = $item['sales_total'] = round(
            (float)$product->getData('se_sales_total'),
            ApiSeHelper::getFloatPrecision()
        );

        $item['related_product_ids'] = $item['up_sell_product_ids'] = $item['cross_sell_product_ids'] = [];

        // Add related products
        $relatedProducts = $product->getRelatedProducts();
        if (!empty($relatedProducts)) {
            foreach ($relatedProducts as $relatedProduct) {
                $item['related_product_ids'][] = $relatedProduct->getId();
            }
        }

        // Add upsell products
        $upsellProducts = $product->getUpSellProducts();
        if (!empty($upsellProducts)) {
            foreach ($upsellProducts as $upsellProduct) {
                $item['up_sell_product_ids'][]  = $upsellProduct->getId();
            }
        }

        // Add crosssell products
        $crossSellProducts = $product->getCrossSellProducts();
        if (!empty($crossSellProducts)) {
            foreach ($crossSellProducts as $crossSellProduct) {
                $item['cross_sell_product_ids'][] = $crossSellProduct->getId();
            }
        }

        return $item;
    }

    /**
     * Returns stock item
     *
     * @param ProductModel $product Product model
     * @param StoreModel     $store   Object store
     *
     * @return mixed
     */
    public function getStockItem(
        ProductModel $product,
        StoreModel $store = null
    ) {
        $stockItem = null;

        if (!empty($product)) {
            $stockItem = $this->stockRegistry->getStockItem($product->getId());
        }

        return $stockItem;
    }

    /**
     * getProductQty
     *
     * @param ProductModel $product
     * @param StoreModel   $store
     * @param array        $unitedProducts - Current product + childrens products (if exists)
     *
     * @return int
     */
    private function getProductQty(
        ProductModel $product,
        StoreModel $store,
        array $unitedProducts = []
    ) {
        $quantity = 1;
        $stockItem = $this->getStockItem($product);

        if (!empty($stockItem)) {
            $manageStock = null;

            if ($stockItem->getData(StockItemInterface::USE_CONFIG_MANAGE_STOCK)) {
                $manageStock = $this->configuration
                    ->getValue(CatalogInventoryConfiguration::XML_PATH_MANAGE_STOCK);
            } else {
                $manageStock = $stockItem->getData(StockItemInterface::MANAGE_STOCK);
            }

            if (empty($manageStock)) {
                $quantity = 1;
            } else {
                $isInStock = $stockItem->getIsInStock();

                if (!$isInStock) {
                    $quantity = 0;
                } else {
                    $quantity = (int)$stockItem->getQty();

                    if ($quantity <= 0) {
                        $backorders = StockItemInterface::BACKORDERS_NO;

                        if ($stockItem->getData(StockItemInterface::USE_CONFIG_BACKORDERS) == 1) {
                            $backorders = $this->configuration
                                ->getValue(CatalogInventoryConfiguration::XML_PATH_BACKORDERS);
                        } else {
                            $backorders = $stockItem->getData(StockItemInterface::BACKORDERS);
                        }

                        if ($backorders != StockItemInterface::BACKORDERS_NO) {
                            $quantity = 1;
                        }
                    }

                    if (!empty($unitedProducts)) {
                        $quantity = 0;

                        foreach ($unitedProducts as $itemProductKey => $itemProduct) {
                            $quantity += $this->getProductQty($itemProduct, $store);
                        }
                    }
                }
            }
        }

        return $quantity;
    }

    /**
     * Returns header for api request
     *
     * @param StoreModel $store Store object
     * 
     * @return array
     */
    public function getHeader(StoreModel $store = null)
    {
        $url = '';

        if (empty($store)) {
            $this->storeManager->getStore()->getBaseUrl(\Magento\Framework\Url::URL_TYPE_WEB);
        } else {
            $url = self::getUrlInstance($store)->getBaseUrl([
                '_nosid' => true,
                '_scope' => $store->getId(),
            ]);
        }
        $date = date('c');

        return [
            'id'      => $url,
            'updated' => $date,
        ];
    }

    /**
     * Return list of the products
     *
     * @param array      $productIds         List of the product ids
     * @param StoreModel $store              Store object
     * @param int        $customerGroupId    Customer group id
     * @param bool       $generateSalesData  If true, sales data will be generated
     * @param bool       $generateReviewData If true, reviews data will be generated
     *
     * @return ProductCollection
     */
    public function getProducts(
        array $productIds = [],
        StoreModel $store = null,
        $customerGroupId = null,
        $generateSalesData = true,
        $generateReviewData = true
    ) {
        $resultProducts = [];

        if (empty($productIds)) {
            return $resultProducts;
        }

        $startTime = microtime(true);

        static $arrProducts = [];

        $keyProducts = '';

        if (!empty($productIds)) {
            if (is_array($productIds)) {
                $keyProducts .= implode('_', $productIds);
            } else {
                $keyProducts .= $productIds;
            }
        }

        $keyProducts .= ':' .  ($store ? $store->getId() : '0');
        $keyProducts .= ':' .  $customerGroupId;
        $keyProducts .= ':' .  ($this->isGetProductsByItems ? '1' : '0');

        if (!isset($arrProducts[$keyProducts])) {
            $products = [];

            if ($this->isGetProductsByItems) {
                $products = $this->getProductsByItems($productIds, $store);
            } else {
                $products = $this->getProductCollection()
                    ->distinct(true)
                    ->addAttributeToSelect('*')
                    ->addFinalPrice()
                    ->addMinimalPrice()
                    ->addUrlRewrite();

                if (!empty($customerGroupId)) {
                    if (!emtpy($store)) {
                        $products->addPriceData($customerGroupId, $store->getWebsiteId());
                    } else {
                        $products->addPriceData($customerGroupId);
                    }
                }

                if (!empty($store)) {
                    $products
                        ->setStoreId($store->getId())
                        ->addStoreFilter($store);
                }

                if ($productIds !== \Searchanise\SearchAutocomplete\Model\Queue::NOT_DATA) {
                    // Already exist automatic definition 'one value' or 'array'.
                    $products->addIdFilter($productIds);
                }

                $products->load();

                // Fix: Disabled product not comming in product collection in version 2.2.2 or highter, so try to reload them directly
                if ($productIds !== \Searchanise\SearchAutocomplete\Model\Queue::NOT_DATA && $this->isVersionMoreThan('2.2.2')) {
                    $skippedProductIds = array_diff($productIds, $products->getLoadedIds());

                    if (!empty($skippedProductIds)) {
                        $reloadedItems = $this->getProductsByItems($skippedProductIds, $store);

                        if (!empty($reloadedItems)) {
                            foreach ($reloadedItems as $item) {
                                try {
                                    $products->addItem($item);
                                } catch (\Exception $e) {
                                    // Workaround if item already exist in collection. See se-5148
                                }
                            }
                        }
                    }
                }
            }

            // Fixme in the future
            // Maybe create cache without customerGroupId and setCustomerGroupId after using cache.
            if (count($products) > 0 && (!empty($store) || $customerGroupId != null)) {
                foreach ($products as $key => &$product) {
                    if (!empty($product)) {
                        if (!empty($store)) {
                            $product->setWebsiteId($store->getWebsiteId());
                        }

                        if (!empty($customerGroupId)) {
                            $product->setCustomerGroupId($customerGroupId);
                        }
                    }
                }
            }
            // end fixme

            if (
                $generateReviewData
                && $products instanceof DataCollection
                && $this->getModuleManager()->isEnabled('Magento_Review')
            ) {
                $this->reviewFactory->create()->appendSummary($products);
            }

            if ($generateSalesData) {
                $this->generateSalesData($products, $store);
            }

            $arrProducts[$keyProducts] = $products;
        } // End isset

        $endTime = microtime(true);

        $this->loggerHelper->log(
            sprintf("===== ApiProducts::getProducts() for %d products takes %0.2f ms =====", count($productIds), $endTime - $startTime),
            SeLogger::TYPE_DEBUG
        );

        return $arrProducts[$keyProducts];
    }

    /**
     * Attach sales data to products
     *
     * @param ProductCollection|array $products
     * @param StoreModel              $storeStore object
     *
     * @return bool
     */
    private function generateSalesData(&$products, StoreModel $store = null)
    {
        if ($products instanceof ProductCollection) {
            $product_ids = $products->getAllIds();
        } elseif (is_array($products)) {
            $product_ids = array_map(function ($product) {
                return $product->getId();
            }, $products);
        }

        $product_ids = array_filter($product_ids);

        if (empty($product_ids)) {
            return false;
        }

        $startTime = microtime(true);

        $inProductIds = implode(',', $product_ids);
        $ordersTableName = $this->resourceConnection->getTableName('sales_order_item');

        try {
            $salesConnection = $this->resourceConnection->getConnectionByName('sales');
        } catch (\Exception $e) {
            $salesConnection = $this->resourceConnection->getConnection();
        }

        $condition = "product_id IN ({$inProductIds})";

        if (!empty($store)) {
            $condition .= ' AND store_id = ' . $store->getId();
        }

        $query = "SELECT
                product_id,
                SUM(qty_ordered) AS sales_amount,
                SUM(row_total) AS sales_total
            FROM {$ordersTableName}
            WHERE {$condition}
            GROUP BY product_id";

        $salesData = $salesConnection->query($query)->fetchAll(
            \PDO::FETCH_GROUP
            | \PDO::FETCH_UNIQUE
            | \PDO::FETCH_ASSOC
        );

        foreach ($products as &$product) {
            $productId = $product->getId();

            if (isset($salesData[$productId])) {
                $product->setData('se_sales_amount', $salesData[$productId]['sales_amount']);
                $product->setData('se_sales_total', $salesData[$productId]['sales_total']);
            }
        }

        $endTime = microtime(true);
        $this->loggerHelper->log(
            sprintf("===== ApiProducts::generateSalesData() for %d products takes %0.2f ms =====", count($product_ids), $endTime - $startTime),
            SeLogger::TYPE_DEBUG
        );

        return true;
    }

    /**
     * Return product ids for specific range. Used by full import
     *
     * @param int        $start        Start range
     * @param int        $end          End range
     * @param int        $step         Step
     * @param StoreModel $store        Store object
     * @param bool       $isOnlyActive If true, finds only active produts
     *
     * @return array
     */
    public function getProductIdsFromRange(
        $start,
        $end,
        $step,
        StoreModel $store = null,
        $isOnlyActive = false
    ) {
        $arrProducts = [];

        $startTime = microtime(true);

        $products = $this->getProductCollection()
            ->clear()
            ->distinct(true)
            ->addAttributeToSelect('entity_id')
            ->addFieldToFilter('entity_id', ['from' => $start, 'to' => $end])
            ->setPageSize($step);

        if (!empty($store)) {
            $products->addStoreFilter($store);
        }

        if ($isOnlyActive) {
            $products
                ->addAttributeToFilter('status', ['in'=> $this->productStatus->getVisibleStatusIds()])
                ->addAttributeToFilter(
                    'visibility',
                    ['in' => $this->productVisibility->getVisibleInSearchIds()]
                );
        }

        $arrProducts = $products->getAllIds();
        // It is necessary for save memory.
        unset($products);

        $endTime = microtime(true);

        $this->loggerHelper->log(
            sprintf("===== ApiProducts::getProductIdsFromRange() for %d products takes %0.2f ms =====", count($arrProducts), $endTime - $startTime),
            SeLogger::TYPE_DEBUG
        );

        return $arrProducts;
    }

    /**
     * Get minimum and maximum product ids from store
     *
     * @param StoreModel $store
     *
     * @return number[]
     */
    public function getMinMaxProductId(StoreModel $store = null)
    {
        $startId = $endId = 0;

        $productCollection = $this->getProductCollection()
            ->clear()
            ->addAttributeToSelect('entity_id')
            ->setPageSize(1);

        if (!empty($store)) {
            $productCollection
            ->setStoreId($store->getId())
            ->addStoreFilter($store);
        }

        $productCollection
            ->getSelect()
            ->reset(\Zend_Db_Select::COLUMNS)
            ->columns(['MIN(`e`.`entity_id`) as min_entity_id', 'MAX(`e`.`entity_id`) as max_entity_id']);

        $minMaxArray = $productCollection->load()->toArray(['min_entity_id', 'max_entity_id']);

        if (!empty($minMaxArray)) {
            $firstItem = reset($minMaxArray);
            $startId = (int) $firstItem['min_entity_id'];
            $endId = (int) $firstItem['max_entity_id'];
        }

        return [$startId, $endId];
    }

    /**
     * Get products with items
     *
     * @param array      $productIds List of product ids
     * @param StoreModel $store      Store object
     *
     * @return array
     */
    private function getProductsByItems(array $productIds, StoreModel $store = null)
    {
        $products = [];

        $productIds = $this->validateProductIds($productIds, $store);

        if (!empty($productIds)) {
            foreach ($productIds as $key => $productId) {
                if (empty($productId)) {
                    continue;
                }

                // It can use various types of data.
                if (is_array($productId)) {
                    if (isset($productId['entity_id'])) {
                        $productId = $productId['entity_id'];
                    }
                }

                try {
                    $product = $this->loadProductById($productId);
                } catch (\Exception $e) {
                    $this->loggerHelper->log(__("Error: Script couldn't get product'"));
                    continue;
                }

                if (!empty($product)) {
                    $products[] = $product;
                }
            }
        }

        return $products;
    }

    /**
     * Validate list of the products
     *
     * @param array      $productIds List of the products
     * @param StoreModel $store      Store object
     * 
     * @return array
     */
    private function validateProductIds(array $productIds, StoreModel $store = null)
    {
        $validProductIds = [];

        if (!empty($store)) {
            $this->storeManager->setCurrentStore($store);
        } else {
            $this->storeManager->setCurrentStore(0);
        }

        $products = $this->getProductCollection()
            ->addAttributeToSelect('entity_id');

        if (!empty($store)) {
            $products->addStoreFilter($store);
        }

        // Already exist automatic definition 'one value' or 'array'.
        $products->addIdFilter($productIds);
        $products->load();

        if (count($products) > 0) {
            // Not used because 'arrProducts' comprising 'stock_item' field and is 'array(array())'
            // $arrProducts = $products->toArray(array('entity_id'));
            foreach ($products as $product) {
                $validProductIds[] = $product->getId();
            }
        }

        if (count($validProductIds) != count($productIds) && $this->isVersionMoreThan('2.2.2')) {
            // Fix : Disabled product not coming in product collection in version 2.2.2 or highter
            // So we have to modify SQL query directly and try to reload them
            $updatedfromAndJoin = $updatedWhere = [];

            $fromAndJoin = $products->getSelect()->getPart('FROM');
            $where = $products->getSelect()->getPart('WHERE');
            $products->clear();

            foreach ($fromAndJoin as $key => $index) {
                if ($key == 'stock_status_index' || $key == 'price_index') {
                    $index['joinType'] = 'LEFT JOIN';
                }
                $updatedfromAndJoin[$key] = $index;
            }

            foreach ($where as $key => $condition) {
                if (strpos($condition, 'stock_status_index.stock_status = 1') !== false) {
                    $updatedWhere[] = str_replace('stock_status_index.stock_status = 1', '1', $condition);
                } else {
                    $updatedWhere[] = $condition;
                }
            }

            if (!empty($updatedfromAndJoin)) {
                $products->getSelect()->setPart('FROM', $updatedfromAndJoin);
            }

            if (!empty($updatedWhere)) {
                $products->getSelect()->setPart('WHERE', $updatedWhere);
            }

            $products->load();

            if (count($products) > 0) {
                // Not used because 'arrProducts' comprising 'stock_item' field and is 'array(array())'
                // $arrProducts = $products->toArray(array('entity_id'));
                foreach ($products as $product) {
                    $validProductIds[] = $product->getId();
                }
            }
        }

        // It is necessary for save memory.
        unset($products);

        return array_unique($validProductIds);
    }

    /**
     * Get customer group prices for getSchema()
     *
     * @return array
     */
    public function getSchemaCustomerGroupsPrices()
    {
        $items = [];

        if ($customerGroups = $this->getCustomerGroups()) {
            foreach ($customerGroups as $keyCustomerGroup => $customerGroup) {
                $label = ApiSeHelper::getLabelForPricesUsergroup() . $customerGroup['customer_group_id'];
                $items[] = [
                    'name'  => $label,
                    'title' => 'Price for ' .  $customerGroup['customer_group_code'],
                    'type'  => 'float',
                ];
            }
        }

        return $items;
    }

    /**
     * Returns customer groups
     *
     * @return array
     */
    private function getCustomerGroups()
    {
        static $customerGroups;

        if (!isset($customerGroups)) {
            $_customerGroups = $this->customerGroupCollectionFactory->create();

            if (!$this->configuration->getIsCustomerUsergroupsEnabled()) {
                $_customerGroups->addFieldToFilter('customer_group_id', CustomerGroupModel::NOT_LOGGED_IN_ID);
            }

            $_customerGroups->load();

            foreach ($_customerGroups as $group) {
                $customerGroups[$group->getId()] = $group->toArray();
            }

            $customerGroups[CustomerGroupManagement::CUST_GROUP_ALL] = [
                'customer_group_id' => CustomerGroupManagement::CUST_GROUP_ALL,
                'customer_group_code' => __('ALL GROUPS')
            ];
        }

        return $customerGroups;
    }

    /**
     * Generate custom facet for getSchema()
     *
     * @param string $title
     * @param int    $position
     * @param string $attribute
     * @param string $type
     *
     * @return array
     */
    private function generateFacetFromCustom($title = '', $position = 0, $attribute = '', $type = '')
    {
        $facet = [];

        $facet['title'] = $title;
        $facet['position'] = $position;
        $facet['attribute'] = $attribute;
        $facet['type'] = $type;

        return $facet;
    }

    /**
     * Return product attributes
     *
     * @return ProductAttributeCollection
     */
    public function getProductAttributes()
    {
        static $allAttributes = null;

        if (empty($allAttributes)) {
            $allAttributes = $this->catalogProductAttributeCollectionFactory
                ->create()
                ->setItemObjectClass(ProductAttribute::class)
                ->load();
        }

        return $allAttributes;
    }

    /**
     * Get product schema for searchanise
     *
     * @param StoreModel $store Store object
     *
     * @return array
     */
    public function getSchema(StoreModel $store)
    {
        static $schemas;

        if (!isset($schemas[$store->getId()])) {
            $this->storeManager->setCurrentStore($store);

            $schema = $this->getSchemaCustomerGroupsPrices();

            if ($this->configuration->getIsResultsWidgetEnabled($store->getId())) {
                $schema[] = [
                    'name'        => 'categories',
                    'title'       => __('Category')->getText(),
                    'type'        => 'text',
                    'weight'      => self::WEIGHT_CATEGORIES,
                    'text_search' => 'Y',
                    'facet'       => $this->generateFacetFromCustom(
                        __('Category')->getText(),
                        10,
                        'categories',
                        'select'
                    ),
                ];

                $schema[] = [
                    'name'        => 'category_ids',
                    'title'       => __('Category')->getText() . ' - IDs',
                    'type'        => 'text',
                    'weight'      => 0,
                    'text_search' => 'N',
                ];
            } else {
                $schema[] = [
                    'name'        => 'categories',
                    'title'       => __('Category')->getText(),
                    'type'        => 'text',
                    'weight'      => self::WEIGHT_CATEGORIES,
                    'text_search' => 'Y',
                ];

                $schema[] = [
                    'name'        => 'category_ids',
                    'title'       => __('Category')->getText() . ' - IDs',
                    'type'        => 'text',
                    'weight'      => 0,
                    'text_search' => 'N',
                    'facet'       => $this->generateFacetFromCustom(
                        __('Category')->getText(),
                        10,
                        'category_ids',
                        'select'
                    ),
                ];
            }

            $schema = array_merge($schema, [
                [
                    'name'        => 'is_in_stock',
                    'title'       => __('Stock Availability')->getText(),
                    'type'        => 'text',
                    'weight'      => 0,
                    'text_search' => 'N',
                ],
                [
                    'name'        => 'sales_amount',
                    'title'       => __('Bestselling')->getText(),
                    'type'        => 'int',
                    'sorting'     => 'Y',
                    'weight'      => 0,
                    'text_search' => 'N',
                ],
                [
                    'name'        => 'sales_total',
                    'title'       => __('Sales total')->getText(),
                    'type'        => 'float',
                    'filter_type' => 'none',
                ],
                [
                    'name'        => 'created',
                    'title'       => __('created')->getText(),
                    'type'        => 'int',
                    'sorting'     => 'Y',
                    'weight'      => 0,
                    'text_search' => 'N',
                ],
                [
                    'name'        => 'related_product_ids',
                    'title'       => __('Related Products')->getText() . ' - IDs',
                    'filter_type' => 'none',
                ],
                [
                    'name'        => 'up_sell_product_ids',
                    'title'       => __('Up-Sell Products')->getText() . ' - IDs',
                    'filter_type' => 'none',
                ],
                [
                    'name'        => 'cross_sell_product_ids',
                    'title'       => __('Cross-Sell Products')->getText() . ' - IDs',
                    'filter_type' => 'none',
                ],
            ]);

            if ($attributes = $this->getProductAttributes()) {
                foreach ($attributes as $attribute) {
                    if ($items = $this->getSchemaAttribute($attribute)) {
                        foreach ($items as $keyItem => $item) {
                            $schema[] = $item;
                        }
                    }
                }
            }

            $schemas[$store->getId()] = $schema;
        }

        return $schemas[$store->getId()];
    }

    /**
     * Get schema attribute
     *
     * @param ProductAttribute $attribute Product attribute
     * 
     * @return array
     */
    public function getSchemaAttribute(ProductAttribute $attribute)
    {
        $items = [];

        $requiredAttributes = $this->getRequiredAttributes();

        $attributeCode = $attribute->getAttributeCode();
        $inputType = $attribute->getData('frontend_input');
        $isSearchable = $attribute->getIsSearchable();
        $isVisibleInAdvancedSearch = $attribute->getIsVisibleInAdvancedSearch();
        $usedForSortBy = $attribute->getUsedForSortBy();
        $isFilterable = $attribute->getIsFilterable();
        $attributeName = 'attribute_' . $attribute->getId();

        $isNecessaryAttribute = $isSearchable
            || $isVisibleInAdvancedSearch
            || $usedForSortBy
            || $isFilterable
            || in_array($attributeCode, $requiredAttributes);

        if (!$isNecessaryAttribute) {
            return $items;
        }

        $type = '';
        $name = $attribute->getAttributeCode();
        $title = $attribute->getStoreLabel();
        $sorting = $usedForSortBy ? 'Y' : 'N';
        $textSearch = $isSearchable ? 'Y' : 'N';
        $attributeWeight = 0;

        // <system_attributes>
        if ($attributeCode == 'price') {
            $type = 'float';
            $textSearch = 'N';
        } elseif ($attributeCode == 'status' || $attributeCode == 'visibility') {
            $type = 'text';
            $textSearch = 'N';
        } elseif ($attributeCode == 'has_options') {
        } elseif ($attributeCode == 'required_options') {
        } elseif ($attributeCode == 'custom_layout_update') {
        } elseif ($attributeCode == 'tier_price') { // quantity discount
        } elseif ($attributeCode == 'image_label') {
        } elseif ($attributeCode == 'small_image_label') {
        } elseif ($attributeCode == 'thumbnail_label') {
        } elseif ($attributeCode == 'tax_class_id') {
        } elseif ($attributeCode == 'url_key') { // seo name
        } elseif ($attributeCode == 'group_price') {
        } elseif ($attributeCode == 'category_ids') {
        } elseif ($attributeCode == 'categories') {
            // <system_attributes>
        } elseif ($attributeCode == 'name' || $attributeCode == 'sku' || $attributeCode == 'short_description') {
            //for original
            if ($attributeCode == 'short_description') {
                $name    = 'description';
                $sorting = 'N';
                $weight  = self::WEIGHT_SHORT_DESCRIPTION;
            } elseif ($attributeCode == 'name') {
                $name    = 'title';
                $sorting = 'Y';//always (for search results widget)
                $weight  = self::WEIGHT_SHORT_TITLE;
            } elseif ($attributeCode == 'sku') {
                $name    = 'product_code';
                $sorting = $sorting;
                $weight  = self::WEIGHT_SHORT_TITLE;
            }

            $items[] = [
                'name'    => $name,
                'title'   => $title,
                'type'    => 'text',
                'sorting' => $sorting,
                'weight'  => $weight,
                'text_search' => $textSearch,
            ];

            // for grouped
            $type = 'text';
            $name  = 'se_grouped_' . $attributeCode;
            $sorting = 'N';
            $title = $attribute->getStoreLabel() . ' - Grouped';
            $attributeWeight = ($attributeCode == 'short_description')
                ? self::WEIGHT_SHORT_DESCRIPTION
                : self::WEIGHT_SHORT_TITLE;
        } elseif ($attributeCode == 'short_description'
            || $attributeCode == 'description'
            || $attributeCode == 'meta_title'
            || $attributeCode == 'meta_description'
            || $attributeCode == 'meta_keyword'
        ) {
            if ($isSearchable) {
                if ($attributeCode == 'description') {
                    $attributeWeight = self::WEIGHT_DESCRIPTION;
                } elseif ($attributeCode == 'meta_title') {
                    $attributeWeight = self::WEIGHT_META_TITLE;
                } elseif ($attributeCode == 'meta_description') {
                    $attributeWeight = self::WEIGHT_META_DESCRIPTION;
                } elseif ($attributeCode == 'meta_keyword') {
                    $attributeWeight = self::WEIGHT_META_KEYWORDS;
                }
            }

            $type = 'text';

            if ($attributeCode == 'description') {
                $name = 'full_description';
                $items[] = [
                    'name'   => 'se_grouped_full_' . $attributeCode,
                    'title'  => $attribute->getStoreLabel() . ' - Grouped',
                    'type'   => $type,
                    'weight' => $isSearchable ? self:: WEIGHT_DESCRIPTION_GROUPED : 0,
                    'text_search' => $textSearch,
                ];
            }
        } elseif ($inputType == 'price') {
            $type = 'float';
        } elseif ($inputType == 'select' || $inputType == 'multiselect') {
            $type = 'text';
            $attributeWeight = $isSearchable ? self::WEIGHT_SELECT_ATTRIBUTES : 0;
        } elseif ($inputType == 'text' || $inputType == 'textarea') {
            if ($isSearchable) {
                if ($inputType == 'text') {
                    $attributeWeight = self::WEIGHT_TEXT_ATTRIBUTES;
                } elseif ($inputType == 'textarea') {
                    $attributeWeight = self::WEIGHT_TEXT_AREA_ATTRIBUTES;
                }
            }
            $type = 'text';
        } elseif ($inputType == 'date') {
            $type = 'int';
        } elseif ($inputType == 'media_image') {
            $type = 'text';
        }

        if (!empty($type)) {
            $item = [
                'name'   => $name,
                'title'  => $title,
                'type'   => $type,
                'sorting' => $sorting,
                'weight' => $attributeWeight,
                'text_search' => $textSearch,
            ];

            $facet = $this->generateFacetFromFilter($attribute);

            if (!empty($facet)) {
                $item['facet'] = $facet;
            }

            $items[] = $item;
        }

        return $items;
    }

    /**
     * Checks if attribute is the facet
     *
     * @param ProductAttribute $attribute
     * 
     * @return boolean
     */
    public function isFacet(ProductAttribute $attribute)
    {
        return $attribute->getIsFilterable() || $attribute->getIsFilterableInSearch() || $attribute->getIsVisibleInAdvancedSearch();
    }

    /**
     * Returns price navigation step
     *
     * @param StoreModel $store
     * 
     * @return mixed
     */
    private function getPriceNavigationStep(StoreModel $store = null)
    {
        // TODO: Unused?
        $store = !empty($store) ? $store : $this->storeManager->getStore(0);

        $priceRangeCalculation = $this->configuration->getValue(DataProviderPrice::XML_PATH_RANGE_CALCULATION);

        if ($priceRangeCalculation == DataProviderPrice::RANGE_CALCULATION_MANUAL) {
            return $this->configuration->getValue(DataProviderPrice::XML_PATH_RANGE_STEP);
        }

        return null;
    }

    /**
     * Generates facet from filter
     *
     * @param ProductAttribute $attribute
     * @param StoreModel       $store
     *
     * @return array
     */
    private function generateFacetFromFilter(
        ProductAttribute $attribute,
        StoreModel $store = null
    ) {
        $item = [];

        if ($this->isFacet($attribute)) {
            $attributeType = '';

            $inputType = $attribute->getData('frontend_input');

            // "Can be used only with catalog input type Dropdown, Multiple Select and Price".
            if (($inputType == 'select') || ($inputType == 'multiselect')) {
                $item['type'] = 'select';
            } elseif ($inputType == 'price') {
                $item['type'] = 'dynamic';
                $step = $this->getPriceNavigationStep($store);

                if (!empty($step)) {
                    $item['min_range'] = $step;
                }
            }

            if (isset($item['type'])) {
                $item['title'] = $attribute->getStoreLabel();
                $item['position']  = ($inputType == 'price')
                    ? $attribute->getPosition()
                    : $attribute->getPosition() + 20;
                $item['attribute'] = $attribute->getAttributeCode();
            }

            if (
                !empty($item)
                && !$attribute->getIsFilterable()
                && !$attribute->getIsFilterableInSearch()
                && $attribute->getIsVisibleInAdvancedSearch()
            ) {
                $item['status'] = 'H';
            }
        }

        return $item;
    }
}
