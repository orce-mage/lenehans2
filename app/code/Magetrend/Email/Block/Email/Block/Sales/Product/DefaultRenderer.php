<?php
/**
 * Copyright © 2016 MB Vienas bitas. All rights reserved.
 * @website    www.magetrend.com
 * @package    MT Email for M2
 * @author     Edvinas Stulpinas <edwin@magetrend.com>
 */

namespace Magetrend\Email\Block\Email\Block\Sales\Product;

class DefaultRenderer extends \Magetrend\Email\Block\Email\Block\Template
{
    private $product = [];
    /**
     * @var \Magento\Catalog\Block\Product\ImageBuilder
     */
    public $imageBuilder;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    public $productHelper;

    public $priceCurrency;

    public $appEmulation;

    public $blockFactory;

    public $productFactory;

    public $productImageHelper;

    /**
     * DefaultOrder constructor.
     *
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Block\Product\ImageBuilder $imageBuilder,
        \Magento\Catalog\Helper\Output $productHelper,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        \Magento\Framework\View\Element\BlockFactory $blockFactory,
        \Magento\Store\Model\App\Emulation $appEmulation,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Catalog\Helper\Image $productImageHelper,
        array $data = []
    ) {
        $this->imageBuilder = $imageBuilder;
        $this->productHelper = $productHelper;
        $this->priceCurrency = $priceCurrency;
        $this->blockFactory = $blockFactory;
        $this->appEmulation = $appEmulation;
        $this->productFactory = $productFactory;
        $this->productImageHelper = $productImageHelper;
        parent::__construct($context, $data);
    }

    /**
     * Returns item image html
     *
     * @param $item
     * @return string
     */
    public function getImage($width = null, $height = null)
    {
        $product = $this->getProduct();
        if (!$product || empty($product->getImage())) {
            return $this->getProductImagePlaceholder();
        }

        if ($width == null && $height == null) {
            $imageUrl = $this->imageBuilder->setProduct($product)
               ->setImageId('category_page_grid')
               ->create()->getImageUrl();
            return $imageUrl;
        }

        $productImage = $this->productImageHelper->init($product, 'category_page_grid')
            ->constrainOnly(true)
            ->keepAspectRatio(true)
            ->keepTransparency(true)
            ->keepFrame(true)
            ->resize($width, $height);

        return $productImage->getUrl();
    }

    public function getProductImagePlaceholder()
    {
        return $this->getViewFileUrl('Magento_Catalog::images/product/placeholder/small_image.jpg');
    }

    public function getFormatedPrice()
    {
        $product = $this->getProduct();
        $price = $product->getPriceInfo()->getPrice('regular_price');
        return $this->priceCurrency->format(
            $price->getAmount()->getValue(),
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            $product->getStore()
        );
    }

    public function getFormatedSpecialPrice()
    {
        $product = $this->getProduct();
        $price = $product->getPriceInfo()->getPrice('regular_price')->getAmount()->getValue();
        $specialPrice = $product->getPriceInfo()->getPrice('final_price')->getAmount()->getValue();
        if (!$specialPrice || $specialPrice == 0 || $specialPrice >= $price) {
            return false;
        }

        return $this->priceCurrency->format(
            $specialPrice,
            false,
            \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
            $product->getStore()
        );
    }

    public function getProduct()
    {
        $productId = $this->getData('product')->getId();
        if (!isset($this->product[$productId])) {
            $this->product[$productId] = $this->productFactory->create()->load($productId);
        }

        return $this->product[$productId];
    }

    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }

        /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getPriceRender();
        $price = '';

        $arguments['display_label'] = __('aa');
        $arguments['price_type'] = 'finalPrice';
        $arguments['include_container'] = true;
        $arguments['skip_adjustments'] = true;

        if ($priceRender) {
            $price = $priceRender->render(
                'final_price',
                $product,
                $arguments
            );
        }
        return $price;
    }

    public function getPriceRender()
    {
        return $this->_layout->createBlock(
            \Magento\Framework\Pricing\Render::class,
            '',
            ['data' => ['price_render_handle' => 'catalog_product_prices']]
        );
    }

}