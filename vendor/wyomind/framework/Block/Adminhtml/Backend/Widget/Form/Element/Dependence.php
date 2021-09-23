<?php
/**
 * Copyright © 2020 Wyomind. All rights reserved.
 * See LICENSE.txt for license details.
 */


namespace Wyomind\Framework\Block\Adminhtml\Backend\Widget\Form\Element;

/**
 * Class Dependence
 * @package Wyomind\MassStockUpdate\Magento\Backend\Block\Widget\Form\Element
 */
class Dependence extends \Magento\Backend\Block\Widget\Form\Element\Dependence
{
    /**
     * Register field name dependence one from each other by specified values
     *
     * @param string $fieldName
     * @param string $fieldNameFrom
     * @param \Magento\Config\Model\Config\Structure\Element\Dependency\Field|string $refField
     * @return \Magento\Backend\Block\Widget\Form\Element\Dependence
     */
    public function addFieldDependence($fieldName, $fieldNameFrom, $refField)
    {

        if (!is_object($refField)) {
            if (is_array($refField)) {
                $value = implode(",", $refField);
            } else {
                $value = $refField;
            }
            /** @var $refField \Magento\Config\Model\Config\Structure\Element\Dependency\Field */
            $refField = $this->_fieldFactory->create(

                ['fieldData' => ['value' => $value, 'separator' => ','], 'fieldPrefix' => '']
            );
        }
        $this->_depends[$fieldName][$fieldNameFrom] = $refField;
        return $this;
    }
}
