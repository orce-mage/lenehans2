<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCardAccount
 */

declare(strict_types=1);

namespace Amasty\GiftCardAccount\Model\OptionSource;

use Magento\Framework\Data\OptionSourceInterface;

class AccountStatus implements OptionSourceInterface
{
    const STATUS_INACTIVE = 0;
    const STATUS_ACTIVE = 1;
    const STATUS_EXPIRED = 2;
    const STATUS_USED = 3;

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
            self::STATUS_INACTIVE => __('Inactive'),
            self::STATUS_ACTIVE => __('Active'),
            self::STATUS_EXPIRED => __('Expired'),
            self::STATUS_USED => __('Used'),
        ];
    }
}
