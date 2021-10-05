<?php

declare(strict_types=1);

namespace MidoriWeb\Custom\Api\Data;

interface DeliveryMessageDataInterface
{
    const DELIVERY_MESSAGE = 'delivery_message';
    /**
     * Gets delivery message.
     *
     * @return \MidoriWeb\Custom\Api\Data\DeliveryMessageContentInterface.
     */
    public function getDeliveryMessage();

    /**
     * Set delivery message.
     *
     * @param \MidoriWeb\Custom\Api\Data\DeliveryMessageContentInterface $deliveryMessage
     * @return $this
     */
    public function setDeliveryMessage(\MidoriWeb\Custom\Api\Data\DeliveryMessageContentInterface $deliveryMessage);
}
