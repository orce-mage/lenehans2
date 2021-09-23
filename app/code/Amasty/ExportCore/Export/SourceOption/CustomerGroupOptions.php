<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_ExportCore
 */


namespace Amasty\ExportCore\Export\SourceOption;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Framework\Data\OptionSourceInterface;
use Magento\Customer\Model\Customer\Attribute\Source\Group;

class CustomerGroupOptions implements OptionSourceInterface
{
    /**
     * @var Group
     */
    private $groupSource;

    /**
     * @var bool
     */
    private $withAllGroups;

    public function __construct(
        Group $groupSource,
        $withAllGroups = false
    ) {
        $this->groupSource = $groupSource;
        $this->withAllGroups = $withAllGroups;
    }

    public function toOptionArray(): array
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
        if ($this->withAllGroups) {
            array_unshift(
                $optionArray,
                [
                    'value' => GroupInterface::CUST_GROUP_ALL,
                    'label' => __('ALL GROUPS')->getText()
                ]
            );
        }

        return $optionArray;
    }

    public function toArray(): array
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
