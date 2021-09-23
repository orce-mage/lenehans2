<?php
namespace Data8\DataCaptureValidation\Config\Model;
class ValidationMethodTypeList implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => false, 'label' => __('On form submission')],
            ['value' => true, 'label' => __('On change')]
        ];
    }
}