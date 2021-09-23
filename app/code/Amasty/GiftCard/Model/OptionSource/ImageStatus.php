<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class ImageStatus implements OptionSourceInterface
{
    const DISABLED = 0;
    const ENABLED = 1;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $result = [];

        foreach ($this->toArray() as $value => $label) {
            $result[] = ['value' => $value, 'label' => $label];
        }

        return $result;
    }

    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            self::ENABLED => __('Enabled'),
            self::DISABLED => __('Disabled'),
        ];
    }
}
