<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace MageBig\SyntaxCms\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\DataObject;

/**
 * Class Wysioptions
 */
class Wysioptions extends AbstractFieldArray
{
    /**
     * Prepare to render
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'name',
            [
                'label' => __('Name'),
                'type' => 'text'
            ]
        );
        $this->addColumn(
            'value',
            [
                'label' => __('Value'),
                'type' => 'text'
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
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
     * Prepare existing row data object
     *
     * @param DataObject $row
     * @return void
     */
    protected function _prepareArrayRow(DataObject $row)
    {
        // Fix delete action in Magento 2.4
        $row['_id'] = 'op-'.rand();
    }
}
