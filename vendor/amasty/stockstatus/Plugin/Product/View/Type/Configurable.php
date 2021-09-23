<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Plugin\Product\View\Type;

use Amasty\Stockstatus\Model\ConfigProvider;
use Amasty\Stockstatus\Model\ResourceModel\Inventory as InventoryResource;
use Amasty\Stockstatus\Model\Source\Outofstock;
use Amasty\Stockstatus\Model\Stockstatus\Processor;
use Amasty\Stockstatus\Model\Stockstatus\Renderer\Info as InfoRenderer;
use Amasty\Stockstatus\Model\Stockstatus\Renderer\Status as StatusRenderer;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Catalog\Model\Product\Attribute\Source\Status;
use Magento\ConfigurableProduct\Block\Product\View\Type\Configurable as NativeConfigurable;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Framework\View\Element\Template;

class Configurable
{
    /**
     * @var bool
     */
    private $isProductPage;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    private $catalogProduct;

    /**
     * @var \Amasty\Stockstatus\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\ConfigurableProduct\Helper\Data
     */
    private $configurableHelper;

    /**
     * @var array
     */
    private $originalAllowedProducts = [];

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * @var Processor
     */
    private $processor;

    /**
     * @var StatusRenderer
     */
    private $statusRenderer;

    /**
     * @var InfoRenderer
     */
    private $infoRenderer;

    /**
     * @var InventoryResource
     */
    private $inventoryResource;

    public function __construct(
        Processor $processor,
        StatusRenderer $statusRenderer,
        InfoRenderer $infoRenderer,
        \Magento\Catalog\Helper\Product $catalogProduct,
        \Amasty\Stockstatus\Helper\Data $helper,
        JsonSerializer $jsonSerializer,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\ConfigurableProduct\Helper\Data $configurableHelper,
        \Magento\Framework\App\Request\Http $request,
        ConfigProvider $configProvider,
        InventoryResource $inventoryResource
    ) {
        $this->catalogProduct = $catalogProduct;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->configurableHelper = $configurableHelper;
        $this->configProvider = $configProvider;
        $this->isProductPage = $request->getFullActionName() == 'catalog_product_view';
        $this->jsonSerializer = $jsonSerializer;
        $this->processor = $processor;
        $this->statusRenderer = $statusRenderer;
        $this->infoRenderer = $infoRenderer;
        $this->inventoryResource = $inventoryResource;
    }

    /**
     * @param NativeConfigurable $subject
     *
     * @return array
     * @throws LocalizedException
     */
    public function beforeGetAllowProducts($subject)
    {
        if ($this->isProductPage()
            && !$subject->hasAllowProducts()
            && $this->shouldLoadStock()
        ) {
            $products = [];
            $websiteCode = $this->storeManager->getWebsite()->getCode();
            $allProducts = $this->getConfigurableChilds($subject->getProduct());
            foreach ($allProducts as $product) {
                /* remove code for showing out of stock options*/
                if ($product->getStatus() == Status::STATUS_ENABLED) {
                    $products[] = $product;
                }
                try {
                    $stockStatus = $this->inventoryResource->getStockStatus(
                        $product->getData('sku'),
                        $websiteCode
                    );
                    if ($stockStatus) {
                        $this->originalAllowedProducts[] = $product;
                    }
                } catch (NoSuchEntityException $e) {
                    continue;
                }
            }
            $subject->setAllowProducts($products);
        }

        return [];
    }

