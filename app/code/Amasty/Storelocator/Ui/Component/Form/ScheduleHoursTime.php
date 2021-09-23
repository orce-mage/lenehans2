<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Storelocator
 */


namespace Amasty\Storelocator\Ui\Component\Form;

/**
 * Class ScheduleHoursTime
 */
class ScheduleHoursTime implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Get hours options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $hoursArray = [];
        for ($i = 0; $i < 24; $i++) {
            $hoursArray[] = [
                'value' => str_pad($i, 2, '0', STR_PAD_LEFT),
                'label' => str_pad($i, 2, '0', STR_PAD_LEFT)
            ];
        }

        return $hoursArray;
    }
}
