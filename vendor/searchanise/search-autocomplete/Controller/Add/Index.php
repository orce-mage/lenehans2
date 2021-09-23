<?php

namespace Searchanise\SearchAutocomplete\Controller\Add;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Catalog\Model\ProductFactory;
use Magento\Checkout\Model\Cart as CartModel;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Product\Type as ProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable as ConfigurableProductType;
use Magento\GroupedProduct\Model\Product\Type\Grouped as GroupedProductType;
use Magento\Downloadable\Model\Product\Type as DownloadableProductType;
use Magento\Catalog\Model\Product as CatalogProduct;
use Magento\Catalog\Model\Product\Option as ProductOption;

class Index extends Action
{
    const STATUS_SUCCESS    = 'OK';
    const STATUS_NO_ACTION  = 'NO_ACTION';
    const STATUS_FAILED     = 'FAILED';

    const DEFAULT_GROUP_QUANTITY    = 1;
    const DEFAULT_BUNDLE_QUANTITY   = 1;

    const MAX_CONFIGURABLE_INTERATION = 30;

    /**
     * @var ProductFactory
     */
    private $productFactory;

    /**
     * @var CartModel
     */
    private $cart;

    /**
     * @var StockRegistryInterface
     */
    private $stockRegistry;

    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    public function __construct(
        Context $context,
        ProductFactory $productFactory,
        CartModel $cart,
        StockRegistryInterface $stockRegistry,
        JsonFactory $resultJsonFactory
    ) {
        $this->productFactory = $productFactory;
        $this->cart = $cart;
        $this->stockRegistry = $stockRegistry;
        $this->resultJsonFactory = $resultJsonFactory;

        parent::__construct($context);
    }

    /**
     * Async
     *
     * {@inheritDoc}
     *
     * @see \Magento\Framework\App\ActionInterface::execute()
     */
    public function execute()
    {
        $request = $this->getRequest();

        if ($request->getParam('test') == 'Y') {
            $result = $this->testAddToCart();
            return $this->resultJsonFactory->create()->setData($result);
        }

        $productId = $request->getParam('id');
        $quantity = (int)$request->getParam('quantity');

        try {
            $response['status'] = $this->addToCart($productId, $quantity);
        } catch (\Exception $e) {
            $response['status'] = self::STATUS_FAILED;
            $response['message'] = $e->getMessage();
        }

        if ($response['status'] == self::STATUS_SUCCESS) {
            $response['redirect'] = $this->_url->getUrl('checkout/cart');
        } else {
            // Unable to add product to the cart. Just redirect customer to the product page
            $product = $this->productFactory->create()->load($productId);

            if ($product) {
                $response['redirect'] = $product->getProductUrl();
            }
        }

        return $this->resultJsonFactory->create()->setData($response);
    }

    /**
     * Add to cart test functionality
     */
    private function testAddToCart()
    {
        ini_set('memory_limit', '1024M');
        set_time_limit(3600);

        $errors = [];
        $this->cart->truncate()->save();

        $request = $this->getRequest();
        $page = $request->getParam('page', 1);
        $size = $request->getParam('size', 0);

        $products = $this->productFactory->create()->getCollection();

        if (!empty($size)) {
            $products->setCurPage($page)->setPageSize($size);
        }

        $products->load();

        foreach ($products as $product) {
            if (!$this->getStockItem($product)->getIsInStock()) {
                continue;
            }

            try {
                $this->addToCart($product->getId());
                $this->cart->truncate();
            } catch (\Exception $e) {
                $errors[$product->getId()] = $e->getMessage();
            }
        }
        $this->cart->save();

        return [
            'page'   => $page,
            'size'   => $size,
            'count'  => $products->count(),
            'total'  => $this->productFactory->create()->getCollection()->getSize(),
            'errors' => $errors
        ];
    }

    /**
     * Add to cart function
     *
     * @param  number $productId Product identifier
     * @param  number $qty       Quanity value
     * @param  array  $options   Add to cart options
     *
     * @return string
     * @thrown \Exception
     */
    private function addToCart($productId, $qty = 1, $options = [])
    {
        $product = $this->productFactory->create()->load($productId);

        if (!$product || !$qty) {
            throw new LocalizedException(__('Incorrect product or quantity parameter'));
        }

        $params = [
            'qty' => $qty,
        ];

        if (!empty($options)) {
            $params['options'] = $options;
        }

        switch ($product->getTypeId()) {
            case ProductType::TYPE_BUNDLE:
                $this->setBundleOptions($product, $params);
                // We have to reload the product to avoid fatal error.
                // It happended only for bundle product
                $product = $this->productFactory->create()->load($productId);
                break;
            case ConfigurableProductType::TYPE_CODE:
                $this->setConfigurableOptions($product, $params);
                break;
            case GroupedProductType::TYPE_CODE:
                $this->setGroupedOptions($product, $params);
                break;
            case DownloadableProductType::TYPE_DOWNLOADABLE:
                $this->setDownloadableOptions($product, $params);
                break;
            case ProductType::TYPE_SIMPLE:
                // Simple, no action
                break;
            default:
                // Not supported product types
                return self::STATUS_NO_ACTION;
        }

        if ($product->getTypeId() == ConfigurableProductType::TYPE_CODE) {
            for ($i = 0; $i < self::MAX_CONFIGURABLE_INTERATION; $i++) {
                try {
                    $this->cart->addProduct($product, $params);
                } catch (\Exception $e) {
                    $error = $e->getMessage();
                    $this->setConfigurableOptions($product, $params);
                    continue;
                }

                $error = false;
                break;
            }

            if (!empty($error)) {
                throw new LocalizedException($error);
            }
        } else {
            $this->cart->addProduct($product, $params);
        }

        $this->cart->save();

        return self::STATUS_SUCCESS;
    }

