<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageBig\SyntaxCms\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\DataObject;

/**
 * Class Bgelements
 */
class Bgelements extends AbstractFieldArray
{
    /**
     * @var Types
     */
    protected $typesRenderer = null;

    /**
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        if ($element->getInherit() == 1) {
            if ($element->getValue()) {
                $value = $element->getValue();
                if (!is_array($value)) {
                    $value = @json_decode($value, true);
                }
                if (is_array($value)) {
                    $element->setValue($value);
                }
            }
        }
        return parent::render($element);
    }

    /**
     *
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'selector',
            [
                'label' => __('JQuery Selector'),
                'type' => 'text',
            ]
        );

        $this->addColumn(
            'type',
            [
                'label' => __('Type'),
                'renderer' => $this->getTypesRenderer(),
            ]
        );

        $this->addColumn(
            'comment',
            [
                'label' => __('Comment'),
                'type' => 'text',
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @return \Magento\Framework\View\Element\BlockInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getTypesRenderer()
    {
        if (!$this->typesRenderer) {
            $this->typesRenderer = $this->getLayout()->createBlock(
                Types::class,
                '',
                ['data' => ['is_render_to_js_template' => true]]
            );
        }
        return $this->typesRenderer;
    }

    /**
     * @param DataObject $row
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        $type = $row->getType();
        $options = [];
        if ($type) {
            $options['option_' . $this->getTypesRenderer()->calcOptionHash($type)]
                = 'selected="selected"';
        }
        $row->setData('option_extra_attrs', $options);
        // Fix delete action in Magento 2.4
        $row['_id'] = 'bg-'.rand();
    }
}
