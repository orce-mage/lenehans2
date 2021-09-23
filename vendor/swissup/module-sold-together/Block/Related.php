<?php

namespace Swissup\SoldTogether\Block;

use Magento\Catalog\Block\Product\AbstractProduct;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Catalog\Api\Data\ProductInterface;

class Related extends AbstractProduct implements IdentityInterface
{
    /**
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        Context $context,
        array $data = []
    ) {
        $this->checkoutSession = $context->getCheckoutSession();
        $this->collectionFactory = $context->getCollectionFactory();
        $this->catalogProductVisibility = $context->getCatalogVisibility();
        $this->stockHelper = $context->getStockHelper();
        $this->resource = [
            'order' => $context->getOrderResource(),
            'customer' => $context->getCustomerResource()
        ];
        $this->categoryRepository = $context->getCategoryRepository();
        parent::__construct($context->getProductContext(), $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareLayout()
    {
        // Check if layout still has this block. Otherwise CRITICAL message in log.
        // Maybe it is unset from layout programatically.
        // For example, easytabs in Argento Force
        $layout = $this->getLayout();
        if ($layout->hasElement($this->getNameInLayout())) {
            // Add details renderer list
            $rendererList = $this->addChild(
                'details.renderers',
                \Magento\Framework\View\Element\RendererList::class
            );
            // Add default renderer
            $renderer = $rendererList->addChild(
                'default',
                \Magento\Framework\View\Element\Text::class
            );
            // Add configurable product details renderer
            $renderer = $rendererList->addChild(
                'configurable',
                Product\Renderer\Configurable::class
            );
        }

        return parent::_prepareLayout();
    }

    /**
     * {@inheritdoc}
     */
    public function getJsLayout()
    {
        $this->fixNumberType($this->jsLayout);

        return parent::getJsLayout();
    }

    /**
     * Cast types for numbers
     */
    private function fixNumberType(&$data)
    {
        foreach ($data as &$v) {
            if (is_array($v)) {
                $this->fixNumberType($v);
            } elseif (is_numeric($v)) {
                settype($v, 'int');
            }
        }
    }

    /**
     * Before rendering html, but after trying to load cache
     *
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $this->_prepareSoldTogetherData();
        return parent::_beforeToHtml();
    }

    /**
     * Prepare product collection using soldtogether data
     *
     * @return $this
     */
    protected function _prepareSoldTogetherData()
    {
        $productIds = $this->getProductIds();

        if (!$productIds) {
            return $this;
        }

        $this->_collection = $this->prepareCollection($this->collectionFactory->create());
        $relationTable = $this->getRelationTable();
        if ($relationTable) {
            $this->_collection->getSelect()
                ->joinInner(
                    ['soldtogether' => $relationTable],
                    'soldtogether.related_id = e.entity_id',
                    []
                )
                ->where('soldtogether.product_id IN (?)', $productIds)
                ->order('soldtogether.weight DESC');
        }

        if ($this->_collection->count() === 0 && $this->canUseRandom()) {
            if ($collection = $this->getRandomCollection()) {
                $this->_collection = $collection;
            }
        }

        $this->_eventManager->dispatch(
            'catalog_block_product_list_collection', [
                'collection' => $this->_collection
            ]
        );

        return $this;
    }

    /**
     * Get product ids that will be used to retrieve related products collection
     *
     * @return array
     */
    public function getProductIds()
    {
        $ids = [];

        if ($this->getProduct()) {
            $ids[] = $this->getProduct()->getId();
        } else {
            $lastRealOrder = $this->checkoutSession->getLastRealOrder();
            $items = ($lastRealOrder && $lastRealOrder->getId()) ?
                $lastRealOrder->getAllVisibleItems() :
                $this->checkoutSession->getQuote()->getAllItems();
            $ids = array_map(function ($item) {
                return $item->getProductId();
            }, $items);
        }

        return $ids;
    }