    /**
     * @param NativeConfigurable $subject
     * @param string $html
     *
     * @return string
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterToHtml(
        $subject,
        $html
    ) {
        $isChangeStatus = $this->configProvider->isChangeStatus();

        if (!$this->isProductPage()
            || strpos($html, 'amstockstatusRenderer.init') !== false
            || !($subject->getNameInLayout() == 'product.info.options.configurable'
                || ($subject->getNameInLayout() == 'product.info.options.swatches' && $isChangeStatus))
        ) {
            return $html;
        }

        $allProducts = $this->getConfigurableChilds($subject->getProduct());
        $attributes = $subject->getProduct()->getTypeInstance()
            ->getConfigurableAttributes($subject->getProduct());
        $shouldLoadStock = $this->shouldLoadStock();
        $childData = [
            'changeConfigurableStatus' => (int) $isChangeStatus,
            'type' => $subject->getNameInLayout(),
            'info_block' => $this->infoRenderer->render(),
            'display_in_dropdowns' => (int) $this->configProvider->isDisplayInDropdowns(),
            'should_load_stock' => $shouldLoadStock
        ];

        $this->processor->execute($allProducts);

        /** @var ProductInterface|Product $product */
        foreach ($allProducts as $product) {
            $key = $this->getKey($attributes, $product);

            if ($key) {
                $stockstatusInformation =  $product->getExtensionAttributes()->getStockstatusInformation();

                $childData[$key] = [
                    'custom_status' => $this->statusRenderer->render($product),
                    'custom_status_text' => $stockstatusInformation->getStatusId()
                        ? $stockstatusInformation->getStatusMessage()
                        : '',
                    'product_id' => $product->getId()
                ];

                if ($shouldLoadStock) {
                    $childData = $this->addChildStockData($childData, $product, $key);
                }

                $childData[$key]['pricealert'] = $this->helper->getPriceAlert($product);

                /* add status for previous option when all statuses are the same*/
                $pos = strrpos($key, ',');

                if ($pos) {
                    $newKey = substr($key, 0, $pos);

                    if (isset($childData[$newKey])) {
                        if ($childData[$newKey]['custom_status'] != $childData[$key]['custom_status']) {
                            $childData[$newKey] = null;
                        }
                    } elseif (!array_key_exists($newKey, $childData)) {
                        $childData[$newKey] = $childData[$key];
                    }
                }
            }
        }

        $html .= $subject->getLayout()->createBlock(Template::class)
            ->setData('options_data', $this->jsonSerializer->serialize($childData))
            ->setTemplate('Amasty_Stockstatus::init_renderer.phtml')
            ->toHtml();

        return $html;
    }

    /**
     * @param mixed $optionsData
     * @param Product $product
     * @param array|string $key
     *
     * @return mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    protected function addChildStockData($optionsData, Product $product, $key)
    {
        $stockStatus = $this->inventoryResource->getStockStatus(
            $product->getData('sku'),
            $this->storeManager->getWebsite()->getCode()
        );
        $optionsData[$key]['is_in_stock'] = (int) $stockStatus;

        if (!$stockStatus) {
            $product->setData('is_salable', 0);
            $optionsData[$key]['stockalert'] = $this->helper->getStockAlert($product);
        }

        if (!$optionsData[$key]['is_in_stock'] && !$optionsData[$key]['custom_status']) {
            $optionsData[$key]['custom_status'] = __('Out of Stock');
        }

        return $optionsData;
    }

    /**
     * @param Attribute[] $attributes
     * @param Product $product
     * @return array|string
     */
    protected function getKey($attributes, Product $product)
    {
        $key = [];
        foreach ($attributes as $attribute) {
            $key[] = $product->getData(
                $attribute->getData('product_attribute')->getData(
                    'attribute_code'
                )
            );
        }

        $key = implode(',', $key);

        return $key;
    }

    public function afterGetJsonConfig(NativeConfigurable $subject, string $result): string
    {
        $result = $this->jsonSerializer->unserialize($result);

        if ($this->isProductPage()
            && $this->configProvider->getOutofstockVisibility() === Outofstock::SHOW_AND_CROSSED
        ) {
            $result['original_products'] = $this->configurableHelper->getOptions(
                $subject->getProduct(),
                $this->originalAllowedProducts
            );
        }

        return $this->jsonSerializer->serialize($result);
    }

    protected function isProductPage(): bool
    {
        return $this->isProductPage;
    }

    protected function shouldLoadStock(): bool
    {
        return $this->configProvider->getOutofstockVisibility() !== Outofstock::MAGENTO_LOGIC;
    }

    /**
     * @param Product $product
     * @return Product[]
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getConfigurableChilds(Product $product): array
    {
        $products = $product->getTypeInstance()->getUsedProducts($product);
        if ($this->shouldLoadStock()) {
            $productSkus = array_map(function ($product) {
                return $product->getData('sku');
            }, $products);
            $this->inventoryResource->loadStockStatus($productSkus, $this->storeManager->getWebsite()->getCode());
        }

        return $products;
    }
}
