<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Ui\Component\Form;

/**
 * Class ScheduleStatus
 */
class ScheduleStatus implements \Magento\Framework\Option\ArrayInterface
{
    const OPEN_STATUS = 1;
    const CLOSE_STATUS = 0;

    /**
     * Getting schedule status options
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::OPEN_STATUS, 'label' => __('Open')],
            ['value' => self::CLOSE_STATUS, 'label' => __('Closed')]
        ];
    }
}
