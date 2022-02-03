<?php

declare(strict_types=1);

namespace Amasty\Faq\Model\OptionSource;

use Magento\Customer\Model\Customer\Attribute\Source\Group;

class CustomerGroups implements \Magento\Framework\Data\OptionSourceInterface
{
    /**
     * @var Group
     */
    protected $groupSource;

    public function __construct(Group $groupSource)
    {
        $this->groupSource = $groupSource;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $optionArray = [];
        $options = $this->groupSource->getAllOptions();

        if (!empty($options[0]) && is_array($options[0]['value'])) {
            array_unshift($options[0]['value'], ['value' => '0', 'label' =>  __('NOT LOGGED IN')]);

            foreach ($options as &$optionGroup) {

                foreach ($optionGroup['value'] as &$option) {
                    $option['value'] = (string)$option['value'];
                }
            }
            $optionArray = $options;
        } else {
            foreach ($this->toArray() as $stepId => $label) {
                $optionArray[] = ['value' => $stepId, 'label' => $label];
            }
        }

        return $optionArray;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $options = $this->groupSource->getAllOptions();
        $result = ['0'  => __('NOT LOGGED IN')];

        /**
         * B2B Fix
         */
        if (!empty($options[0]) && is_array($options[0]['value'])) {
            $options = $options[0]['value'];
        }

        foreach ($options as $option) {
            $result[$option['value']] = $option['label'];
        }

        return $result;
    }
}
