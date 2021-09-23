<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_SalesRuleWizard
 */


namespace Amasty\SalesRuleWizard\Model\OptionsProvider;

class ApplyTime implements \Magento\Framework\Data\OptionSourceInterface
{
    const FIRST_TIME = 'first';
    const EVERY_TIME = 'every';
    const LIMIT_TIME = 'limit';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::FIRST_TIME, 'label' => __('Only First Time when conditions are met')],
            ['value' => self::EVERY_TIME, 'label' => __('Every Time when conditions are met')],
            ['value' => self::LIMIT_TIME, 'label' => __('Every Time with Limit when conditions are met')]
        ];
    }
}
