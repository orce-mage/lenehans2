<?php
namespace Data8\DataCaptureValidation\Config\Model;
class EmailValidationTypeList implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'None', 'label' => __('None')],
            ['value' => 'Syntax', 'label' => __('Syntax (lowest)')],
            ['value' => 'MX', 'label' => __('Domain')],
            ['value' => 'Server', 'label' => __('Server')],
            ['value' => 'Address', 'label' => __('Address (highest)')]
        ];
    }
}