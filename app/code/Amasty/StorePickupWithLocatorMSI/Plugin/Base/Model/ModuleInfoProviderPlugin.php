<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Plugin\Base\Model;

use Amasty\Base\Model\ModuleInfoProvider;

class ModuleInfoProviderPlugin
{
    /**
     * @param ModuleInfoProvider $subject
     * @param array $result
     * @return array
     */
    public function afterGetRestrictedModules(ModuleInfoProvider $subject, $result): array
    {
        array_push($result, 'Amasty_StorePickupWithLocatorMSI');

        return $result;
    }
}
