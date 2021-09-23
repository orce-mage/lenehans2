<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Plugin\Storelocator\Model\Config\Source;

use Amasty\Storelocator\Model\Config\Source\ConditionType;

class ConditionTypePlugin
{
    const MSI_SOURCE = 2;

    /**
     * @param ConditionType $subject
     * @param array $result
     * @return array[]
     */
    public function afterToOptionArray(ConditionType $subject, $result): array
    {
        array_push($result, ['label' => __('MSI Source'), 'value' => self::MSI_SOURCE]);

        return $result;
    }
}
