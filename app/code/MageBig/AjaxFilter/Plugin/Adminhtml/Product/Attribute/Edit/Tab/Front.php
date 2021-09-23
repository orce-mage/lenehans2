<?php
/**
 * Copyright © www.magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageBig\AjaxFilter\Plugin\Adminhtml\Product\Attribute\Edit\Tab;

use MageBig\AjaxFilter\Model\Adminhtml\Source\FilterStyle;
use Magento\Catalog\Block\Adminhtml\Product\Attribute\Edit\Tab\Front as ProductAttributeFrontTabBlock;
use Magento\Framework\Data\Form;
use Magento\Framework\Data\Form\Element\Fieldset;

/**
 * Add Search Weight field to the product attribute add/edit tab
 */
class Front
{
    /**
     * @var
     */
    private $filterStyle;

    /**
     * Front constructor.
     * @param FilterStyle $filterStyle
     */
    public function __construct(FilterStyle $filterStyle)
    {
        $this->filterStyles = $filterStyle;
    }

    /**
     * Add Search Weight field
     *
     * @param ProductAttributeFrontTabBlock $subject
     * @param Form $form
     * @return void
     */
    public function beforeSetForm(ProductAttributeFrontTabBlock $subject, Form $form)
    {
        /** @var Fieldset $fieldset */
        $fieldset = $form->getElement('front_fieldset');
        $fieldset->addField(
            'filter_style',
            'select',
            [
                'name' => 'filter_style',
                'label' => __('Filter Style'),
                'values' => $this->filterStyles->toOptionArray()
            ],
            'is_filterable'
        );
        $subject->getChildBlock('form_after')
            ->addFieldMap('is_filterable', 'is_filterable')
            ->addFieldMap('filter_style', 'filter_style')
            ->addFieldDependence('filter_style', 'is_filterable', '1|2');
    }
}
