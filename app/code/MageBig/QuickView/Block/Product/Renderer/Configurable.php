<?php

namespace MageBig\QuickView\Block\Product\Renderer;

class Configurable extends \Magento\Swatches\Block\Product\Renderer\Configurable
{
    const SWATCH_RENDERER_TEMPLATE       = 'MageBig_QuickView::product/view/renderer.phtml';
    const CONFIGURABLE_RENDERER_TEMPLATE = 'MageBig_QuickView::product/view/type/options/configurable.phtml';

    protected function getRendererTemplate()
    {
        return $this->isProductHasSwatchAttribute() ?
        self::SWATCH_RENDERER_TEMPLATE : self::CONFIGURABLE_RENDERER_TEMPLATE;
    }
}
