<?php

namespace MageBig\AjaxFilter\Block\Navigation\Renderer;

use Magento\Catalog\Helper\Data as CatalogHelper;
use Magento\Catalog\Model\Layer\Filter\FilterInterface;
use Magento\Framework\View\Element\Template;
use Magento\LayeredNavigation\Block\Navigation\FilterRendererInterface;

abstract class AbstractRenderer extends Template implements FilterRendererInterface
{
    /**
     * @var FilterInterface
     */
    private $filter;

    /**
     * @var CatalogHelper
     */
    private $catalogHelper;

    protected $helper;

    /**
     * Constructor.
     *
     * @param Template\Context $context Block context.
     * @param CatalogHelper $catalogHelper Catalog helper.
     * @param array $data Additionnal block data.
     */
    public function __construct(
        Template\Context $context,
        CatalogHelper $catalogHelper,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->catalogHelper = $catalogHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function render(FilterInterface $filter)
    {
        $html = '';
        $this->filter = $filter;

        if ($this->canRenderFilter()) {
            $this->assign('filterItems', $filter->getItems());
            $html = $this->_toHtml();
            $this->assign('filterItems', []);
        }

        return $html;
    }

    /**
     * @return FilterInterface
     */
    public function getFilter()
    {
        return $this->filter;
    }

    public function getActionUrl()
    {
        $query = $this->getRequest()->getQueryValue();
        $code = $this->getCode();
        $query[$code] = null;
        $params['_current'] = true;
        $params['_use_rewrite'] = true;
        $params['_query'] = $query;
        $params['_escape'] = true;

        return $this->getUrl('*/*/*', $params);
    }

    public function getCode()
    {
        return $this->getFilter()->getRequestVar();
    }

    /**
     * Indicates if the product count should be displayed or not.
     *
     * @return boolean
     */
    public function displayProductCount()
    {
        return $this->catalogHelper->shouldDisplayProductCountOnLayer();
    }

    /**
     * Check if the current block can render a filter (previously set through ::setFilter).
     *
     * @return boolean
     */
    abstract protected function canRenderFilter();

    public function getFilterStyle()
    {
        if ($this->getFilter()->hasAttributeModel()) {
            return $this->getFilter()->getAttributeModel()->getData('filter_style');
        }

        return null;
    }
}
