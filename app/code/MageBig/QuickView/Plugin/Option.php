<?php

namespace MageBig\QuickView\Plugin;

class Option extends \Magento\Catalog\Block\Product\View\Options\AbstractOptions
{
    public function beforeGetOption()
    {
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');
        if (!$priceRender) {
            $priceRender = $this->getLayout()->createBlock(
                \Magento\Framework\Pricing\Render::class,
                'product.price.render.default',
                ['data' => ['price_render_handle' => 'catalog_product_prices']]
            );
        }

        return $this;
    }
}