<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_Number
 */


namespace Amasty\Number\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Offset implements OptionSourceInterface
{
    /**
     * Get options in "key-value" format
     *
     * @return array
     */
    public function toArray()
    {
        $result = [];

        foreach ($this->toOptionArray() as $row) {
            $result[$row['value']] = $row['label'];
        }

        return $result;
    }

    /**
     * Options getter
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [];

        for ($i = -12; $i <= 12; $i++) {
            $v = $i > 0 ? "+$i" : $i;
            $hours = ($i==1 || $i==-1) ? '%1 hour': '%1 hours';
            $options[] = [
                'value' => $v,
                'label' => __($hours, $v),
            ];
        }

        return $options;
    }
}
