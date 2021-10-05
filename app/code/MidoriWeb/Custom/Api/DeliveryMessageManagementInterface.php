<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace MidoriWeb\Custom\Api;

interface DeliveryMessageManagementInterface
{
    /**
     * @param int $productId
     * @return \MidoriWeb\Custom\Api\Data\DeliveryMessageDataInterface
     */
    public function getDeliveryMessageByProduct(
        int $productId
    ): \MidoriWeb\Custom\Api\Data\DeliveryMessageDataInterface;
}
