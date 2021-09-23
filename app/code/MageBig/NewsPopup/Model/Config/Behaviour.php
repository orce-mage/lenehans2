<?php
/**
 * Copyright © magebig.com - All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace MageBig\NewsPopup\Model\Config;

class Behaviour implements \Magento\Framework\Option\ArrayInterface
{
    public function toOptionArray()
    {
        return [
            ['value' => 365, 'label' => 'Never show again'],
            ['value' => 1, 'label' => 'Hide for the rest of the day'],
            ['value' => 0, 'label' => 'Hide for the rest of the session'],
        ];
    }
}
