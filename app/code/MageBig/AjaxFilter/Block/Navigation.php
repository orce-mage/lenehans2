<?php


namespace MageBig\AjaxFilter\Block;


class Navigation extends \Magento\LayeredNavigation\Block\Navigation
{
    const DEFAULT_EXPANDED_FACETS = 'magebig_ajaxfilter/general/expand_box_state';

    /**
     * @var string[]
     */
    private $inlineLayouts = ['1column'];

    /**
     * @var string|NULL
     */
    private $pageLayout;

    /**
     * Navigation constructor.
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Catalog\Model\Layer\Resolver $layerResolver
     * @param \Magento\Catalog\Model\Layer\FilterList $filterList
     * @param \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $visibilityFlag
     * @param array $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Catalog\Model\Layer\Resolver $layerResolver,
        \Magento\Catalog\Model\Layer\FilterList $filterList,
        \Magento\Catalog\Model\Layer\AvailabilityFlagInterface $visibilityFlag,
        array $data = []
    ) {
        parent::__construct($context, $layerResolver, $filterList, $visibilityFlag, $data);
        $this->pageLayout = $context->getPageConfig()->getPageLayout() ?: $this->getLayout()->getUpdate()->getPageLayout();
    }

    /**
     * @return bool
     */
    public function canShowBlock()
    {
        $canShowBlock = parent::canShowBlock();

        if ($this->getLayer() instanceof \Magento\Catalog\Model\Layer\Category &&
            $this->getLayer()->getCurrentCategory()->getDisplayMode() === \Magento\Catalog\Model\Category::DM_PAGE) {
            $canShowBlock = false;
        }

        return $canShowBlock;
    }

    /**
     * Return index of the facets that are expanded for the current page :
     *
     *  - nth first facets (depending of config)
     *  - facets with at least one selected filter
     *
     * @return string
     */
    public function getActiveFilters()
    {
        $activeFilters = [];

        if (!$this->isInline()) {
            $requestParams    = array_keys($this->getRequest()->getParams());
            $displayedFilters = $this->getDisplayedFilters();
            $expandedFacets   = $this->_scopeConfig->getValue(self::DEFAULT_EXPANDED_FACETS);
            $activeFilters    = [];
            if ($expandedFacets > 0) {
                $activeFilters = range(0, min(count($displayedFilters), $expandedFacets) - 1);
            }

            foreach ($displayedFilters as $index => $filter) {
                if (in_array($filter->getRequestVar(), $requestParams)) {
                    $activeFilters[] = $index;
                }
            }
        }

        return json_encode($activeFilters);
    }

    /**
     * Returns facet that are displayed.
     *
     * @return array
     */
    public function getDisplayedFilters()
    {
        $displayedFilters = array_filter(
            $this->getFilters(),
            function ($filter) {
                return $filter->getItemsCount() > 0;
            }
        );

        return array_values($displayedFilters);
    }

    /**
     * Indicates if the block is displayed inline or not.
     *
     * @return boolean
     */
    public function isInline()
    {
        return in_array($this->pageLayout, $this->inlineLayouts);
    }
}