    /**
     * Set required product options
     *
     * @param  CatalogProduct $product Magento product model
     * @param  array          $params  Add to cart parameters
     *
     * @return boolean
     */
    private function setOptions(CatalogProduct $product, array &$params)
    {
        foreach ($product->getOptions() as $option) {
            if (!$option->getIsRequire()) {
                continue;
            }

            switch ($option->getType()) {
                case ProductOption::OPTION_TYPE_DROP_DOWN:
                case ProductOption::OPTION_TYPE_RADIO:
                    $values = $option->getValues();
                    $v = current($values);
                    $params['options'][$option->getId()] = $v->getData()['option_type_id'];
                    break;
                case ProductOption::OPTION_TYPE_CHECKBOX:
                case ProductOption::OPTION_TYPE_MULTIPLE:
                    $values = $option->getValues();

                    foreach ($values as $v) {
                        $params['options'][$option->getId()][] = $v->getData()['option_type_id'];
                    }
                    break;
                default:
                    // Not suported
                    throw new LocalizedException(
                        __('Option type is not supported: ') . $option->getType()
                    );
            }
        }

        return true;
    }

    /**
     * Set links for downloadable products
     *
     * @param  CatalogProduct $product Magento product model
     * @param  array          $params  Add to cart parameters
     *
     * @return boolean
     */
    private function setDownloadableOptions(CatalogProduct $product, array &$params)
    {
        $links = $product->getTypeInstance()->getLinks($product);

        if (empty($links)) {
            return false;
        }

        foreach ($links as $link) {
            $params['links'][] = $link->getId();
        }

        return true;
    }

    /**
     * Set configurable options for add to cart
     *
     * @param  CatalogProduct $product Magento product model
     * @param  array          $params  Add to cart params
     *
     * @return boolean
     */
    private function setConfigurableOptions(CatalogProduct $product, array &$params)
    {
        $configurableAttributeOptions = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);
        $bNextInteration = !empty($params['super_attribute']);
        $bContinue = false;

        foreach ($configurableAttributeOptions as $attribute) {
            $allValues = array_column($attribute['values'], 'value_index');
            $currentProductValue = $product->getData($attribute['attribute_code']);

            if (in_array($currentProductValue, $allValues)) {
                $params['super_attribute'][$attribute['attribute_id']] = $currentProductValue;
            } elseif (is_array($allValues)) {
                if (!empty($params['super_attribute'][$attribute['attribute_id']])) {
                    if (!$bContinue) {
                        $key = array_search($params['super_attribute'][$attribute['attribute_id']], $allValues);

                        if (key_exists($key + 1, $allValues)) {
                            $params['super_attribute'][$attribute['attribute_id']] = $allValues[$key + 1];
                            $bContinue = true;
                        }
                    }
                } else {
                    $params['super_attribute'][$attribute['attribute_id']] = current($allValues);
                }
            }
        }

        return !$bNextInteration || $bContinue;
    }

    /**
     * Set bundle options for add to cart
     *
     * @param  CatalogProduct $product Magento product model
     * @param  array  $params  Add to cart params
     *
     * @return boolean
     */
    private function setBundleOptions(CatalogProduct $product, &$params)
    {
        $optionCollection = $product->getTypeInstance()->getOptionsCollection($product);
        $selectionCollection = $product->getTypeInstance()->getSelectionsCollection(
            $product->getTypeInstance()->getOptionsIds($product),
            $product
        );
        $options = $optionCollection->appendSelections($selectionCollection);

        $bundle_option = $bundle_option_qty = [];

        foreach ($options as $option) {
            $_selections = $option->getSelections();

            foreach ($_selections as $selection) {
                $bundle_option[$option->getOptionId()][] = $selection->getSelectionId();
                break;
            }
        }

        $params = array_merge(
            $params,
            [
            'product'           => $product->getId(),
            'bundle_option'     => $bundle_option,
            'related_product'   => null,
            ]
        );

        return true;
    }

    /**
     * Set grouped options for add to cart
     *
     * @param  CatalogProduct $product Magento product model
     * @param  array          $params  Add to cart params
     *
     * @return boolean
     */
    private function setGroupedOptions(CatalogProduct $product, &$params)
    {
        $childs = $product->getTypeInstance()->getAssociatedProducts($product);

        foreach ($childs as $c_product) {
            if ($this->getStockItem($c_product)->getIsInStock()) {
                $params['super_group'][$c_product->getId()] = self::DEFAULT_GROUP_QUANTITY;
            }
        }

        return true;
    }

    /**
     * Returns stock item
     *
     * @param  CatalogProduct $product Product model
     *
     * @return mixed
     */
    private function getStockItem(CatalogProduct $product)
    {
        $stockItem = null;

        if (!empty($product)) {
            $stockItem = $this->stockRegistry->getStockItem($product->getId());
        }

        return $stockItem;
    }
}
