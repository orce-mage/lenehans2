<?php

namespace Amasty\Faq\Model\OptionSource\Category;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::STATUS_DISABLED, 'label'=> __('Disabled')],
            ['value' => self::STATUS_ENABLED, 'label'=> __('Enabled')]
        ];
    }
}
