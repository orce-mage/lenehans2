<?php

namespace MageBig\AjaxFilter\Block\Navigation\Renderer;

use Magento\Framework\View\Element\Template\Context;
use Magento\Swatches\Helper\Data as SwatchHelper;
use Magento\Catalog\Helper\Data as CatalogHelper;

class Swatches extends AbstractRenderer
{
    const SWATCHES_TEMPLATE = 'MageBig_AjaxFilter::layer/filter/style/swatches.phtml';

    /**
     * @var string
     */
    protected $block = 'MageBig\AjaxFilter\Block\Navigation\Renderer\SwatchesRender';

    /**
     * @var SwatchHelper
     */
    private $swatchHelper;

    /**
     * Constructor.
     *
     * @param Context       $context       Template context.
     * @param CatalogHelper $catalogHelper Catalog helper.
     * @param SwatchHelper  $swatchHelper  Swatch helper.
     * @param array         $data          Custom data.
     */
    public function __construct(Context $context, CatalogHelper $catalogHelper, SwatchHelper $swatchHelper, array $data = [])
    {
        parent::__construct($context, $catalogHelper, $data);
        $this->swatchHelper = $swatchHelper;
    }

    /**
     * {@inheritDoc}
     */
    protected function canRenderFilter()
    {
        $canRenderFilter = false;
        try {
            $attribute = $this->getFilter()->getAttributeModel();
            $canRenderFilter = $this->swatchHelper->isSwatchAttribute($attribute);
        } catch (\Exception $e) {
        }

        return $canRenderFilter;
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     *
     * {@inheritDoc}
     */
    protected function _toHtml()
    {
        $html = false;

        if ($this->canRenderFilter()) {
            $html = $this->getLayout()
                ->createBlock($this->block)
                ->setSwatchFilter($this->getFilter())
                ->setFilterStyle($this->getFilterStyle())
                ->toHtml();
        }

        return $html;
    }
}
