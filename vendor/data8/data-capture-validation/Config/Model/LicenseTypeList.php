<?php
namespace Data8\DataCaptureValidation\Config\Model;
class LicenseTypeList implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 'None', 'label' => __('None')],
            ['value' => 'FreeTrial', 'label' => __('Free Trial (All Available Data)')],
            ['value' => 'FreeTrialThoroughfare', 'label' => __('Free Trial (Street Level Data)')],
            ['value' => 'WebClickFull', 'label' => __('Per Click License (All Available Data)')],
            ['value' => 'WebClickThoroughfare', 'label' => __('Per Click License (Street Level Data)')],
            ['value' => 'WebServerFull', 'label' => __('Unlimited License (All Available Data)')],
            ['value' => 'WebServerThoroughfare', 'label' => __('Unlimited License (Street Level Data)')]
        ];
    }
}
//