    /**
     * Apply common filters to the product collection
     *
     * @param \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection
     */
    protected function prepareCollection(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $collection
    ) {
        $collection->distinct(true)
            ->addAttributeToSelect('required_options')
            ->addStoreFilter()
            ->setVisibility(
                $this->catalogProductVisibility->getVisibleInCatalogIds()
            )
            ->addAttributeToFilter('entity_id', [
                'nin' => $this->getProductIds()
            ]);

        if ($this->stockHelper->isModuleOutputEnabled('Magento_Checkout')) {
            $this->_addProductAttributesAndPrices($collection);
        }

        $allowedProductTypes = $this->getAllowedProductTypes();
        $collection->getSelect()
            ->where('e.type_id IN (?)', $allowedProductTypes);

        if (!$this->showOutOfStock()) {
            $this->stockHelper->addInStockFilterToCollection($collection);
        }

        $collection->getSelect()->limit($this->getProductsCount());

        return $collection;
    }

    /**
     * Prepare random collection of products from same category
     *
     * @return \Magento\Catalog\Model\ResourceModel\Product\Collection|false
     */
    protected function getRandomCollection()
    {
        $product = $this->getProduct();

        if (!$product) {
            return false;
        }

        if ($product->hasCategory()) {
            $category = $product->getCategory();
        } elseif ($product->hasCategoryIds()) {
            $categoryIds = $product->getCategoryIds();
            try {
                $category = $this->categoryRepository->get(reset($categoryIds));
            } catch (NoSuchEntityException $e) {
                return false;
            }
        } else {
            return false;
        }

        $collection = $this->prepareCollection($category->getProductCollection());
        $collection->getSelect()->order('rand()');

        return $collection;
    }

    /**
     * Get collection items
     *
     * @return \Magento\Framework\Data\Collection\AbstractDb
     */
    public function getItems()
    {
        return $this->_collection;
    }

    /**
     * Get IDs of products
     *
     * @return array
     */
    public function getIdentities()
    {
        $identities = [];
        if ($this->getItems()) {
            foreach ($this->getItems() as $item) {
                $identities = array_merge($identities, $item->getIdentities());
            }
        }

        return $identities;
    }

    /**
     * Get list of allowed product types to display
     *
     * @return array
     */
    public function getAllowedProductTypes()
    {
        return $this->hasAllowedProductTypes() ? $this->getAllowedProductTypes() : [];
    }

    /**
     * Show out of stock products also.
     *
     * @return boolean
     */
    public function showOutOfStock()
    {
        return !!$this->_getData('show_out_of_stock');
    }

    /**
     * Check is random collection is allowed when product has no sold together relations.
     *
     * @return boolean
     */
    public function canUseRandom()
    {
        return !!$this->_getData('can_use_random');
    }


    /**
     * Get DB table name for relation.
     *
     * @return boolean
     */
    public function getRelationTable()
    {
        $relation = $this->getRelation();

        return isset($this->resource[$relation])
            ? $this->resource[$relation]->getMainTable()
            : '';
    }

    /**
     * Return HTML block with price for current product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getCurrentProductPriceHtml()
    {
        if ($price = $this->getLayout()->getBlock('product.price.render.bundle.customization')) {
            // there is price renderer for bundle product price - use it
            return $price->toHtml();
        }

        return $this->getProductPrice($this->getProduct());
    }

    /**
     * Get modified product iamge HTML for SoldTogether item.
     *
     * @param  ProductInterface $product
     * @param  string           $imageId
     * @param  array            $attributes
     * @return string
     */
    public function getImageHtml($product, $imageId, $attributes = [])
    {
        $html = $this->getImage($product, $imageId, $attributes)->toHtml();

        return str_replace(
            "product-image-container-{$product->getId()}",
            "soldtogether-item-image-container-{$product->getId()}",
            $html
        );
    }

    /**
     * Render product details.
     *
     * @param  ProductInterface $product
     * @return string
     */
    public function renderDetailsHtml(ProductInterface $product)
    {
        return $this->getProductDetailsHtml($product);
    }
}
