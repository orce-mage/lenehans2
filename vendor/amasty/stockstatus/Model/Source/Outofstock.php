<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Stockstatus
 */


declare(strict_types=1);

namespace Amasty\Stockstatus\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Outofstock implements OptionSourceInterface
{
    const MAGENTO_LOGIC = 0;

    const SHOW = 1;

    const SHOW_AND_CROSSED = 2;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => self::MAGENTO_LOGIC,
                'label' => __('No, Magento logic')
            ],
            [
                'value' => self::SHOW,
                'label' => __('Yes, Out of stock options selectable')
            ],
            [
                'value' => self::SHOW_AND_CROSSED,
                'label' => __('Yes, Out of stock options selectable and crossed-out')
            ]
        ];
    }
}
