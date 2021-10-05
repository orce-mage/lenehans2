<?php
/**
 * @author Amasty Team
 * @copyright Copyright (c) 2021 Amasty (https://www.amasty.com)
 * @package Amasty_StorePickupWithLocatorMSI
 */


declare(strict_types=1);

namespace MidoriWeb\Custom\Model\Data;

use MidoriWeb\Custom\Api\Data\DeliveryMessageDataInterface;
use Magento\Framework\Api\AbstractSimpleObject;

class DeliveryMessageData extends AbstractSimpleObject implements DeliveryMessageDataInterface
{

    /**
     * @return \MidoriWeb\Custom\Api\Data\DeliveryMessageContentInterface|null
     */
    public function getDeliveryMessage(): ?\MidoriWeb\Custom\Api\Data\DeliveryMessageContentInterface
    {
        return $this->_get(self::DELIVERY_MESSAGE);
    }

    /**
     * @param \MidoriWeb\Custom\Api\Data\DeliveryMessageContentInterface|null $deliveryMessage
     */
    public function setDeliveryMessage(?\MidoriWeb\Custom\Api\Data\DeliveryMessageContentInterface $deliveryMessage): void
    {
        $this->setData(self::DELIVERY_MESSAGE, $deliveryMessage);
    }
}
