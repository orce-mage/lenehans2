<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace Amasty\StorePickupWithLocatorMSI\Model\Data;

use Amasty\StorePickupWithLocatorMSI\Api\Data\LocationSourceSearchResultInterface;
use Magento\Framework\Api\SearchResults;

class LocationSourceSearchResult extends SearchResults implements LocationSourceSearchResultInterface
{

}
