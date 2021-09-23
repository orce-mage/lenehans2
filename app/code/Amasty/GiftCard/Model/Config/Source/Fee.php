<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Config\Source;

class Fee extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const PRICE_TYPE_PERCENT = 1;
    const PRICE_TYPE_FIXED = 2;

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            ['value' => self::PRICE_TYPE_PERCENT, 'label' => __('Percent')],
            ['value' => self::PRICE_TYPE_FIXED, 'label' => __('Fixed')],
        ];
    }
}
