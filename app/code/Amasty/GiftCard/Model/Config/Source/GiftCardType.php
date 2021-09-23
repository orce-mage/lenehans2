<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_GiftCard
 */

declare(strict_types=1);

namespace Amasty\GiftCard\Model\Config\Source;

class GiftCardType extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    const TYPE_VIRTUAL = 1;
    const TYPE_PRINTED = 2;
    const TYPE_COMBINED = 3;

    /**
     * @return array
     */
    public function getAllOptions()
    {
        return [
            ['value' =>self::TYPE_VIRTUAL, 'label' => __('Virtual')],
            ['value' => self::TYPE_PRINTED, 'label' => __('Printed')],
            ['value' => self::TYPE_COMBINED, 'label' => __('Combined')],
        ];
    }
}
