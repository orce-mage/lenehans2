<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MageBig\AjaxFilter\Block\Navigation\Renderer;

use Magento\Eav\Model\Entity\Attribute;
use Magento\Catalog\Model\ResourceModel\Layer\Filter\AttributeFactory;
use Magento\Framework\View\Element\Template;
use Magento\Eav\Model\Entity\Attribute\Option;
use Magento\Catalog\Model\Layer\Filter\Item as FilterItem;

/**
 * Class RenderLayered Render Swatches at Layered Navigation
 * @api
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @since 100.0.2
 */
class SwatchesRender extends \Magento\Swatches\Block\LayeredNavigation\RenderLayered
{
    /**
     * For `Filterable (with results)` setting
     */
    const FILTERABLE_WITH_RESULTS = '1';

    /**
     * Path to template file.
     *
     * @var string
     */
    protected $_template = 'MageBig_AjaxFilter::layer/filter/style/swatches.phtml';

    public function getFilter() {
        return $this->filter;
    }

    public function getActionUrl() {
        $query = $this->getRequest()->getQueryValue();
        $code = $this->getCode();
        $query[$code] = null;
        $url = $this->getUrl('*/*/*', [
            '_current'      => true,
            '_use_rewrite'  => true,
            '_query'        => $query
        ]);

        return $url;
    }

    public function getCode() {
        return $this->getFilter()->getRequestVar();
    }

    /**
     * @param Option $swatchOption
     * @return array
     */
    protected function getUnusedOption(Option $swatchOption)
    {
        return [
            'label' => $swatchOption->getLabel(),
            'link' => 'javascript:void();',
            'custom_style' => 'disabled',
            'value'=> 0,
            'count' => 0,
            'selected' => 0
        ];
    }

    /**
     * @param FilterItem $filterItem
     * @param Option $swatchOption
     * @return array
     */
    protected function getOptionViewData(FilterItem $filterItem, Option $swatchOption)
    {
        $customStyle = '';
        $value = $filterItem->getValue();
        $linkToOption = $this->buildUrl($this->eavAttribute->getAttributeCode(), $value);
        if ($this->isOptionDisabled($filterItem)) {
            $customStyle = 'disabled';
            $linkToOption = 'javascript:void();';
        }

        return [
            'label' => $swatchOption->getLabel(),
            'link' => $linkToOption,
            'custom_style' => $customStyle,
            'value' => $value,
            'count' => $filterItem->getCount(),
            'selected' => $this->checkSelected($value)
        ];
    }

    public function checkSelected ($value) {
        $code = $this->filter->getRequestVar();
        $selectedOption = $this->getRequest()->getParam($code);
        if (is_array($selectedOption)) {
            return false;
        }
        $selectedOption = explode(',', $selectedOption);

        return in_array($value, $selectedOption) ? 1 : 0;
    }
}
