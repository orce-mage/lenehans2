<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Block;

use Magento\Framework\View\Element\AbstractBlock;

class Location extends \Amasty\StorePickupWithLocator\Block\Location
{
    const MAP_UPDATE_ROUTE = 'amstorepickupmsi/map/update';

    /**
     * @param array $params
     * @return string
     */
    public function getUpdateUrl($params = []): string
    {
        return $this->getUrl(self::MAP_UPDATE_ROUTE, $params);
    }
}
