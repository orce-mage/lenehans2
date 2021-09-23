<?php
namespace Swissup\SoldTogether\Model\Config\Source;

class Layout implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options[] = ['label' => '', 'value' => ''];
        $availableOptions = [
            'amazon-default' => __('Default (Amazon inspired)'),
            'amazon-stripe' => __('Stripe')
        ];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}
